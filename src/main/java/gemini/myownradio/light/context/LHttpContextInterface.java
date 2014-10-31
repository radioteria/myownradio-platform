package gemini.myownradio.light.context;

/**
 * Created by Roman on 30.10.14.
 */
public interface LHttpContextInterface {

    public boolean is(String path);

    public int compare();

    public String getContext();
}
