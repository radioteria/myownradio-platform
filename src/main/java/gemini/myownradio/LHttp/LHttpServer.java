package gemini.myownradio.LHttp;

import gemini.myownradio.LHttp.ContextObjects.LHttpContextAbstract;
import gemini.myownradio.tools.DelayedAction;
import gemini.myownradio.tools.MORLogger;

import javax.net.ssl.SSLServerSocketFactory;
import java.io.*;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.TreeMap;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpServer {

    public final int MIN_PORT       = 1024;
    public final int MAX_PORT       = 65535;

    private int port                = 1024;
    private int workersCore         = 1024;
    private int workersMax          = 2096;
    private int blockingQueue       = 1024;
    private int maximalEntitySize   = 8192;

    private final long READ_REQUEST_TIMEOUT = 2_000L;

    private ServerSocket serverSocket;

    private Map<LHttpContextAbstract, LHttpContext>
            handlerMap = new TreeMap<>();

    private static MORLogger logger = new MORLogger(MORLogger.MessageKind.SERVER);

    public LHttpServer() {
    }

    public void setPort(int port) {
        if (port < MIN_PORT || port > MAX_PORT) {
            throw new IllegalArgumentException(
                    String.format("Port must be in range %d..%d but %d given", MIN_PORT, MAX_PORT, port));
        }
        this.port = port;
    }

    public void start() throws IOException {

        logger.println("Initializing thread pool");

//        ExecutorService threadPool = new ThreadPoolExecutor(workersCore, workersMax, 10L, TimeUnit.SECONDS,
//                new ArrayBlockingQueue<>(blockingQueue));

        ExecutorService threadPool = Executors.newCachedThreadPool();

        logger.println("Initializing server socket");
        serverSocket = new ServerSocket(port, blockingQueue);

        logger.println("Server started");

        while (true) {

            final Socket socket = serverSocket.accept();

            threadPool.submit(() -> {

                try (
                        InputStream inputStream = socket.getInputStream();
                        OutputStream outputStream = socket.getOutputStream()
                ) {
                    try {
                        logger.println("New connection attempt. Reading request...");
                        LHttpRequest request = readRequest(inputStream, socket);
                        logger.sprintf("Client IP=%s, ROUTE=%s", socket.getInetAddress().getHostAddress(),
                                request.getRequestPath());
                        routeRequest(request, outputStream);
                    } catch (LHttpException e) {
                        logger.sprintf("Unable to route request. STATUS=%s", e.getStatus().getCode());
                        PrintWriter printWriter = new PrintWriter(outputStream, true);
                        LHttpStatus st = e.getStatus();
                        printWriter.printf("HTTP/1.1 %s\r\n", st.getResponse());
                        printWriter.println("Content-Type: text/html");
                        printWriter.println("");
                        printWriter.printf("<h1>%s</h1>", st.getResponse());
                    }
                } catch (IOException hotClientDisconnection) {
                    logger.sprintf("Client IP=%s hardly disconnected", socket.getInetAddress().getHostAddress());
                }

            });

        }
    }

    private LHttpRequest readRequest(InputStream inputStream, Socket socket) throws IOException, LHttpException {

        BufferedReader bufferedReader = new BufferedReader(new InputStreamReader(inputStream));
        List<String> requestComponents = new ArrayList<>();

        int count = 0;

        String line;

        DelayedAction delayedAction = new DelayedAction(() -> {
            try { socket.close(); }
            catch (IOException e) { /* NOP */ }
        }, READ_REQUEST_TIMEOUT);

        delayedAction.start();

        try {

            // Read request begin
            while ((line = bufferedReader.readLine()) != null) {

                if (count + line.length() > maximalEntitySize) {
                    throw LHttpException.newEntityTooLong();
                }

                requestComponents.add(line);
                count += line.length();

                if (line.isEmpty()) {
                    return new LHttpRequest(requestComponents, socket);
                }

            }

        } finally {
            delayedAction.cancel();
        }



        throw LHttpException.badRequest();

    }

    private void routeRequest(LHttpRequest req, OutputStream os) throws IOException {

        logger.sprintf("Routing request %s...", req.getRequestPath());

        handlerMap
                .keySet()
                .stream()
                .filter(handle -> handle.is(req.getRequestPath()))
                .map(handle -> handlerMap.get(handle).getHandler())
                .filter(action -> action != null)
                .findFirst()
                .orElseThrow(LHttpException::documentNotFound)
                .handle(new LHttpProtocol(req, os));

    }

    public LHttpContext when(LHttpContextAbstract context) {
        LHttpContext ctx = new LHttpContext(context);
        handlerMap.put(context, ctx);
        return ctx;
    }

    public void setWorkersCore(int workersCore) {
        this.workersCore = workersCore;
    }

    public void setWorkersMax(int workersMax) {
        this.workersMax = workersMax;
    }

    public void setBlockingQueue(int blockingQueue) {
        this.blockingQueue = blockingQueue;
    }

}
