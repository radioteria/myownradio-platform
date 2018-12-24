import { Readable, PassThrough } from 'stream';

export const repeat = (provideReadable: () => Promise<Readable>): Readable => {
  const output = new PassThrough();

  const handleInput = (input: Readable) => {
    return input
      .once('end', () => getNext())
      .pipe(
        output,
        { end: false },
      );
  };

  const handleError = (err: Error) => {
    output.emit('error', err);
  };

  const getNext = () => {
    provideReadable().then(handleInput, handleError);
  };

  getNext();

  return output;
};
