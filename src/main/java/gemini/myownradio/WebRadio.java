package gemini.myownradio;

/**
 * Created by Roman on 30.09.14.
 */

import gemini.myownradio.light.ContextHandlers.handlerAudio;
import gemini.myownradio.light.ContextHandlers.handlerImage;
import gemini.myownradio.light.ContextHandlers.handlerNotify;
import gemini.myownradio.light.ContextHandlers.handlerRun;
import gemini.myownradio.light.LHttpContextRegexp;
import gemini.myownradio.light.LHttpContextString;
import gemini.myownradio.tools.BaseLogger;
import gemini.myownradio.light.LHttpServer;
import gemini.myownradio.tools.MORSettings;

import java.io.IOException;


public class WebRadio {

    public static void main(String... args) {

        BaseLogger.configure();
        BaseLogger.writeLog("WebRadio Server " + WebRadio.class.getPackage().getImplementationVersion());
        BaseLogger.writeLog("Starting radio server");

        LHttpServer httpServer = new LHttpServer();

        int configuredPort = MORSettings.getFirstInteger("server", "listening_port", 7778);

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

        httpServer
                .createContext(new LHttpContextRegexp(".jpg$"))
                .setHandler(new handlerImage());

        try {
            httpServer.start().join();
        } catch (IOException | InterruptedException e) {
            e.printStackTrace();
        }

    }

}
