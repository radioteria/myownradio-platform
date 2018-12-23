import ffmpeg = require('fluent-ffmpeg');
import { Observable } from 'rxjs';
import { millisToSeconds } from '../app/utils/time-utils';

const DECODER_CHANNELS = 2;
const DECODER_FREQUENCY = 44100;
const DECODER_FORMAT = 's16le';
const AUDIO_CODEC = 'pcm_s16le';

const FADEIN_FILTER = 'afade=t=in:st=0:d=1';

const KILL_SIGNAL = 'SIGINT';

const decode = (url: string, offset: number = 0): Observable<Buffer> => {
  return new Observable(observer => {
    let killed = false;

    const coder = ffmpeg()
      .audioCodec(AUDIO_CODEC)
      .audioChannels(DECODER_CHANNELS)
      .audioFrequency(DECODER_FREQUENCY)
      .outputFormat(DECODER_FORMAT)
      .audioFilter(FADEIN_FILTER)
      .input(url)
      .seekInput(millisToSeconds(offset))
      .native()
      .on('error', err => (killed ? observer.complete() : observer.error(err)))
      .on('data', data => observer.next(data))
      .on('end', () => observer.complete());

    return () => {
      if (!killed) {
        killed = true;
        coder.kill(KILL_SIGNAL);
      }
    };
  });
};

export default decode;
