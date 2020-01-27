package biz.myownradio.tools;

/**
 * Created by Roman on 15.10.14.
 */
public class CaseString {
    private String val;

    public CaseString(String val) {
        this.val = val;
    }

    @Override
    public String toString() {
        return this.val;
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) return true;
        if (o == null || getClass() != o.getClass()) return false;

        CaseString that = (CaseString) o;

        if (val != null ? !val.toLowerCase().equals(that.val.toLowerCase()) : that.val != null) return false;

        return true;
    }

    @Override
    public int hashCode() {
        return val != null ? val.toLowerCase().hashCode() : 0;
    }
}
