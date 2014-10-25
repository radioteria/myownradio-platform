package gemini.myownradio.tools;

/**
 * Created by Roman on 06.10.14.
 */
public class ThreadTools {
    public static void Sleep(long time) {
        try {
            Thread.sleep(time);
        } catch (InterruptedException e) {
            throw new RuntimeException(e);
        }
    }
}
