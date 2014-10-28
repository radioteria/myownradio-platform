package gemini.myownradio.tools;

import gemini.myownradio.WebRadio;
import org.apache.log4j.FileAppender;
import org.apache.log4j.Level;
import org.apache.log4j.Logger;
import org.apache.log4j.PatternLayout;

import java.io.File;

/**
 * Created by Roman on 20.10.14.
 */
public class BaseLogger {
    final static Logger logger = Logger.getLogger(WebRadio.class);
    final public static String binLocation =
            new File(MORSettings.urldecode(WebRadio.class.getProtectionDomain().getCodeSource().getLocation().getPath())).getParent();

    public static void writeLog(String message) {
        logger.info(message);
    }

    public static void configure() {
        System.out.println("Initializing logger...");
        FileAppender fa = new FileAppender();
        fa.setFile(MORSettings.getFirstString("server", "server_logfile", "/tmp/mor-radio.log"));
        fa.setLayout(new PatternLayout("%d{yyyy.MM.dd HH:mm:ss.SSS} [%t] %-5p - %m%n"));
        fa.setThreshold(Level.ALL);
        fa.setAppend(true);
        fa.activateOptions();
        Logger.getRootLogger().addAppender(fa);
    }
}
