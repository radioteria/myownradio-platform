export const DECODER_CHANNELS = 2;
export const DECODER_FREQUENCY = 44100;
export const DECODER_FORMAT = 's16le';
export const DECODER_CODEC = 'pcm_s16le';

export const ENCODER_OUTPUT_FORMAT = 'mp3';
export const ENCODER_CHANNELS = 2;
export const ENCODER_BITRATE = '256k';
export const ENCODER_FILTER = 'compand=0 0:1 1:-90/-900 -70/-70 -21/-21 0/-15:0.01:12:0:0';

export const FADEIN_FILTER = 'afade=t=in:st=0:d=1';

export const KILL_SIGNAL = 'SIGKILL';
