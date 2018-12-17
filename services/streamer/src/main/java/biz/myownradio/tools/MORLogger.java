package biz.myownradio.tools;

import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.PrintWriter;
import java.util.Arrays;
import java.util.Date;
import java.util.Formatter;

/**
 * Created by Roman on 01.12.14
 */
public class MORLogger {

    final private static String logFile = MORSettings.getString("server.log.dir").orElse("/tmp") + "/stream-server.log";
    final private static PrintWriter pw;

    static {
        PrintWriter pw1;
        try {
            pw1 = new PrintWriter(new FileOutputStream(logFile, true), true);
        } catch (FileNotFoundException e) {
            System.err.println("Logger could not start.");
            pw1 = null;
        }
        pw = pw1;
    }

    final private MessageKind kind;

    public MORLogger(MessageKind kind) {
        this.kind = kind;
    }

    public synchronized void println(String message) {

        String out = String.format("[%s] %s", kind.toString(), message);

        if (pw != null) {
            pw.println(out);
        }

        System.out.println(out);

    }

    public void sprintf(String message) {
        this.println(message);
    }

    public void sprintf(String format, Object... args) {
        this.println(new Formatter().format(format, args).toString());
    }

    public void exception(Throwable e) {
        String title = e.getClass().getName();
        String body = e.getMessage();
        String stack = Arrays.toString(e.getStackTrace());
        this.sprintf("Exception: %s, Message: %s, Stack: %s", title, body, stack);
    }

    public enum MessageKind {
        PLAYER, SERVER, CONCURRENT_BUFFER, BUFFER, PIPE
    }

}


