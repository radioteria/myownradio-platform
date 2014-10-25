package gemini.myownradio.tools;

import gemini.myownradio.WebRadio;
import org.jdom.Document;
import org.jdom.Element;
import org.jdom.JDOMException;
import org.jdom.input.SAXBuilder;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.UnsupportedEncodingException;

import static java.net.URLDecoder.*;

/**
 * Created by Roman on 02.10.14.
 */
public class MORConfig {

    final public static String      whoIsMe         = "WebRadio";
    final public static String      binLocation     =
            new File(urldecode(
                    WebRadio.class.getProtectionDomain().getCodeSource().getLocation().getPath()
            )).getParent();

    private static Document props;

    public static void init() {
        try {
            props = new SAXBuilder().build(new FileInputStream(binLocation + "/config.xml"));
        } catch (JDOMException | IOException e) {
            e.printStackTrace();
        }
    }

    public static Element getRoot() {
        return props.getRootElement();
    }

    private static String urldecode(String url) {
        String decoded = null;
        try {
            decoded = decode(url, "UTF-8");
        } catch (UnsupportedEncodingException neverOccurs) {/*NOP*/}
        return decoded;
    }

}
