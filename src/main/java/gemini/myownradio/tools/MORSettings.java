package gemini.myownradio.tools;

import org.ini4j.Ini;
import org.ini4j.IniPreferences;

import java.io.*;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Optional;
import java.util.regex.Matcher;

import static java.net.URLDecoder.decode;

/**
 * Created by Roman on 08.10.14
 */
public class MORSettings {

    private final static File iniFile;
    private final static String defaultSection = "main";
    private static HashMap<String, ArrayList<String>> settings = new HashMap<>();

    static {
        String confFile = System.getenv("MOR_CONFIG_FILE");
        if (confFile == null) {
            throw new RuntimeException("MOR_CONFIG_FILE environment variable not set.");
        }
        iniFile = new File(confFile);
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
                } else if ((m = RegExpTools.getMatcher("(.+?)(\\[\\])* *= *\"(.*)\" *(;*)*", tline)) != null) {
                    // Quoted setting
                    putSetting(section, m.group(1).trim(), m.group(3).trim());
                } else if ((m = RegExpTools.getMatcher("(.+?)(\\[\\])* *= *(.*) *(;*)*", tline)) != null) {
                    // Unquoted setting
                    putSetting(section, m.group(1).trim(), m.group(3).trim());
                }
            }
        } catch (IOException e) {
            /* Do nothing if settings file could not be read */
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
     * @return Returns setting value or alternative (string)
     */
    public static Optional<String> getFirstString(String section, String setting) {

        if (settings.get(String.format("%s/%s", section, setting)) == null)
            return Optional.empty();

        if (settings.get(String.format("%s/%s", section, setting)).get(0) == null)
            return Optional.empty();

        return Optional.of(settings.get(String.format("%s/%s", section, setting)).get(0));

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
     * @return Returns setting value or alternative (string)
     */
    public static Optional<Integer> getFirstInteger(String section, String setting) {

        if (settings.get(String.format("%s/%s", section, setting)) == null)
            return Optional.empty();

        if (settings.get(String.format("%s/%s", section, setting)).get(0) == null)
            return Optional.empty();

        try {

            return Optional.of(
                    Integer.parseInt(
                            settings.get(String.format("%s/%s", section, setting)).get(0)
                    )
            );

        } catch (NumberFormatException e) {

            return Optional.empty();

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
