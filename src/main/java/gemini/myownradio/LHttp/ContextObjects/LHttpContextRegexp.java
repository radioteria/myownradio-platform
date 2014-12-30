package gemini.myownradio.LHttp.ContextObjects;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Created by Roman on 16.10.14.
 */
public class LHttpContextRegexp extends LHttpContextAbstract {

    private final Pattern pat;
    private final int PRIORITY_INDEX = 1_000_000;

    public LHttpContextRegexp(String context) {
        super(context);
        pat = Pattern.compile(context);
    }

    @Override
    public boolean is(String path) {
        Matcher m = pat.matcher(path);
        return m.find();
    }

}
