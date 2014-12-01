package gemini.myownradio.tools;

import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.OutputStream;
import java.io.PrintWriter;
import java.util.Date;
import java.util.Formatter;

/**
 * Created by Roman on 01.12.14.
 */
public class MORLogger {

    final private static String logFile = MORSettings.getFirstString("server", "server_logfile", "/tmp/mor-server.log");
    final private static OutputStream os;

    static {
        OutputStream os1;
        try {
            os1 = new FileOutputStream(logFile, true);
        } catch (FileNotFoundException e) {
            os1 = null;
        }
        os = os1;
    }

    final private MessageKind kind;
    final private PrintWriter pw;

    public MORLogger(MessageKind kind) {
        this.kind = kind;
        this.pw = this.os != null ? new PrintWriter(os) : null;
    }

    public void println(String message) {

        String date = new Date().toString();
        String thread = Thread.currentThread().getName();
        String out = String.format("[%s] [%s] [%s] %s", date, kind.toString(), thread, message);

        if (pw != null) {
            this.pw.println(out);
        }

        System.out.println(out);

    }

    public void sprintf(String format, Object... args) {
        this.println(new Formatter().format(format, args).toString());
    }

    public enum MessageKind {
        PLAYER, SERVER, CONCURRENT_BUFFER
    }

}


