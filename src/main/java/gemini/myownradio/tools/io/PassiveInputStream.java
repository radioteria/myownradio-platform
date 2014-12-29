package gemini.myownradio.tools.io;

/**
 * Created by roman on 29.12.14.
 */
public class PassiveInputStream {
    private InputSupplier supplier;
    private int capacity;
    private int position;
    private Thread thread;

    final private static double MIN_THRESHOLD = 0.25;
    final private static double MAX_THRESHOLD = 0.90;

    public PassiveInputStream(InputSupplier supplier, int capacity) {
        this.supplier = supplier;
        this.capacity = capacity;
    }

    private void pump() {
        thread = new Thread(() -> {

        });
        thread.start();
    }
}
