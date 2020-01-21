import ffmpeg = require('fluent-ffmpeg');
import ffmpegPath = require('ffmpeg-static');
import { PassThrough, Readable } from 'stream';
import * as constants from './constants';
import logger from '../../services/logger';
import config from '../../config';
import { hideUrlsInString } from '../helpers/string';

export interface IProgress {
  frames: null;
  currentFps: null;
  currentKbps: number;
  targetSize: number;
  timemark: string;
  percent: number;
}

const millisToSeconds = (millis: number): number => millis / 1000;

export const decode = (url: string, offset: number, withJingle: boolean = false): Readable => {
  const passThrough = new PassThrough();
  const start = Date.now();

  const decoder = ffmpeg()
    .setFfmpegPath(ffmpegPath)
    .addOption(['-fflags fastseek'])
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

  decoder.on('error', err => {
    logger.warn(`Decoder failed: ${err}`);
    decoder.kill(constants.KILL_SIGNAL);
  });

  decoder.on('start', commandLine => {
    logger.verbose(`Decoder started: ${hideUrlsInString(commandLine)}`);
  });

  decoder.on('end', () => {
    logger.verbose(`Decoder finished`);
  });

  decoder.once('progress', (progress: IProgress) => {
    const real = Date.now();
    const delay = real - start;
    logger.verbose(`Decoder started with delay: ${delay}ms`);
  });

  decoder.pipe(passThrough);

  return passThrough;
};
