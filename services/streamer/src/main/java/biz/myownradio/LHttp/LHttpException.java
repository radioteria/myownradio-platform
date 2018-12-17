package biz.myownradio.LHttp;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpException extends RuntimeException {
    private LHttpStatus status;

    public LHttpException(LHttpStatus status) {
        this.status = status;
    }

    public LHttpException(LHttpStatus status, String message) {
        super(message);
    }

    public LHttpStatus getStatus() {
        return status;
    }

    public static LHttpException badRequest() {
        return new LHttpException(LHttpStatus.STATUS_400);
    }

    public static LHttpException newEntityTooLong() {
        return new LHttpException(LHttpStatus.STATUS_413);
    }

    public static LHttpException newMethodNotImplemented() {
        return new LHttpException(LHttpStatus.STATUS_501);
    }

    public static LHttpException documentNotFound() {
        return new LHttpException(LHttpStatus.STATUS_404);
    }

    public static LHttpException forbidden() {
        return new LHttpException(LHttpStatus.STATUS_403);
    }

    public static LHttpException serverError() {
        return new LHttpException(LHttpStatus.STATUS_500);
    }

    public static LHttpException serverError(String message) {
        return new LHttpException(LHttpStatus.STATUS_500, message);
    }

}
