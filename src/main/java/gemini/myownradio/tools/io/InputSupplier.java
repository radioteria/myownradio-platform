package gemini.myownradio.tools.io;

import java.io.IOException;
import java.io.InputStream;
import java.util.concurrent.Callable;

/**
 * Created by roman on 29.12.14.
 */
public interface InputSupplier {
    public InputStream open(Callable<InputStream> callable) throws IOException;
}
