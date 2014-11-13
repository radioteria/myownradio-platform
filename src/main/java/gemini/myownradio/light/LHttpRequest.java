package gemini.myownradio.light;

import gemini.myownradio.tools.CaseString;

import java.io.IOException;
import java.net.Socket;
import java.net.URLDecoder;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpRequest {
    private LHttpHeaders headers = new LHttpHeaders();
    private Map<String, String> get = new HashMap<>();
    private String protoVersion;
    private String requestPath;
    private String remoteIP;

    public LHttpRequest(List<String> requestHeaders, Socket socket) throws IOException, LHttpException {

        int position = 0;

        // Reading initial line
        String line = requestHeaders.get(0);

        if (line == null || line.trim().length() == 0)
            throw LHttpException.BadRequest();

        String[] temp = line.split("\\s+"); // Split method

        if (temp.length != 3)
            throw LHttpException.BadRequest();

        /* Request method */
        String method = temp[0];

        if (!temp[2].contains("HTTP/"))
            throw LHttpException.BadRequest();

        this.protoVersion = temp[2];

        if (temp[1].contains("?")) {
            String[] subRequest = temp[1].split("\\?");
            this.requestPath = URLDecoder.decode(subRequest[0], "ISO-8859-1");

            String[] args = subRequest[1].split("&");

            String[] temp1;
            for (String arg : args) {
                // Reading GET arguments
                temp1 = arg.split("=");
                if (temp1.length == 2) {
                    get.put(
                            URLDecoder.decode(temp1[0], "ISO-8859-1"),
                            URLDecoder.decode(temp1[1], "ISO-8859-1")
                    );
                } else if (temp1.length == 1) {
                    get.put(
                            URLDecoder.decode(temp1[0], "ISO-8859-1"),
                            URLDecoder.decode(temp1[0], "ISO-8859-1")
                    );
                }

            }
        } else {
            this.requestPath = URLDecoder.decode(temp[1], "ISO-8859-1");
        }

        if (!method.equals("GET"))
            throw LHttpException.MethodNotImplemented();

        // Parse headers
        for (int i = 1; i < requestHeaders.size(); i++) {
            line = requestHeaders.get(i);

            // Looking for end of request
            if (line.trim().length() == 0)
                break;

            if (!line.contains(":"))
                throw LHttpException.BadRequest();

            temp = line.split(":", 2);
            this.headers.add(new CaseString(temp[0]), temp[1].trim());
        }

        this.remoteIP = socket.getInetAddress().getHostAddress();

    }

    public String getRequestPath() {
        return requestPath;
    }

    public String getHeader(String header) {
        return headers.getFirst(new CaseString(header));
    }

    public String get(String key) {
        return get.get(key);
    }

    public String getProtoVersion() {
        return protoVersion;
    }

    public String getClientIP() {
        return remoteIP;
    }
}
