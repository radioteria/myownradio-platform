package gemini.myownradio;

import gemini.myownradio.engine.Notif1er;

import java.io.IOException;

/**
 * Created by Roman on 29.12.2014.
 */
public class TestBuffer {
    public static void main(String[] args) throws IOException, InterruptedException {

        new Notif1er.Event("Hello", 1, 2).queue("test");

    }
}
