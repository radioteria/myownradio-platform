import { Readable, PassThrough } from 'stream';

export const repeat = (provideReadable: () => Promise<Readable>): Readable => {
  const output = new PassThrough();
  let currentInput: Readable;

  output.on('error', err => {
    currentInput && currentInput.destroy(err);
  });

  const handleInput = (input: Readable) => {
    currentInput = input;

    currentInput
      .once('end', () => getNext())
      .pipe(
        output,
        { end: false },
      );
  };

  const handleError = (err: Error) => {
    output.destroy(err);
  };

  const getNext = () => {
    provideReadable().then(handleInput, handleError);
  };

  getNext();

  return output;
};
