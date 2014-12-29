package gemini.myownradio.tools.io;

import java.io.IOException;
import java.io.InputStream;

/**
 * Created by roman on 29.12.14.
 */
public interface InputSupplier {
    public InputStream open() throws IOException;
}
