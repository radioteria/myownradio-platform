package gemini.myownradio.light;

import gemini.myownradio.light.Exceptions.LHttpException;
import gemini.myownradio.light.Exceptions.LHttpExceptionBadRequest;
import gemini.myownradio.light.Exceptions.LHttpExceptionEntityTooLong;
import gemini.myownradio.light.Exceptions.LHttpExceptionNotFound;
import gemini.myownradio.light.context.LHttpContextAbstract;
import gemini.myownradio.tools.BaseLogger;

import java.io.*;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.*;
import java.util.concurrent.ArrayBlockingQueue;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.ThreadPoolExecutor;
import java.util.concurrent.TimeUnit;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpServer {

    private final int MIN_PORT = 80;
    private final int MAX_PORT = 65535;

    private int port = 80;
    private int workersCore = 4;
    private int workersMax = 1024;

    private int blockingQueue = 256;
    private int maximalEntitySize = 8192;

    private ServerSocket serverSocket;

    private Map<LHttpContextAbstract, LHttpContext> handlerMap;

    public LHttpServer() {
        handlerMap = new TreeMap<>(new Comparator<LHttpContextAbstract>() {
            @Override
            public int compare(LHttpContextAbstract o1, LHttpContextAbstract o2) {
                return o2.compare() - o1.compare();
            }
        });
    }

    public int getPort() {
        return port;
    }

    public void setPort(int port) {
        if (port < MIN_PORT || port > MAX_PORT) {
            throw new IllegalArgumentException(
                    String.format("Port must be in range %d..%d but %d given", MIN_PORT, MAX_PORT, port));
        }
        this.port = port;
    }

    public Thread start() throws IOException {
        ExecutorService threadPool = new ThreadPoolExecutor(workersCore, workersMax, 60L, TimeUnit.SECONDS,
                new ArrayBlockingQueue<Runnable>(blockingQueue));

        serverSocket = new ServerSocket(port, blockingQueue);

        Thread t = new Thread(() -> {
            BaseLogger.writeLog("Server started listening on port " + this.port);
            try {
                while (true) {
                    final Socket socket = serverSocket.accept();
                    threadPool.submit(() -> {
                        try (
                                InputStream is = socket.getInputStream();
                                OutputStream os = socket.getOutputStream()
                        ) {
                            try {
                                LHttpRequest req = readRequest(is, socket);
                                if (!routeRequest(req, os)) {
                                    throw new LHttpExceptionNotFound();
                                }
                            } catch (LHttpException e) {
                                PrintWriter pw = new PrintWriter(os, true);
                                LHttpStatus st = e.getStatus();
                                pw.printf("HTTP/1.1 %s\r\n", st.getResponse());
                                pw.println("Content-Type: text/html");
                                pw.println("");
                                pw.printf("<h1>%s</h1>", st.getResponse());
                            }
                        } catch (IOException e) {
                            e.printStackTrace();
                        }
                    });

                }
            } catch (IOException e) {
                e.printStackTrace();
            }
        });

        t.start();

        return t;

    }

    private boolean routeRequest(LHttpRequest req, OutputStream os) throws IOException {

        LHttpHandler handler = handlerMap
                .keySet()
                .stream()
                .filter(k -> k.is(req.getRequestPath()))
                .map(k -> handlerMap.get(k).getHandler())
                .filter(v -> v != null)
                .findFirst()
                .orElse(null);


        if (handler != null) {
            handler.handler(new LHttpProtocol(req, os));
            return true;
        }

        return false;

    }

    private LHttpRequest readRequest(InputStream is, Socket socket) throws IOException, LHttpException {

        BufferedReader br = new BufferedReader(new InputStreamReader(is));
        List<String> requestHeaders = new ArrayList<>();
        int count = 0;

        String line;
        while ((line = br.readLine()) != null) {
            if (count + line.length() > maximalEntitySize) {
                throw new LHttpExceptionEntityTooLong();
            }
            requestHeaders.add(line);
            count += line.length();
            if (line.equals("")) {
                return new LHttpRequest(requestHeaders, socket);
            }
        }
        throw new LHttpExceptionBadRequest();
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
