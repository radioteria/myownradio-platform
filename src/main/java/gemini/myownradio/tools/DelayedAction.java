package gemini.myownradio.tools;

/**
 * Created by roman on 27.12.14.
 */
public class DelayedAction {

    private Runnable action;

    private boolean cancel = false;
    private long delay;

    public DelayedAction(Runnable action, long delay) {
        this.action = action;
        this.delay = delay;
    }

    public void start() {

        Thread thread = new Thread(() -> {

            try { Thread.sleep(delay); }
            catch (InterruptedException e) { /* NOP */ }

            if (!cancel) {
                action.run();
            }

        });

        thread.start();

    }

    public void cancel() {
        this.cancel = true;
    }
}
