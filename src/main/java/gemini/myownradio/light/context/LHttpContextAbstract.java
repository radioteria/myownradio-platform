package gemini.myownradio.light.context;

/**
 * Created by Roman on 15.10.14.
 */
public abstract class LHttpContextAbstract implements LHttpContextInterface {

    final protected String context;

    public LHttpContextAbstract(String context) {
        this.context = context;
    }

    public String getContext() {
        return context;
    }

    public String toString() {
        return context;
    }

    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;

        LHttpContextAbstract that = (LHttpContextAbstract) o;

        if (context != null ? !context.equals(that.context) : that.context != null) return false;

        return true;
    }

    public int hashCode() {
        return context != null ? context.hashCode() : 0;
    }

}
