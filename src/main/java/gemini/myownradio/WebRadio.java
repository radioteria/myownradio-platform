package gemini.myownradio;

/**
 * Created by Roman on 30.09.14.
 */

import gemini.myownradio.engine.FlowListener;
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

        FlowListener.init();

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
                .when(new LHttpContextString("/run"))
                .exec(new GetRunStateHandler());

        httpServer
                .when(new LHttpContextString("/notify"))
                .exec(new SetStreamStateNotifyHandler());

        httpServer
                .when(new LHttpContextString("/audio"))
                .exec(new GetStreamAudioHandler());

    }

}
