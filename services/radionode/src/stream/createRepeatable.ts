import { Readable, PassThrough } from 'stream';

export const createRepeatable = (provide: () => Promise<Readable>): Readable => {
  const output = new PassThrough();

  const handleInput = (input: Readable) => {
    return input
      .once('end', () => handleNext())
      .pipe(
        output,
        { end: false },
      );
  };

  const handleError = (err: Error) => {
    output.emit('error', err);
  };

  const handleNext = () => {
    provide().then(handleInput, handleError);
  };

  handleNext();

  return output;
};
