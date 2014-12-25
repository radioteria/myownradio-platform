package gemini.myownradio.exception;

import java.io.IOException;

/**
 * Created by Roman on 25.12.2014.
 */
public class NoConsumersException extends IOException {
    public NoConsumersException() {
        super();
    }

    public NoConsumersException(String message) {
        super(message);
    }

    public NoConsumersException(String message, Throwable cause) {
        super(message, cause);
    }

    public NoConsumersException(Throwable cause) {
        super(cause);
    }
}
