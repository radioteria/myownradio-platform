package gemini.myownradio.exception;

import java.io.IOException;

/**
 * Created by Roman on 25.12.2014.
 */
public class ShutdownException extends IOException {
    public ShutdownException() {
        super();
    }

    public ShutdownException(String message) {
        super(message);
    }

    public ShutdownException(String message, Throwable cause) {
        super(message, cause);
    }

    public ShutdownException(Throwable cause) {
        super(cause);
    }
}
