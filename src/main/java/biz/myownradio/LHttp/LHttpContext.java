package biz.myownradio.LHttp;

import biz.myownradio.LHttp.ContextObjects.LHttpContextInterface;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpContext {

    private final LHttpContextInterface context;
    private LHttpHandler handler = null;

    public LHttpContext(LHttpContextInterface context) {
        this.context = context;
    }

    public void exec(LHttpHandler handler) {
        this.handler = handler;
    }

    public LHttpHandler getHandler() {
        return handler;
    }

}
