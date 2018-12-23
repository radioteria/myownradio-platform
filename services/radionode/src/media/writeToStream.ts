import { Writable } from 'stream';
import { Observable, Subject } from 'rxjs';

const writeToStream = (writable: Writable, pauseSubject: Subject<boolean>) => (
  source: Observable<Buffer>,
): Observable<void> => {
  return new Observable(observer => {
    const handleError = (error: Error) => {
      complete();
      observer.error(error);
    };
    const handleDrain = () => {
      pauseSubject.next(false);
    };
    const handleClose = () => {
      complete();
      observer.complete();
    };
    const handleFinish = () => {
      complete();
      observer.complete();
    };

    writable.on('error', handleError);
    writable.on('drain', handleDrain);
    writable.on('close', handleClose);
    writable.on('finish', handleFinish);

    const subscription = source.subscribe({
      next: chunk => {
        if (!writable.write(chunk)) {
          pauseSubject.next(true);
        }
      },
      complete: () => {
        complete();
        observer.complete();
      },
      error: (err: Error) => {
        complete();
        observer.error(err);
      },
    });

    const complete = () => {
      subscription.unsubscribe();
      writable.off('error', handleError);
      writable.off('drain', handleDrain);
      writable.off('close', handleClose);
      writable.off('finish', handleFinish);
    };

    return complete;
  });
};

export default writeToStream;
