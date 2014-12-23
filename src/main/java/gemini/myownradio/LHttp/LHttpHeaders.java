package gemini.myownradio.LHttp;

import gemini.myownradio.tools.CaseString;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpHeaders {

    private Map<CaseString, List<String>> headers = new HashMap<>();

    public void add(CaseString header, String value) {
        List<String> tmp = headers.get(header);
        if (tmp == null) {
            List<String> tmp2 = new ArrayList<>();
            tmp2.add(value);
            headers.put(header, tmp2);
        } else {
            tmp.add(value);
        }
    }

    public void remove(CaseString header) {
        List<String> tmp = headers.get(header);
        if (tmp == null || tmp.size() == 0) {
            headers.remove(header);
        } else {
            tmp.remove(tmp.size() - 1);
        }
    }

    public void removeAll(CaseString header) {
        headers.remove(header);
    }

    public String getFirst(CaseString header) {
        List<String> tmp = headers.get(header);
        if (tmp == null || tmp.size() == 0) {
            return null;
        } else {
            return tmp.get(0);
        }
    }

    public List<String> getAll(CaseString header) {
        return headers.get(header);
    }

    public String get(CaseString header, int index) {
        List<String> tmp = headers.get(header);
        if (tmp == null || tmp.size() == 0 || tmp.size() - index < 1) {
            return null;
        } else {
            return tmp.get(index);
        }
    }

    public int getCount(CaseString header) {
        List<String> tmp = headers.get(header);
        if (tmp == null) {
            return 0;
        } else {
            return tmp.size();
        }
    }

}
