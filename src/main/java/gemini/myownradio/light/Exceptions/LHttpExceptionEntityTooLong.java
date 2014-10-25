package gemini.myownradio.light.Exceptions;

import gemini.myownradio.light.LHttpStatus;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpExceptionEntityTooLong extends LHttpException {
    public LHttpExceptionEntityTooLong() {
        super(LHttpStatus.STATUS_413);
    }
}
