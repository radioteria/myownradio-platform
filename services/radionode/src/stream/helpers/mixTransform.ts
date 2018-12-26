import { Transform, Readable } from 'stream';
import { identity } from 'lodash';
import { mapBufferAsInt16LE } from '../mixer/mixBuffers';

const STEP = 2;

export class MixTransform extends Transform {
  constructor(private micInput: Readable) {
    super();
  }

  _transform(chunk: Buffer, enc: string, callback: (err: Error, data: Buffer) => void) {
    callback(null, chunk);
  }
}
