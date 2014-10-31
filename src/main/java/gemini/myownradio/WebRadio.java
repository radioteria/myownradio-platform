package gemini.myownradio;

/**
 * Created by Roman on 30.09.14.
 */

import gemini.myownradio.light.ContextHandlers.handlerAudio;
import gemini.myownradio.light.ContextHandlers.handlerNotify;
import gemini.myownradio.light.ContextHandlers.handlerRun;
import gemini.myownradio.light.LHttpServer;
import gemini.myownradio.light.context.LHttpContextPostfix;
import gemini.myownradio.light.context.LHttpContextPrefix;
import gemini.myownradio.light.context.LHttpContextRegexp;
import gemini.myownradio.light.context.LHttpContextString;
import gemini.myownradio.tools.BaseLogger;
import gemini.myownradio.tools.MORSettings;

import java.io.IOException;
import java.io.PrintWriter;


public class WebRadio {

    public static void main(String... args) {

        BaseLogger.writeLog("WebRadio Server " + WebRadio.class.getPackage().getImplementationVersion());
        BaseLogger.writeLog("Starting radio server");

        LHttpServer httpServer = new LHttpServer();

        setConfiguration(httpServer);
        setHandlers(httpServer);

        try {
            httpServer.start().join();
        } catch (IOException | InterruptedException e) {
            e.printStackTrace();
        }

    }

    public static void setConfiguration(LHttpServer httpServer) {
        int port = MORSettings.getFirstInteger("server", "listening_port", 7778);
        httpServer.setPort(port);
    }

    public static void setHandlers(LHttpServer httpServer) {

        httpServer
                .createContext(new LHttpContextString("/run"))
                .setHandler(new handlerRun());

        httpServer
                .createContext(new LHttpContextString("/notify"))
                .setHandler(new handlerNotify());

        httpServer
                .createContext(new LHttpContextString("/audio"))
                .setHandler(new handlerAudio());

        /**
         * Next contexts created only for testing purposes
         */
        httpServer
                .createContext(new LHttpContextRegexp(".jpg$"))
                .setHandler(exchange -> {
                    exchange.setContentType("text/plain");
                    PrintWriter pw = exchange.getPrinter();
                    pw.println(".jpg$");
                });

        httpServer
                .createContext(new LHttpContextRegexp("picture\\.jpg$"))
                .setHandler(exchange -> {
                    exchange.setContentType("text/plain");
                    PrintWriter pw = exchange.getPrinter();
                    pw.println("picture\\.jpg$");
                });

        httpServer
                .createContext(new LHttpContextPrefix("/files/"))
                .setHandler(exchange -> {
                    exchange.setContentType("text/plain");
                    PrintWriter pw = exchange.getPrinter();
                    pw.println("You're in prefix /files/*");
                });

        httpServer
                .createContext(new LHttpContextPostfix("style.css"))
                .setHandler(exchange -> {
                    exchange.setContentType("text/plain");
                    PrintWriter pw = exchange.getPrinter();
                    pw.println("You're in postfix /style.css");
                });

        httpServer
                .createContext(new LHttpContextRegexp("\\.php$"))
                .setHandler(exchange -> {
                    exchange.setContentType("text/html;charset=utf8");
                    PrintWriter pw = exchange.getPrinter();
                    pw.println("<!DOCTYPE html>");
                    pw.println("<html>");
                    pw.println("<body><center><img style='padding-top:100px' src='http://myownradio.biz/images/java_powered.png' /></center></body>");
                    pw.println("</html>");
                });
    }

}
