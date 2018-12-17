package biz.myownradio.LHttp.ContextObjects;

/**
 * Created by Roman on 29.10.14.
 */
public class LHttpContextPostfix extends LHttpContextAbstract {

    private final int PRIORITY_INDEX = 3_000_000;

    public LHttpContextPostfix(String context) {
        super(context);
    }

    @Override
    public boolean is(String path) {
        return path.endsWith(context);
    }

}
