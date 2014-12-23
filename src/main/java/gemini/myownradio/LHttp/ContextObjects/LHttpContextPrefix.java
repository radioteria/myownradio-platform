package gemini.myownradio.LHttp.ContextObjects;

/**
 * Created by Roman on 29.10.14.
 */
public class LHttpContextPrefix extends LHttpContextAbstract {

    private final int PRIORITY_INDEX = 2_000_000;

    public LHttpContextPrefix(String context) {
        super(context);
    }

    @Override
    public boolean is(String path) {
        return path.startsWith(context);
    }

    @Override
    public int compare() {
        return PRIORITY_INDEX + context.length();
    }
}
