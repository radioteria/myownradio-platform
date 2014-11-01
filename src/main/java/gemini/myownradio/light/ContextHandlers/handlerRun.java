package gemini.myownradio.light.ContextHandlers;

import gemini.myownradio.engine.buffer.ConcurrentBufferRepository;
import gemini.myownradio.light.LHttpHandler;
import gemini.myownradio.light.LHttpProtocol;

import java.io.IOException;
import java.io.PrintWriter;
import java.text.DecimalFormat;
import java.text.DecimalFormatSymbols;

/**
 * Created by Roman on 16.10.14.
 */
public class handlerRun implements LHttpHandler {

    public void handler(LHttpProtocol exchange) throws IOException {

        Runtime rt = Runtime.getRuntime();

        DecimalFormat df = new DecimalFormat("###,###,##0");
        DecimalFormatSymbols symbols = df.getDecimalFormatSymbols();
        symbols.setGroupingSeparator(' ');
        df.setDecimalFormatSymbols(symbols);

        exchange.setContentType("text/plain");

        PrintWriter out = exchange.getPrinter();

        out.println("Free Memory    : " + String.format("%7s", df.format(rt.freeMemory() >> 10)) + "K");
        out.println("Total Memory   : " + String.format("%7s", df.format(rt.totalMemory() >> 10)) + "K");
        out.println("");
        out.println("Active threads : " + Thread.activeCount() + "\n");

        for (Thread t : Thread.getAllStackTraces().keySet()) {
            if (t.getThreadGroup().getName().equals("main")) {
                out.println(" * " + t.getName());
            }
        }

        out.println("");
        out.println("Active streamers:");
        out.println("");

        ConcurrentBufferRepository
                .getKeys()
                .forEach((value) -> System.out.println( " * " + value.toString()));

        out.println("");
        out.println("You're: " + exchange.getHeader("X-Forwarded-For"));

        exchange.flush();

    }
}
