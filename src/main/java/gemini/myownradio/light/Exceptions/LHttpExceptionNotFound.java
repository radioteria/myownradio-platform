package gemini.myownradio.light.Exceptions;

import gemini.myownradio.light.LHttpStatus;

/**
 * Created by Roman on 16.10.14.
 */
public class LHttpExceptionNotFound extends LHttpException {
    public LHttpExceptionNotFound() {
        super(LHttpStatus.STATUS_404);
    }
}
