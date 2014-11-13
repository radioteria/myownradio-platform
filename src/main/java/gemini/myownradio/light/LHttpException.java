package gemini.myownradio.light;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpException extends RuntimeException {
    private LHttpStatus status;

    public LHttpException(LHttpStatus status) {
        this.status = status;
    }

    public LHttpStatus getStatus() {
        return status;
    }

    public static LHttpException BadRequest() {
        return new LHttpException(LHttpStatus.STATUS_400);
    }

    public static LHttpException EntityToLong() {
        return new LHttpException(LHttpStatus.STATUS_413);
    }

    public static LHttpException MethodNotImplemented() {
        return new LHttpException(LHttpStatus.STATUS_501);
    }

    public static LHttpException DocumentNotFound() {
        return new LHttpException(LHttpStatus.STATUS_404);
    }
}
