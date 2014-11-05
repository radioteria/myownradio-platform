package gemini.myownradio;

/**
 * Created by Roman on 30.09.14.
 */

import gemini.myownradio.light.ContextHandlers.handlerAudio;
import gemini.myownradio.light.ContextHandlers.handlerNotify;
import gemini.myownradio.light.ContextHandlers.handlerRun;
import gemini.myownradio.light.LHttpServer;
import gemini.myownradio.light.context.LHttpContextString;
import gemini.myownradio.tools.BaseLogger;
import gemini.myownradio.tools.MORSettings;

import java.io.IOException;
import java.io.PrintWriter;


public class WebRadio {

    public static void main(String[] args) throws IOException, InterruptedException {

        BaseLogger.writeLog("WebRadio Server " + WebRadio.class.getPackage().getImplementationVersion());
        BaseLogger.writeLog("Starting radio server");

        LHttpServer httpServer = new LHttpServer();

        setConfiguration(httpServer);
        addHandlers(httpServer);

        httpServer.start().join();

    }

    public static void setConfiguration(LHttpServer httpServer) {
        int port = MORSettings.getFirstInteger("server", "listening_port", 7778);
        httpServer.setPort(port);
    }

    public static void addHandlers(LHttpServer httpServer) {

        httpServer
                .createContext(new LHttpContextString("/run"))
                .setHandler(new handlerRun());

        httpServer
                .createContext(new LHttpContextString("/notify"))
                .setHandler(new handlerNotify());

        httpServer
                .createContext(new LHttpContextString("/audio"))
                .setHandler(new handlerAudio());

    }

}
