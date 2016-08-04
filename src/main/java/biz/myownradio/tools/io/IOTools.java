package biz.myownradio.tools.io;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;

/**
 * Created by Roman on 30.12.2014.
 */
public class IOTools {
    final public static int DEFAULT_BUFFER_SIZE = 1024;
    final public static boolean DEFAULT_AUTO_FLUSH = true;
    final public static boolean DEFAULT_AUTO_CLOSE = true;

    public static void copy(InputStream is, OutputStream os) throws IOException {
        copy(is, os, DEFAULT_AUTO_FLUSH);
    }

    public static void copy(InputStream is, OutputStream os, boolean autoFlush) throws IOException {
        byte[] buffer = new byte[DEFAULT_BUFFER_SIZE];
        int length;
        while((length = is.read(buffer, 0, buffer.length)) != -1) {
            os.write(buffer, 0, length);
            if (autoFlush) {
                os.flush();
            }
        }
    }
}
