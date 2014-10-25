package gemini.myownradio.light.Exceptions;

import gemini.myownradio.light.LHttpStatus;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpExceptionMethodNotImplemented extends LHttpException {
    public LHttpExceptionMethodNotImplemented() {
        super(LHttpStatus.STATUS_501);
    }
}
