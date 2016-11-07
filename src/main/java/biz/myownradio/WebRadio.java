package biz.myownradio;

/**
 * Created by Roman on 30.09.14.
 */

import biz.myownradio.LHttp.ContextHandlers.GetRunStateHandler;
import biz.myownradio.LHttp.ContextHandlers.GetStreamAudioHandler;
import biz.myownradio.LHttp.ContextHandlers.SetStreamStateNotifyHandler;
import biz.myownradio.LHttp.ContextObjects.LHttpContextString;
import biz.myownradio.LHttp.LHttpException;
import biz.myownradio.LHttp.LHttpHandler;
import biz.myownradio.LHttp.LHttpProtocol;
import biz.myownradio.LHttp.LHttpServer;
import biz.myownradio.tools.MORLogger;
import biz.myownradio.tools.MORSettings;

import java.io.IOException;

public class WebRadio {

    public static void main(String[] args) throws IOException, InterruptedException {

        int listeningPort = MORSettings.getInteger("server.port").orElse(7778);

        MORLogger log = new MORLogger(MORLogger.MessageKind.SERVER);

        log.println("Starting WebRadio Server");

        LHttpServer httpServer = new LHttpServer();

        log.sprintf("Server is starting listening on port: %s", listeningPort);

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

        httpServer
                .when(new LHttpContextString("/error"))
                .exec(exchange -> {
                    throw LHttpException.serverError("Something wrong...");
                });

    }

}
