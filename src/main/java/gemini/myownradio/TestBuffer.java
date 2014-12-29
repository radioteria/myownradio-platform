package gemini.myownradio;

import gemini.myownradio.tools.io.AsyncInputStreamBuffer;

import java.io.IOException;
import java.io.InputStream;
import java.net.MalformedURLException;
import java.net.URL;

/**
 * Created by Roman on 29.12.2014.
 */
public class TestBuffer {
    public static void main(String[] args) throws IOException {

        InputStream source;
        InputStream cache;

        URL url = new URL("http://ftp.heanet.ie/pub/ubuntu-cdimage/releases/quantal/release/ubuntu-12.10-server-armhf+omap.img");

        source = url.openStream();

        cache = new AsyncInputStreamBuffer(source, 1000000);

    }
}
