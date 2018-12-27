export const concatBuffers = (buffer1: Buffer, buffer2: Buffer): Buffer => {
  const newBuffer = Buffer.alloc(buffer1.length + buffer2.length);
  if (buffer1.length > 0) {
    buffer1.copy(newBuffer, 0, 0, buffer1.length - 1);
  }
  if (buffer2.length > 0) {
    buffer2.copy(newBuffer, buffer1.length, 0, buffer2.length - 1);
  }
  return newBuffer;
};
