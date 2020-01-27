package biz.myownradio.exception;

/**
 * Created by Roman on 01.10.14.
 */
public class RadioException extends Exception {
    public RadioException() {
        super();
    }

    public RadioException(String message) {
        super(message);
    }

    public RadioException(String message, Throwable cause) {
        super(message, cause);
    }

    public RadioException(Throwable cause) {
        super(cause);
    }

    protected RadioException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
