package biz.morStreamer.router;

import java.util.HashMap;
import java.util.Map;
import java.util.Optional;
import java.util.regex.Pattern;

/**
 * Created by Roman on 20.08.16
 */
public class Router<T>
{
    final private HashMap<String, HashMap<Pattern, T>> routes = new HashMap<>();

    public void add(String method, String route, T t)
    {
        String upperMethod = method.toUpperCase();
        String preparedRoute = route.replaceAll("/:([^\\\\/]+)/", "(?P<$1>[\\w-]+)");

        routes.computeIfAbsent(upperMethod, (k) -> new HashMap<>())
              .put(Pattern.compile(route), t);
    }

    public Optional<T> find(String method, String path)
    {
        String upperMethod = method.toUpperCase();

        HashMap<Pattern, T> routeMap = routes.computeIfAbsent(upperMethod, (k) -> new HashMap<>());

        if (!routes.containsKey(upperMethod)) {
            return Optional.empty();
        }

        for (Map.Entry<Pattern, T> e : routeMap.entrySet()) {
            if (e.getKey().matcher(path).matches()) {
                return Optional.of(e.getValue());
            }
        }

        return Optional.empty();
    }
}
