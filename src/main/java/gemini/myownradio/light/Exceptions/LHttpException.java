package gemini.myownradio.light.Exceptions;

import gemini.myownradio.light.LHttpStatus;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpException extends RuntimeException {
    private LHttpStatus status;

    public LHttpException(LHttpStatus status) {
        this.status = status;
    }

    public LHttpStatus getStatus() {
        return status;
    }
}
