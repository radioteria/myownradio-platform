package biz.myownradio.tools;

import java.io.*;
import java.util.*;

/**
 * Created by Roman on 08.10.14
 */
public class MORSettings
{
    final private static Properties properties = new Properties();

    static {
        String confFile = System.getenv("MOR_CONFIG_FILE");

        if (confFile == null) {
            throw new RuntimeException("MOR_CONFIG_FILE environment variable must point to valid .properties file");
        }

        try {
            properties.load(new FileInputStream(confFile));
        } catch (IOException e) {
            throw new RuntimeException(confFile + " could not be read");
        }
    }

    public static String getString(String key)
    {
        String value = properties.getProperty(key);
        if (value == null) {
            throw new RuntimeException("Setting {" + key + "} not found in .properties file");
        }
        return value;
    }

    public static int getInteger(String key)
    {
        String value = getString(key);
        return Integer.parseInt(value);
    }
}
