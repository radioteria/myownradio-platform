package gemini.myownradio;

import gemini.myownradio.engine.Notif1er;
import gemini.myownradio.tools.io.SharedFile.SharedFileReader;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;

/**
 * Created by Roman on 29.12.2014.
 */
public class TestBuffer {
    public static void main(String[] args) throws IOException, InterruptedException {

        new Notif1er.Event("Hello", 1, 2).send("test");

    }
}
