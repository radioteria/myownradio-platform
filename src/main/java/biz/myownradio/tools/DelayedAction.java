package biz.myownradio.tools;

/**
 * Created by roman on 27.12.14.
 */
public class DelayedAction {

    private Runnable action;

    private boolean cancel = false;
    private long delay;

    private Thread thread;

    private static final Logger logger = new Logger(Logger.MessageKind.SERVER);

    public DelayedAction(Runnable action, long delay) {
        this.action = action;
        this.delay = delay;
        logger.print("Initializing delayed action");
    }

    public void start() {

        thread = new Thread(() -> {

            try { Thread.sleep(delay); }
            catch (InterruptedException e) { /* NOP */ }

            if (!cancel) {
                logger.print("Delayed action started");
                action.run();
            } else {
                logger.print("Delayed action cancelled");
            }

        });

        thread.setName("Delayed action");
        thread.start();

    }

    public void cancel() {
        this.cancel = true;
        this.thread.interrupt();
    }
}
