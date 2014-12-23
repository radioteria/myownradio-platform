package gemini.myownradio.LHttp;

import java.io.IOException;

/**
 * Created by Roman on 15.10.14.
 */

@FunctionalInterface
public interface LHttpHandler {
    public void handler(LHttpProtocol exchange) throws IOException;
}
