package gemini.myownradio.light.Exceptions;

import gemini.myownradio.light.LHttpStatus;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpExceptionBadRequest extends LHttpException {
    public LHttpExceptionBadRequest() {
        super(LHttpStatus.STATUS_400);
    }
}
