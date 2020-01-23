import { Transform, Readable } from 'stream';
import { mixInt16LEBuffers } from '../buffers/mixBuffers';
import { concatBuffers } from '../buffers/concatBuffers';

// todo: Think about correct name for mixingReadable

export class MixWithTransform extends Transform {
  private mixingBuffer: Buffer = Buffer.alloc(0);

  constructor(private mixingReadable: Readable, private bufferSize: number) {
    super();
    this.readMixingReadable();
  }

  public _transform(chunk: Buffer, enc: string, callback: (err: Error, data: Buffer) => void) {
    const resultChunk = Buffer.alloc(chunk.length);

    chunk.copy(resultChunk, 0, 0, chunk.length);

    if (this.mixingBuffer.length > 0) {
      const mixedChunk = mixInt16LEBuffers(chunk, this.mixingBuffer);
      mixedChunk.copy(resultChunk, 0, 0, mixedChunk.length);

      this.mixingBuffer = this.mixingBuffer.slice(mixedChunk.length);

      if (this.mixingBuffer.length < this.bufferSize && this.mixingReadable.isPaused()) {
        this.mixingReadable.resume();
      }
    }

    callback(null, resultChunk);
  }

  private readMixingReadable() {
    this.mixingReadable.on('data', (data: Buffer) => {
      this.mixingBuffer = concatBuffers(this.mixingBuffer, data);
      if (this.mixingBuffer.length >= this.bufferSize && !this.mixingReadable.isPaused()) {
        this.mixingReadable.pause();
      }
    });
  }
}
