package biz.streamserver.core;

import biz.streamserver.entities.Stream;
import biz.streamserver.services.StreamService;
import com.sun.net.httpserver.HttpServer;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;

import javax.annotation.PostConstruct;
import javax.annotation.Resource;
import java.io.IOException;
import java.io.PrintWriter;
import java.net.InetSocketAddress;
import java.util.Optional;

@Service
public class Application
{
    @Value("${server.port}")
    int port;

    @Resource
    StreamService streamService;

    private static final Logger logger = LogManager.getLogger(Application.class);

    private HttpServer httpServer;

    @PostConstruct
    private void setup() throws IOException
    {
        logger.debug("Initializing HTTP server");
        httpServer = HttpServer.create();

        logger.debug("Starting listening on port {}", port);
        httpServer.bind(new InetSocketAddress(port), 0);

        logger.debug("Registering HTTP route handlers");
        registerRoutes();
    }

    private void registerRoutes()
    {
        httpServer.createContext("/", httpExchange -> {
            httpExchange.sendResponseHeaders(200, 0);
            PrintWriter printWriter = new PrintWriter(httpExchange.getResponseBody());
            printWriter.println("stream server is up");
            printWriter.close();
        });

        httpServer.createContext("/test", httpExchange -> {
            Optional<Stream> stream = streamService.findById(89L);
            httpExchange.sendResponseHeaders(200, 0);
            PrintWriter printWriter = new PrintWriter(httpExchange.getResponseBody());
            printWriter.println(stream);
            printWriter.close();
        });
    }

    public void start()
    {
        httpServer.start();
    }
}
