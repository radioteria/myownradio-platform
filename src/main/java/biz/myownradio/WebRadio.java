package biz.myownradio;

/**
 * Created by Roman on 30.09.14
 */

import biz.myownradio.LHttp.ContextHandlers.GetRunStateHandler;
import biz.myownradio.LHttp.ContextHandlers.GetStreamAudioHandler;
import biz.myownradio.LHttp.ContextHandlers.SetStreamStateNotifyHandler;
import biz.myownradio.LHttp.ContextObjects.LHttpContextString;
import biz.myownradio.LHttp.LHttpServer;
import biz.myownradio.tools.Logger;
import biz.myownradio.tools.MORSettings;

import java.io.IOException;

public class WebRadio {

    public static void main(String[] args) throws IOException, InterruptedException
    {
        int listeningPort = MORSettings.getInteger("server.port");

        Logger log = new Logger(Logger.MessageKind.SERVER);

        log.print("Starting WebRadio Server");

        LHttpServer httpServer = new LHttpServer();

        log.printf("Server is starting listening on port: %s", listeningPort);

        httpServer.setPort(listeningPort);

        registerRequestHandlers(httpServer);

        httpServer.start();

    }

    private static void registerRequestHandlers(LHttpServer httpServer) {

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
