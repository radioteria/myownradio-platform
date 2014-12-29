package gemini.myownradio;

import gemini.myownradio.tools.io.AsyncInputStreamBuffer;

import java.io.IOException;
import java.io.InputStream;
import java.net.URL;

/**
 * Created by Roman on 29.12.2014.
 */
public class TestBuffer {
    public static void main(String[] args) throws IOException, InterruptedException {

        InputStream source;
        InputStream cache;

        URL url = new URL("http://www.audiopoisk.com/file/IVQb4Lev9v/scorpions/wind-of-change-6013.mp3");

        source = url.openStream();

        cache = new AsyncInputStreamBuffer(source, 100000000);

        byte[] buffer = new byte[4096];

        int length;

        while ((length = cache.read(buffer)) != -1) {
            //System.out.println(new String(buffer, 0, length));
            System.out.println(length);
            //Thread.sleep(100);
        }

    }
}
