package gemini.myownradio.LHttp.ContextHandlers;

import gemini.myownradio.LHttp.LHttpHandler;
import gemini.myownradio.LHttp.LHttpProtocol;
import gemini.myownradio.tools.ClientCounter;

import java.io.IOException;
import java.io.PrintWriter;
import java.text.DecimalFormat;
import java.text.DecimalFormatSymbols;

/**
 * Created by Roman on 16.10.14.
 */
public class GetRunStateHandler implements LHttpHandler {

    public void handle(LHttpProtocol exchange) throws IOException {

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

        Thread.getAllStackTraces().keySet().stream()
                .filter(t -> t.getThreadGroup().getName().equals("main"))
                .forEach(t -> { out.println(" * " + t.getName()); });

        out.println("");
        out.println("Active clients:");

        ClientCounter.getClients().values().stream()
                .forEach(t -> { out.println(" * " + t.get()); });

        exchange.flush();

    }
}
