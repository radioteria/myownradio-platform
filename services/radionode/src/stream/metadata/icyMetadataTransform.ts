import { Readable, Transform } from 'stream';
import { MetadataEmitter } from './metadataEmitter';
import config from '../../config';

export class IcyMetadataTransform extends Transform {
  private remainder = this.icyMetadataInterval;
  private metadataBuffer: Buffer;

  constructor(private metadataEmitter: MetadataEmitter, private icyMetadataInterval: number) {
    super();

    this.metadataEmitter.on('title-changed', () => {
      this.updateMetadataBuffer();
    });

    this.updateMetadataBuffer();
  }

  private updateMetadataBuffer() {
    const metadataText = `StreamTitle='${this.metadataEmitter.getCurrentTitle()}';`;
    const metadataTextBuffer = Buffer.from(metadataText, 'utf-8');
    const size = Math.ceil(metadataText.length / 16);
    const buffer = Buffer.alloc(size * 16 + 1);
    buffer.writeInt8(size, 0);
    metadataTextBuffer.copy(buffer, 1, 0);
    this.metadataBuffer = buffer;
  }

  private clearMetadataBuffer() {
    this.metadataBuffer = Buffer.alloc(1, 0);
  }

  public _transform(chunk: Buffer, enc: string, callback: (err: Error, data: Buffer) => void) {
    if (this.remainder >= chunk.length) {
      this.remainder -= chunk.length;
      callback(null, chunk);
      return;
    }

    this.push(chunk.slice(0, this.remainder));
    this.push(this.metadataBuffer);
    this.push(chunk.slice(this.remainder));

    this.remainder = this.icyMetadataInterval - (chunk.length - this.remainder);

    this.clearMetadataBuffer();

    callback(null, null);
  }
}

export function withMetadataTransform(
  readable: Readable,
  metadataEmitter: MetadataEmitter,
): Readable {
  return readable
    .pipe(new IcyMetadataTransform(metadataEmitter, config.icyMetadataInterval))
    .on('error', error => readable.destroy(error))
    .on('close', () => readable.destroy());
}
