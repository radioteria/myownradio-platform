package biz.myownradio.LHttp;

import java.io.IOException;

/**
 * Created by Roman on 15.10.14.
 */

@FunctionalInterface
public interface LHttpHandler {
    public void handle(LHttpProtocol exchange) throws IOException;
}
