package gemini.myownradio;

/**
 * Created by Roman on 30.09.14.
 */

import gemini.myownradio.light.ContextHandlers.handlerAudio;
import gemini.myownradio.light.ContextHandlers.handlerNotify;
import gemini.myownradio.light.ContextHandlers.handlerRun;
import gemini.myownradio.light.LHttpContextString;
import gemini.myownradio.tools.BaseLogger;
import gemini.myownradio.tools.MORConfig;
import gemini.myownradio.light.LHttpServer;

import java.io.File;
import java.io.IOException;


public class WebRadio {

    public static void main(String... args) {

        MORConfig.init();
        BaseLogger.configure();

        BaseLogger.writeLog("WebRadio Server " + WebRadio.class.getPackage().getImplementationVersion());
        BaseLogger.writeLog("We're in " + MORConfig.binLocation);
        BaseLogger.writeLog("Starting radio server");

        LHttpServer httpServer = new LHttpServer();

        int configuredPort = Integer.parseInt(MORConfig.getRoot()
                .getChild("server").getChild("listening-port").getValue());

        httpServer.setPort(configuredPort);

        httpServer
                .createContext(new LHttpContextString("/run"))
                .setHandler(new handlerRun());

        httpServer
                .createContext(new LHttpContextString("/notify"))
                .setHandler(new handlerNotify());

        httpServer
                .createContext(new LHttpContextString("/audio"))
                .setHandler(new handlerAudio());



        try {
            httpServer.start().join();
        } catch (IOException | InterruptedException e) {
            e.printStackTrace();
        }

    }

}
