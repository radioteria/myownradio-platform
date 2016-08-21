package biz.morStreamer.router;

import junit.framework.TestCase;

import java.util.HashMap;
import java.util.Map;
import java.util.Optional;

/**
 * Created by Roman on 21.08.16
 */
public class RouteTest extends TestCase
{
    final private static String data = "Some Data";

    private void routeTestWithoutParameters(String route, String[] notMatchingPaths)
    {
        Route<String> r = new Route<>(route, data);

        assertEquals(route, r.getRoutePattern().pattern());
        assertSame(data, r.getTarget());

        Optional<Route.RouteMatch<String>> optionalRouteMatch = r.getRouteMatch(route);
        assertTrue(optionalRouteMatch.isPresent());

        Route.RouteMatch<String> routeMatch = optionalRouteMatch.get();

        assertSame(data, routeMatch.getT());
        assertEquals(0, routeMatch.getParameters().size());

        for (String path : notMatchingPaths) {
            assertFalse(r.getRouteMatch(path).isPresent());
        }
    }

    private void routeTestWithParameters(
            String route,
            Map<String, Map<String, String>> matchingPaths,
            String[] notMatchingPaths
    )
    {
        Route<String> r = new Route<>(route, data);

        for (String path : matchingPaths.keySet()) {
            Map<String, String> parameters = matchingPaths.get(path);
            Optional<Route.RouteMatch<String>> routeMatchOptional = r.getRouteMatch(path);
            assertTrue(routeMatchOptional.isPresent());
            Route.RouteMatch<String> routeMatch = routeMatchOptional.get();
            assertEquals(parameters, routeMatch.getParameters());
        }

        for (String path : notMatchingPaths) {
            assertFalse(r.getRouteMatch(path).isPresent());
        }
    }

    public void testRoutesWithoutParameters()
    {
        routeTestWithoutParameters("/", new String[] { "foo/bar", "baz/bar" });
        routeTestWithoutParameters("foo/bar", new String[] { "/", "baz/bar" });
        routeTestWithoutParameters("foo/bar/baz", new String[] { "/", "baz/bar", "foo/baz" });
    }

    public void testRoutesWithParameters()
    {
        routeTestWithParameters(
                "articles/:id",
                new HashMap<String, Map<String, String>>() {{
                    this.put("articles/some-article", new HashMap<String, String>() {{
                        this.put("id", "some-article");
                    }});
                    this.put("articles/other-article", new HashMap<String, String>() {{
                        this.put("id", "other-article");
                    }});
                }},
                new String[] { "/", "baz/bar", "foo/baz", "articles/something/deeper" }
        );

        routeTestWithParameters(
                "articles/&id",
                new HashMap<String, Map<String, String>>() {{
                    this.put("articles/11", new HashMap<String, String>() {{
                        this.put("id", "11");
                    }});
                    this.put("articles/24", new HashMap<String, String>() {{
                        this.put("id", "24");
                    }});
                }},
                new String[] { "/", "baz/bar", "foo/baz", "articles/some-article", "articles/22/deeper" }
        );
    }
}
