package biz.myownradio.tools;

import java.io.*;
import java.util.*;
import java.util.function.Supplier;

/**
 * Created by Roman on 08.10.14
 */
public class MORSettings {

    private static class SettingsException extends RuntimeException {
        SettingsException(String message) {
            super(message);
        }
    }

    private static class OptionalCombiner {
        @SafeVarargs
        static <T> Optional<T> combine(Supplier<Optional<T>>... suppliers) {
            for (Supplier<Optional<T>> supplier : suppliers) {
                Optional<T> value = supplier.get();
                if (value.isPresent()) {
                    return value;
                }
            }
            return Optional.empty();
        }
    }

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

        return OptionalCombiner.combine(
                () -> Optional.ofNullable(value),
                () -> getEnv(key)
        );
    }

    public static Optional<Integer> getInteger(String key) {
        return getString(key).map(Integer::parseInt);
    }

    public static Optional<Boolean> getBoolean(String key) {
        return getString(key).map(Boolean::valueOf);
    }

    public static String getStringNow(String key) {
        return getOrFail(getString(key), key);
    }

    public static Integer getIntegerNow(String key) {
        return getOrFail(getInteger(key), key);
    }

    public static Boolean getBooleanNow(String key) {
        return getOrFail(getBoolean(key), key);
    }

    private static <T> T getOrFail(Optional<T> optional, String key) {
        return optional.orElseThrow(() -> new SettingsException("Setting '" + key + "' does not exist"));
    }

    private static Optional<String> getEnv(String key) {
        return Optional.ofNullable(System.getenv(keyToEnv(key)));
    }

    private static String keyToEnv(String key) {
        return "MOR_" + key.toUpperCase().replace(".", "_");
    }
}
