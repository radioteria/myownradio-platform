import ffmpeg = require('fluent-ffmpeg');
import { PassThrough, Readable } from 'stream';
import * as constants from './constants';
import logger from '../../services/logger';
import config from '../../config';

const millisToSeconds = (millis: number): number => millis / 1000;

export const decode = (url: string, offset: number, withJingle: boolean = false): Readable => {
  const passThrough = new PassThrough();

  const decoder = ffmpeg()
    .audioCodec(constants.DECODER_CODEC)
    .audioChannels(constants.DECODER_CHANNELS)
    .audioFrequency(constants.DECODER_FREQUENCY)
    .outputFormat(constants.DECODER_FORMAT)
    .input(url)
    .seekInput(millisToSeconds(offset))
    .native();

  if (withJingle) {
    decoder.input(config.jingleFilePath).complexFilter(constants.JINGLE_FILTER, []);
  } else {
    decoder.audioFilter(constants.FADEIN_FILTER);
  }

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
