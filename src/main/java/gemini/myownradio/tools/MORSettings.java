package gemini.myownradio.tools;

import java.io.*;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.regex.Matcher;

import static java.net.URLDecoder.decode;

/**
 * Created by Roman on 08.10.14.
 */
public class MORSettings {

    private final static File iniFile;
    private final static String defaultSection = "main";
    private static HashMap<String, ArrayList<String>> settings = new HashMap<>();

    static {
        iniFile = new File("/usr/local/myownradio/conf/mor.conf");
        readFile();
    }

    private static void readFile() {
        try (
                FileReader file = new FileReader(iniFile);
                BufferedReader buffered = new BufferedReader(file);
        ) {
            String line;
            String tline;
            Matcher m;
            String section = defaultSection;
            while ((line = buffered.readLine()) != null) {
                tline = line.trim();
                if (tline.startsWith("[") && tline.endsWith("]")) {
                    section = tline.substring(1, tline.length() - 1);
                } else if ((m = RegExpTools.getMatcher("(.+?)(\\[\\])* *= *\"(.+)\" *(;*)*", tline)) != null) {
                    // Quoted setting
                    putSetting(section, m.group(1).trim(), m.group(3).trim());
                } else if ((m = RegExpTools.getMatcher("(.+?)(\\[\\])* *= *(.*) *(;*)*", tline)) != null) {
                    // Unquoted setting
                    putSetting(section, m.group(1).trim(), m.group(3).trim());
                }
            }
        } catch (IOException e) {
            /* Do nothing if settings setting file could not be read */
        }
    }

    private static void putSetting(String section, String setting, String value) {
        ArrayList<String> tmp = settings.get(String.format("%s/%s", section, setting));
        if (tmp == null) {
            tmp = new ArrayList<>();
            tmp.add(value);
            settings.put(String.format("%s/%s", section, setting), tmp);
        } else {
            tmp.add(value);
        }
    }

    /**
     * @param section     Setting section in .ini file
     * @param setting     Setting in .ini file
     * @param alternative Alternative value if setting not set
     * @return Returns setting value or alternative (string)
     */
    public static String getFirstString(String section, String setting, String alternative) {

        if (settings.get(String.format("%s/%s", section, setting)) == null)
            return alternative;

        if (settings.get(String.format("%s/%s", section, setting)).get(0) == null)
            return alternative;

        return settings.get(String.format("%s/%s", section, setting)).get(0);

    }

    public static List<String> getValues(String section, String setting) {
        if (settings.get(String.format("%s/%s", section, setting)) == null) {
            return new ArrayList<>();
        } else {
            return settings.get(String.format("%s/%s", section, setting));
        }
    }

    /**
     * @param section     Setting section in .ini file
     * @param setting     Setting in .ini file
     * @param alternative Alternative value if setting not set
     * @return Returns setting value or alternative (string)
     */
    public static Integer getFirstInteger(String section, String setting, Integer alternative) {

        if (settings.get(String.format("%s/%s", section, setting)) == null)
            return alternative;

        if (settings.get(String.format("%s/%s", section, setting)).get(0) == null)
            return alternative;

        try {
            return Integer.parseInt(settings.get(String.format("%s/%s", section, setting)).get(0));
        } catch (NumberFormatException e) {
            return alternative;
        }

    }

    public static String urldecode(String url) {
        String decoded = null;
        try {
            decoded = decode(url, "UTF-8");
        } catch (UnsupportedEncodingException neverOccurs) {/*NOP*/}
        return decoded;
    }

}
