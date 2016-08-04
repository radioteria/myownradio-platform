package biz.myownradio.tools;

import java.io.*;
import java.util.*;

/**
 * Created by Roman on 08.10.14
 */
public class MORSettings {

    private static Properties properties = new Properties();

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

    public static Optional<String> getString(String key) {
        String value = properties.getProperty(key);
        return value == null ? Optional.empty() : Optional.of(value);
    }

    public static Optional<Integer> getInteger(String key) {
        return getString(key).map(Integer::parseInt);
    }
}
