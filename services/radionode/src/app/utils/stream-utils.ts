import { Readable, Writable } from 'stream';

export const wrap = (target: Writable): Writable =>
  new Writable({
    write(chunk: Buffer, enc: string, callback: () => void): boolean {
      return target.write(chunk, enc, callback);
    },
  });

export const pipeWithError = (src: Readable, dst: Writable) => {
  src.on('error', error => dst.emit('error', error));
  src.pipe(dst);
};

export const unpipeOnCloseOrError = (src: Readable, dst: Writable) => {
  const unpipe = () => {
    src.unpipe(dst);
    src.resume();
  };
  dst.on('close', () => unpipe());
  dst.on('error', () => unpipe());
};

export default {
  wrap,
  pipeWithError,
  unpipeOnCloseOrError,
};
