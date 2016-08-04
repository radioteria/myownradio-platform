package biz.myownradio.LHttp;

/**
 * Created by Roman on 15.10.14.
 */
public enum LHttpStatus {
    STATUS_200(200, "OK"),
    STATUS_400(400, "Bad Request"),
    STATUS_403(403, "Forbidden"),
    STATUS_404(404, "Not Found"),
    STATUS_413(413, "Request Entity Too Large"),
    STATUS_501(501, "Not Implemented");

    final private int code;
    final private String message;

    LHttpStatus(int code, String message) {
        this.code = code;
        this.message = message;
    }

    public int getCode() {
        return code;
    }

    public String getMessage() {
        return message;
    }

    public String getResponse() {
        return String.format("%d %s", this.code, this.message);
    }
}
