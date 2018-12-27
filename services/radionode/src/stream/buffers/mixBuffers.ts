const INT_16LE_SIZE = 2;

export const mixInt16LEBuffers = (first: Buffer, second: Buffer): Buffer => {
  const bytesMixed = Math.min(first.length, second.length);
  const mixedBuffer = Buffer.alloc(bytesMixed);
  for (let offset = 0; offset < bytesMixed; offset += INT_16LE_SIZE) {
    const firstSample = first.readInt16LE(offset);
    const secondSample = second.readInt16LE(offset);
    const mixedSample = Math.floor((firstSample + secondSample) / 2);
    mixedBuffer.writeInt16LE(offset, mixedSample);
  }
  return mixedBuffer;
};
