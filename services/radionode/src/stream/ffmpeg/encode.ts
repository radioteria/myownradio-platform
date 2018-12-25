import ffmpeg = require('fluent-ffmpeg');
import { Readable, Writable, PassThrough } from 'stream';

import * as constants from './constants';
import logger from '../../services/logger';

export const encode = (input: Readable, closeInputOnError: boolean): Writable => {
  const output = new PassThrough();

  const encoder = ffmpeg(input)
    .inputOptions([`-ac ${constants.DECODER_CHANNELS}`, `-ar ${constants.DECODER_FREQUENCY}`])
    .inputFormat(constants.DECODER_FORMAT)
    .audioBitrate(constants.ENCODER_BITRATE)
    .audioChannels(constants.ENCODER_CHANNELS)
    .outputFormat(constants.ENCODER_OUTPUT_FORMAT)
    .audioFilter(constants.ENCODER_FILTER);

  encoder.pipe(output);

  encoder.on('error', err => {
    logger.warn(`Encoder failed: ${err}`);
    closeInputOnError && input.destroy(err);
    encoder.kill(constants.KILL_SIGNAL);
  });

  encoder.on('start', commandLine => {
    logger.verbose(`Encoder started: ${commandLine}`);
  });

  encoder.on('end', () => {
    logger.verbose(`Encoder finished`);
  });

  return output;
};
