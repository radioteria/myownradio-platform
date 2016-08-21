package biz.morStreamer.router;

import java.util.*;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Created by Roman on 21.08.16
 */
public class Route<T>
{
    public static class RouteMatch<T>
    {
        private T t;
        private Map<String, String> parameters;

        public RouteMatch(T t, Map<String, String> parameters) {
            this.t = t;
            this.parameters = parameters;
        }

        public T getT() {
            return t;
        }

        public Map<String, String> getParameters() {
            return parameters;
        }
    }

    private Pattern routePattern;

    private List<String> parameterNames;

    private T t;

    public Route(String path, T t)
    {
        readParameterNames(path);

        readRoutePattern(path);

        this.t = t;
    }

    private void readParameterNames(String path)
    {
        List<String> parameterNames = new ArrayList<>();

        Matcher parameterMatcher = Pattern
                .compile("(?::|&)([a-zA-Z]+)")
                .matcher(path);

        while (parameterMatcher.find()) {
            parameterNames.add(parameterMatcher.group(1));
        }

        this.parameterNames = Collections.unmodifiableList(parameterNames);
    }

    private void readRoutePattern(String path)
    {
        String preparedPath = path
                .replaceAll(":([a-zA-Z]+)", "([\\\\w-]+)")
                .replaceAll("&([a-zA-Z]+)", "([\\\\d]+)");

        this.routePattern = Pattern.compile(preparedPath);
    }

    public Optional<RouteMatch<T>> getRouteMatch(String path)
    {
        Matcher matcher = routePattern.matcher(path);

        if (! matcher.matches()) {
            return Optional.empty();
        }

        Map<String, String> parameters = new HashMap<>();
        for (int i = 0; i < matcher.groupCount(); i ++) {
            parameters.put(parameterNames.get(i), matcher.group(i + 1));
        }
        Map<String, String> frozenParameters = Collections.unmodifiableMap(parameters);

        RouteMatch<T> routeMatch = new RouteMatch<>(t, frozenParameters);

        return Optional.of(routeMatch);
    }

    public Pattern getRoutePattern()
    {
        return routePattern;
    }

    public List<String> getParameterNames()
    {
        return parameterNames;
    }

    public T getTarget()
    {
        return t;
    }
}
