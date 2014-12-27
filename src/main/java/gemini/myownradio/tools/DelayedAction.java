package gemini.myownradio.tools;

/**
 * Created by roman on 27.12.14.
 */
public class DelayedAction {

    private Runnable action;

    private boolean cancel = false;
    private long delay;

    private static final MORLogger logger = new MORLogger(MORLogger.MessageKind.SERVER);

    public DelayedAction(Runnable action, long delay) {
        this.action = action;
        this.delay = delay;
        logger.println("Initializing delayed action");
    }

    public void start() {

        Thread thread = new Thread(() -> {

            try { Thread.sleep(delay); }
            catch (InterruptedException e) { /* NOP */ }

            if (!cancel) {
                logger.println("Starting delayed action");
                action.run();
            } else {
                logger.println("Delayed action cancelled");
            }

        });

        thread.setName("Delayed action");
        thread.start();

        logger.println("Delayed action started");

    }

    public void cancel() {
        logger.println("Cancelling action");
        this.cancel = true;
    }
}
