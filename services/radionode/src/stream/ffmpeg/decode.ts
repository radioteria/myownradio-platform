import ffmpeg = require('fluent-ffmpeg');
import { PassThrough, Readable } from 'stream';
import * as constants from './constants';
import { millisToSeconds } from '../../app/utils/time-utils';
import logger from '../../services/logger';

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
    .native();

  decoder.pipe(passThrough);

  decoder.on('error', err => {
    logger.warn(`Decoder failed: ${err}`);
    decoder.kill(constants.KILL_SIGNAL);
  });

  decoder.on('start', commandLine => {
    logger.verbose(`Decoder started: ${commandLine}`);
  });

  decoder.on('end', () => {
    logger.verbose(`Decoder finished`);
  });

  return passThrough;
};
