import { Readable, PassThrough } from 'stream';
import { MixWithTransform } from './mixWithTransform';
import config from '../../config';
import { decode } from '../ffmpeg/decode';

const BUFFER_SIZE = 32768 * 8;

export const withJingle = (readable: Readable): Readable => {
  const master = new PassThrough();
  const slave = new PassThrough();

  // const timer = setInterval(
  //   () =>
  //     decode(config.jingleFilePath, 0).pipe(
  //       slave,
  //       { end: false },
  //     ),
  //   60000,
  // );

  readable.pipe(new MixWithTransform(slave, BUFFER_SIZE)).pipe(master);

  master.on('error', err => {
    readable.destroy(err);
    // clearInterval(timer);
  });

  return master;
};
