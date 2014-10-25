package gemini.myownradio.light;

/**
 * Created by Roman on 15.10.14.
 */
public class LHttpContextString implements LHttpContextInterface {
    final private String context;

    public LHttpContextString(String context) {
        this.context = context;
    }

    public String getContext() {
        return context;
    }

    @Override
    public String toString() {
        return this.context;
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;

        LHttpContextString that = (LHttpContextString) o;

        if (context != null ? !context.equals(that.context) : that.context != null) return false;

        return true;
    }

    @Override
    public int hashCode() {
        return context != null ? context.hashCode() : 0;
    }

    @Override
    public boolean is(String path) {
        return context.equals(path);
    }
}
