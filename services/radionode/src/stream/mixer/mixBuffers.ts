import { sum, range } from 'lodash';

const mixSamples = (samples: number[]): number => {
  if (samples.length === 0) {
    return 0;
  }
  return sum(samples) / samples.length;
};

export const mapBufferAsInt16LE = (buffer: Buffer, cb: (int: number) => number): Buffer => {
  const newBuffer = Buffer.alloc(buffer.length);

  range(0, buffer.length, 2).forEach(offset => {
    const sample = buffer.readInt16LE(offset);
    newBuffer.writeInt16LE(sample / 2, offset);
  });

  return newBuffer;
};

export const mixBuffers = ([master, slave]: [Buffer, Buffer]): [Buffer, Buffer] => {
  const newMaster = mapBufferAsInt16LE(master, sample => {
    return sample;
  });
  return [newMaster, slave];
};
