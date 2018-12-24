import ffmpeg = require('fluent-ffmpeg');
import { PassThrough, Readable } from 'stream';
import * as constants from './constants';
import { millisToSeconds } from '../../app/utils/time-utils';

export const decode = (url: string, offset: number): Readable => {
  const passThrough = new PassThrough();

  const decoder = ffmpeg()
    .audioCodec(constants.DECODER_CODEC)
    .audioChannels(constants.DECODER_CHANNELS)
    .audioFrequency(constants.DECODER_FREQUENCY)
    .outputFormat(constants.DECODER_FORMAT)
    .audioFilter(constants.FADEIN_FILTER)
    .input(url)
    .seekInput(millisToSeconds(offset))
    .native()
    .on('error', err => passThrough.emit('error', err));

  // passThrough.on('close', () => decoder.kill(constants.KILL_SIGNAL));

  decoder.pipe(passThrough);

  return passThrough;
};
