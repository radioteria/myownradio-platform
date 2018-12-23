import ffmpeg = require('fluent-ffmpeg');
import { Observable, Subject } from 'rxjs';
import { PassThrough } from 'stream';
import { millisToSeconds } from '../app/utils/time-utils';

const DECODER_CHANNELS = 2;
const DECODER_FREQUENCY = 44100;
const DECODER_FORMAT = 's16le';
const AUDIO_CODEC = 'pcm_s16le';

const FADEIN_FILTER = 'afade=t=in:st=0:d=1';

const KILL_SIGNAL = 'SIGINT';

const decodeMedia = (
  url: string,
  offset: number,
  pauseSubject: Subject<boolean>,
): Observable<Buffer> => {
  return new Observable(observer => {
    let killed = false;

    const passThrough = new PassThrough();

    const pauseSubscription = pauseSubject.subscribe(pause => {
      pause ? passThrough.pause() : passThrough.resume();
    });

    const handleError = (err: Error) => {
      pauseSubscription.unsubscribe();
      killed ? observer.complete() : observer.error(err);
    };

    const handleEnd = () => {
      pauseSubscription.unsubscribe();
      observer.complete();
    };

    const handleData = (data: Buffer) => {
      observer.next(data);
    };

    const coder = ffmpeg()
      .audioCodec(AUDIO_CODEC)
      .audioChannels(DECODER_CHANNELS)
      .audioFrequency(DECODER_FREQUENCY)
      .outputFormat(DECODER_FORMAT)
      .audioFilter(FADEIN_FILTER)
      .input(url)
      .seekInput(millisToSeconds(offset))
      .native()
      .on('error', handleError)
      .on('end', handleEnd);

    passThrough.on('data', handleData);

    coder.pipe(passThrough);

    return () => {
      if (!killed) {
        killed = true;
        coder.kill(KILL_SIGNAL);
      }
    };
  });
};

export default decodeMedia;
