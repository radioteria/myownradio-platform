package gemini.myownradio.tools;

import java.io.*;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.regex.Matcher;

/**
 * Created by Roman on 08.10.14.
 */
public class IniReader {

    private final File iniFile;
    private final String defaultSection = "main";

    private HashMap<String,ArrayList<String>> settings;

    public IniReader(File iniFile) {
        this.iniFile = iniFile;

        settings = new HashMap<>();

        this.readFile();
    }

    private void readFile() {
        System.out.println("Reading ini file...");
        try(
            FileReader file = new FileReader(this.iniFile);
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
                    this.putSetting(section, m.group(1).trim(), m.group(3).trim());
                } else if ((m = RegExpTools.getMatcher("(.+?)(\\[\\])* *= *(.*) *(;*)*", tline)) != null) {
                    // Unquoted setting
                    this.putSetting(section, m.group(1).trim(), m.group(3).trim());
                }
            }
        } catch (IOException e) {
            /* Do nothing if settings file could not be found */
        }
    }

    private void putSetting(String section, String setting, String value) {
        ArrayList<String> tmp = settings.get(String.format("%s/%s", section, setting));
        if (tmp == null) {
            tmp = new ArrayList<>();
            tmp.add(value);
            settings.put(String.format("%s/%s", section, setting), tmp);
        } else {
            tmp.add(value);
        }
    }

    public String getFirst(String section, String setting) {
        return this.getFirst(section, setting, null);
    }

    public String getFirst(String section, String setting, String alt) {

        if(settings.get(String.format("%s/%s", section, setting)) == null)
            return alt;

        if(settings.get(String.format("%s/%s", section, setting)).get(0) == null)
            return alt;

        return settings.get(String.format("%s/%s", section, setting)).get(0);

    }

}
