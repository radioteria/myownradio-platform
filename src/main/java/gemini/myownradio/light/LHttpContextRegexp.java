package gemini.myownradio.light;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Created by Roman on 16.10.14.
 */
public class LHttpContextRegexp implements LHttpContextInterface {
    final private String context;
    final Pattern pat;

    public LHttpContextRegexp(String context) {
        this.context = context;
        this.pat = Pattern.compile(context);
    }

    public String getContext() {
        return context;
    }

    @Override
    public boolean is(String path) {
        Matcher m = pat.matcher(path);

        if(m.find()) {
            return true;
        } else {
            return false;
        }
    }
}
