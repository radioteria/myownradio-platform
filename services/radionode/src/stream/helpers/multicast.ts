import { Writable, PassThrough, Readable } from 'stream';
import { MetadataEmitter } from '../metadata/metadataEmitter';
import logger from '../../services/logger';

interface IWritableState {
  waitForDrain: boolean;
}

export class Multicast extends Writable {
  private clientsMap = new Map<Writable, IWritableState>();

  private updatedAt = new Date();

  readonly metadataEmitter = new MetadataEmitter();

  public _write(chunk: Buffer | string, enc: string, callback: (err?: Error) => void): boolean {
    this.clientsMap.forEach((state, writable) => {
      if (state.waitForDrain) {
        return;
      }

      const ok = writable.write(chunk, enc);

      if (!ok) {
        state.waitForDrain = true;
      }
    });

    process.nextTick(callback);

    return true;
  }

  public createStream(): Readable {
    this.updatedAt = new Date();

    const stream = new PassThrough();

    const handleClose = () => removeClient();
    const handleError = () => removeClient();
    const handleDrain = () => {
      if (!this.clientsMap.has(stream)) {
        logger.warn(`Received "drain" event but client not found`);
        return;
      }
      this.clientsMap.get(stream).waitForDrain = false;
      logger.verbose(`Received "drain" event`);
    };

    const removeClient = () => {
      this.updatedAt = new Date();

      this.clientsMap.delete(stream);

      stream.off('close', handleClose);
      stream.off('error', handleError);
      stream.off('drain', handleDrain);

      logger.verbose(`Client removed (left: ${this.clientsCount()})`);

      this.emit('gone', stream);
    };

    stream.on('close', handleClose);
    stream.on('error', handleError);
    stream.on('drain', handleDrain);

    this.clientsMap.set(stream, { waitForDrain: false });

    logger.verbose(`New client added (amount: ${this.clientsCount()})`);

    this.emit('created', stream);

    return stream;
  }

  public clientsCount(): number {
    return this.clientsMap.size;
  }

  public getUpdatedAt() {
    return this.updatedAt;
  }
}

export const multicast = () => new Multicast();
