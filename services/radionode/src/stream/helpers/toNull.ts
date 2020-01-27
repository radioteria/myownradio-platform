import { Writable } from 'stream';

export default function toNull(): Writable {
  return new Writable({
    write(chunk, enc, cb: (error?: Error | null) => void) {
      cb();
    },
    final(cb: (error?: Error | null) => void): void {
      cb();
    },
  });
}
