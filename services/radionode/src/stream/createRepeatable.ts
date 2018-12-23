import { Readable, PassThrough } from 'stream';

export const createRepeatable = (provide: () => Promise<Readable>): Readable => {
  const output = new PassThrough();

  const handleInput = (input: Readable) => {
    return input
      .pipe(
        output,
        { end: false },
      )
      .once('end', () => handleNext());
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
