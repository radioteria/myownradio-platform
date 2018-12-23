import { Observable } from 'rxjs';
import { PassThrough, Readable, Writable } from 'stream';
import ffmpeg = require('fluent-ffmpeg');
import writeToStream from './writeToStream';

const DECODER_CHANNELS = 2;
const DECODER_FREQUENCY = 44100;
const DECODER_FORMAT = 's16le';

const ENC_OUTPUT_FORMAT = 'mp3';
const ENC_CHANNELS = 2;
const ENC_BITRATE = '256k';
const ENC_FILTER = 'compand=0 0:1 1:-90/-900 -70/-70 -21/-21 0/-15:0.01:12:0:0';

const encodeMedia = (source: Observable<Buffer>) => {
  return new Observable<Buffer>(observer => {
    const input = new PassThrough();
    const output = new PassThrough();

    ffmpeg(input)
      .inputOptions([`-ac ${DECODER_CHANNELS}`, `-ar ${DECODER_FREQUENCY}`])
      .inputFormat(DECODER_FORMAT)
      .audioBitrate(ENC_BITRATE)
      .audioChannels(ENC_CHANNELS)
      .outputFormat(ENC_OUTPUT_FORMAT)
      .audioFilter(ENC_FILTER)
      .on('error', error => input.emit('error', error))
      .pipe(output);

    const subscription = source.subscribe({
      next: (chunk: Buffer) => {},
      complete: () => {},
      error: (err: Error) => {},
    });

    const complete = () => {
      subscription.unsubscribe();
    };

    return complete;
  });
};

export default encodeMedia;
