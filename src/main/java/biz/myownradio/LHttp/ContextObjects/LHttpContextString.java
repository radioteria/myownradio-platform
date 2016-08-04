package biz.myownradio.LHttp.ContextObjects;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpContextString extends LHttpContextAbstract {

    private final int PRIORITY_INDEX = 4_000_000;

    public LHttpContextString(String context) {
        super(context);
    }

    @Override
    public boolean is(String path) {
        return path.equals(context);
    }

}
