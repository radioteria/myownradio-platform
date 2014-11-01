package gemini.myownradio.light;

import java.io.IOException;

/**
 * Created by Roman on 15.10.14.
 */

public interface LHttpHandler {
    public void handler(LHttpProtocol exchange) throws IOException;
}
