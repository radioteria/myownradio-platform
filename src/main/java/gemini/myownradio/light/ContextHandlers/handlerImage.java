package gemini.myownradio.light.ContextHandlers;

import gemini.myownradio.light.LHttpContextInterface;
import gemini.myownradio.light.LHttpHandler;
import gemini.myownradio.light.LHttpProtocol;

import java.io.IOException;

/**
 * Created by Roman on 28.10.14.
 */
public class handlerImage extends LHttpHandler {
    @Override
    public void handler(LHttpProtocol exchange) throws IOException {
        exchange.setContentType("text/html");
        exchange.getPrinter().println("Hello, World!");
    }
}
