import ffmpeg = require('fluent-ffmpeg');
import { Readable, Writable, PassThrough } from 'stream';

import * as constants from './constants';

export const encode = (input: Readable): Writable => {
  const output = new PassThrough();

  const encoder = ffmpeg(input)
    .inputOptions([`-ac ${constants.DECODER_CHANNELS}`, `-ar ${constants.DECODER_FREQUENCY}`])
    .inputFormat(constants.DECODER_FORMAT)
    .audioBitrate(constants.ENCODER_BITRATE)
    .audioChannels(constants.ENCODER_CHANNELS)
    .outputFormat(constants.ENCODER_OUTPUT_FORMAT)
    .audioFilter(constants.ENCODER_FILTER)
    .on('error', error => input.emit('error', error));

  encoder.pipe(output);

  // output.on('close', () => encoder.kill(constants.KILL_SIGNAL));

  return output;
};
