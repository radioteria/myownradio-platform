package gemini.myownradio.light;

import gemini.myownradio.light.context.LHttpContextAbstract;
import gemini.myownradio.tools.BaseLogger;

import java.io.*;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.TreeMap;
import java.util.concurrent.ArrayBlockingQueue;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.ThreadPoolExecutor;
import java.util.concurrent.TimeUnit;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpServer {

    public final int MIN_PORT       = 1024;
    public final int MAX_PORT       = 65535;

    private int port                = 1024;
    private int workersCore         = 4;
    private int workersMax          = 1024;
    private int blockingQueue       = 256;
    private int maximalEntitySize   = 8192;

    private ServerSocket serverSocket;

    private Map<LHttpContextAbstract, LHttpContext>
            handlerMap = new TreeMap<>((o1, o2) -> o2.compare() - o1.compare());

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

        ExecutorService threadPool = new ThreadPoolExecutor(workersCore, workersMax, 60L, TimeUnit.SECONDS,
                new ArrayBlockingQueue<>(blockingQueue));

        serverSocket = new ServerSocket(port, blockingQueue);

        BaseLogger.writeLog("Server started listening on port " + this.port);

        while (true) {

            final Socket socket = serverSocket.accept();

            threadPool.submit(() -> {

                try (
                        InputStream inputStream = socket.getInputStream();
                        OutputStream outputStream = socket.getOutputStream()
                ) {
                    try {
                        LHttpRequest request = readRequest(inputStream, socket);
                        routeRequest(request, outputStream);
                    } catch (LHttpException e) {
                        PrintWriter printWriter = new PrintWriter(outputStream, true);
                        LHttpStatus st = e.getStatus();
                        printWriter.printf("HTTP/1.1 %s\r\n", st.getResponse());
                        printWriter.println("Content-Type: text/html");
                        printWriter.println("");
                        printWriter.printf("<h1>%s</h1>", st.getResponse());
                    }
                } catch (IOException hotClientDisconnection) { /* NOP */ }

            });

        }
    }

    private LHttpRequest readRequest(InputStream inputStream, Socket socket) throws IOException, LHttpException {

        BufferedReader bufferedReader = new BufferedReader(new InputStreamReader(inputStream));
        List<String> requestComponents = new ArrayList<>();
        int count = 0;

        String line;
        while ((line = bufferedReader.readLine()) != null) {
            if (count + line.length() > maximalEntitySize) {
                throw LHttpException.newEntityToLong();
            }
            requestComponents.add(line);
            count += line.length();
            if (line.isEmpty()) {
                return new LHttpRequest(requestComponents, socket);
            }
        }

        throw LHttpException.newBadRequest();

    }

    private void routeRequest(LHttpRequest req, OutputStream os) throws IOException {

        handlerMap
                .keySet()
                .stream()
                .filter(handle -> handle.is(req.getRequestPath()))
                .map(handle -> handlerMap.get(handle).getHandler())
                .filter(action -> action != null)
                .findFirst().orElseThrow(() -> LHttpException.newDocumentNotFound())
                .handler(new LHttpProtocol(req, os));

    }

    public LHttpContext createContext(LHttpContextAbstract context) {
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
