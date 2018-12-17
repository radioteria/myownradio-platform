package biz.myownradio.tools;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Created by Roman on 08.10.14.
 */
public class RegExpTools {

    public static boolean StringMatches(String pattern, String target) {
        Pattern p = Pattern.compile(pattern, Pattern.CASE_INSENSITIVE);
        Matcher m = p.matcher(target);

        if (m.find())
            return true;
        else
            return false;
    }

    public static Matcher getMatcher(String pattern, String target) {
        Pattern p = Pattern.compile(pattern, Pattern.CASE_INSENSITIVE);
        Matcher r = p.matcher(target);
        if (r.find())
            return r;
        else
            return null;
    }

}
