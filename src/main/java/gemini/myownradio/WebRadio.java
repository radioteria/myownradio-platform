package gemini.myownradio;

/**
 * Created by Roman on 30.09.14.
 */

import gemini.myownradio.light.ContextHandlers.GetRunStateHandler;
import gemini.myownradio.light.ContextHandlers.GetStreamAudioHandler;
import gemini.myownradio.light.ContextHandlers.SetStreamStateNotifyHandler;
import gemini.myownradio.light.LHttpServer;
import gemini.myownradio.light.context.LHttpContextString;
import gemini.myownradio.tools.MORLogger;
import gemini.myownradio.tools.MORSettings;

import java.io.IOException;

public class WebRadio {

    public static void main(String[] args) throws IOException, InterruptedException {

        MORLogger log = new MORLogger(MORLogger.MessageKind.SERVER);

        log.println("Starting WebRadio Server");

        LHttpServer httpServer = new LHttpServer();
        int listeningPort = MORSettings.getFirstInteger("server", "listening_port", 7778);

        log.sprintf("Starting listening on port %s", listeningPort);
        httpServer.setPort(listeningPort);

        registerRequestHandlers(httpServer);

        httpServer.start();

    }

    public static void registerRequestHandlers(LHttpServer httpServer) {

        httpServer
                .createContext(new LHttpContextString("/run"))
                .setHandler(new GetRunStateHandler());

        httpServer
                .createContext(new LHttpContextString("/notify"))
                .setHandler(new SetStreamStateNotifyHandler());

        httpServer
                .createContext(new LHttpContextString("/audio"))
                .setHandler(new GetStreamAudioHandler());

    }

}
