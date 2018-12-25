import { Readable, PassThrough } from 'stream';
import { EventEmitter } from 'events';

export const restartable = (
  readable: Readable,
  channelId: string,
  restartEmitter: EventEmitter,
): Readable => {
  const pass = new PassThrough();

  const handleRestart = (chId: string) => {
    if (chId !== channelId) {
      return;
    }
    readable.unpipe(pass);
    readable.destroy(new Error(`Restart signal received`));
    pass.end();
  };

  restartEmitter.on('restart', handleRestart);

  readable.on('end', () => {
    restartEmitter.off('restart', handleRestart);
  });

  readable.on('error', () => {
    restartEmitter.off('restart', handleRestart);
  });

  pass.on('error', err => readable.destroy(err));

  readable.pipe(pass);

  return pass;
};
