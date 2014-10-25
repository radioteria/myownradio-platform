package gemini.myownradio.light;

import gemini.myownradio.tools.MORConfig;

import java.io.IOException;
import java.io.OutputStream;
import java.io.PrintWriter;
import java.util.ArrayList;
import java.util.List;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpProtocol {
    private List<String> headers;
    private LHttpStatus status = null;
    private String contentType = null;
    private Integer ctSize = null;
    private boolean headersFlushed = false;

    final private LHttpStatus defaultStatus = LHttpStatus.STATUS_200;
    final private String defaultContentType = "text/plain";

    private OutputStream os;
    private LHttpRequest request;
    private PrintWriter pw;

    public LHttpProtocol(LHttpRequest req, OutputStream os) {
        this.headers = new ArrayList<>();
        this.os = os;
        this.pw = new PrintWriter(os, true);
        this.request = req;
    }

    public void setHeader(String header, String value) {
        headers.add(String.format("%s: %s", header, value));
    }

    public void setStatus(LHttpStatus status) {
        this.status = status;
    }

    public void setContentSize(int size) {
        this.ctSize = size;
    }

    public void setContentType(String contentType) {
        this.contentType = contentType;
    }

    public PrintWriter getPrinter() {
        flushHeaders();
        return pw;
    }

    public OutputStream getOutputStream() {
        flushHeaders();
        return os;
    }

    public String getHeader(String key) {
        return request.getHeader(key);
    }

    public boolean headerEquals(String key, String value) {

        String hdr = this.getHeader(key);

        if (value == null && hdr == null) {
            return true;
        }

        if (value == null || hdr == null) {
            return false;
        }

        return hdr.equals(value);

    }

    public String get(String key, String defaultValue) {
        String val = request.get(key);
        return val != null ? val : defaultValue;
    }

    public String get(String key) {
        return request.get(key);
    }

    public String getClientIP() {
        return request.getClientIP();
    }

    private void flushHeaders() {
        if (!this.headersFlushed) {
            pw.printf("%s %s\r\n", request.getProtoVersion(), status != null ? status.getResponse() : defaultStatus.getResponse());
            pw.println("Connection: close");
            pw.printf("Server: %s\r\n", MORConfig.whoIsMe);
            if (ctSize != null) {
                pw.println(String.format("Content-Size: %d", ctSize));
            }
            pw.println(String.format("Content-Type: %s", contentType != null ? contentType : defaultContentType));
            for (String h : headers) {
                pw.println(h);
            }
            pw.println("");
            this.headersFlushed = true;
        }
    }

    public void flush() throws IOException {
        flushHeaders();
        pw.flush();
        os.flush();
    }
}
