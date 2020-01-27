package biz.myownradio.exception;

/**
 * Created by Roman on 01.10.14.
 */
public class RadioStreamNotFoundException extends RadioException {
    public RadioStreamNotFoundException() {
        super();
    }

    public RadioStreamNotFoundException(String message) {
        super(message);
    }

    public RadioStreamNotFoundException(String message, Throwable cause) {
        super(message, cause);
    }

    public RadioStreamNotFoundException(Throwable cause) {
        super(cause);
    }

    protected RadioStreamNotFoundException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
