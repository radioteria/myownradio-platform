package gemini.myownradio.exception;

/**
 * Created by Roman on 01.10.14.
 */
public class RadioNothingPlayingException extends RadioException {
    public RadioNothingPlayingException() {
        super();
    }

    public RadioNothingPlayingException(String message) {
        super(message);
    }

    public RadioNothingPlayingException(String message, Throwable cause) {
        super(message, cause);
    }

    public RadioNothingPlayingException(Throwable cause) {
        super(cause);
    }

    protected RadioNothingPlayingException(String message, Throwable cause, boolean enableSuppression, boolean writableStackTrace) {
        super(message, cause, enableSuppression, writableStackTrace);
    }
}
