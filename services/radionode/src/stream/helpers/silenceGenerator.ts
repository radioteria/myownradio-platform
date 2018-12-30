import { Readable } from 'stream';

const SAMPLE_RATE = 44100;
const BIT_DEPTH = 16;
const CHANNELS = 2;
const BYTES_PER_SECOND = (SAMPLE_RATE * BIT_DEPTH * CHANNELS) / 8;

const BUFFER_SIZE = 4096;
const SLEEP_MS = 50;

export class SilenceGenerator extends Readable {
  private bytesRead = 0;
  private startedAt = Date.now();

  public _read(size: number) {
    const chunkSize = Math.min(size, BUFFER_SIZE);
    const chunk = Buffer.alloc(chunkSize, 0x00);

    const bytesElapsed = ((Date.now() - this.startedAt) / 1000) * BYTES_PER_SECOND;

    this.bytesRead += chunkSize;

    if (bytesElapsed < this.bytesRead) {
      setTimeout(() => this.push(chunk), SLEEP_MS);
    } else {
      process.nextTick(() => this.push(chunk));
    }
  }
}
