package gemini.myownradio.exception;

/**
 * Created by Roman on 01.10.14.
 */
public class RadioIncorrectBitrateException extends RadioException {
    public RadioIncorrectBitrateException() {
        super();
    }

    public RadioIncorrectBitrateException(String message) {
        super(message);
    }

    public RadioIncorrectBitrateException(String message, Throwable cause) {
        super(message, cause);
    }

    public RadioIncorrectBitrateException(Throwable cause) {
        super(cause);
    }

    protected RadioIncorrectBitrateException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
