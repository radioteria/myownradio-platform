package biz.streamserver.server;

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
        logger.debug("Initializing server");
        httpServer = HttpServer.create();

        logger.debug("Registering server shutdown hooks");
        registerServerShutdownHooks();

        logger.debug("Registering route handlers");
        registerRoutes();

        logger.debug("Binding server to listen on port {}", port);
        httpServer.bind(new InetSocketAddress(port), 0);
    }

    private void registerServerShutdownHooks()
    {
        Runtime.getRuntime().addShutdownHook(new Thread(() -> httpServer.stop(0)));
    }

    private void registerRoutes()
    {
        httpServer.createContext("/", httpExchange -> {
            httpExchange.sendResponseHeaders(200, 0);
            PrintWriter printWriter = new PrintWriter(httpExchange.getResponseBody());
            printWriter.println("stream server is up");
            printWriter.close();
        });
    }

    public void start()
    {
        logger.debug("Starting server");
        httpServer.start();
    }
}
