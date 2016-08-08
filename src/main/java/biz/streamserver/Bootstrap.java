package biz.streamserver;

import org.springframework.context.ApplicationContext;
import org.springframework.context.support.ClassPathXmlApplicationContext;

/**
 * Created by roman on 8/8/16
 */
public class Bootstrap {
    public static void main(String[] args) {
        ApplicationContext applicationContext =
                new ClassPathXmlApplicationContext("classpath:spring/applicationContext.xml");

        Application application = (Application) applicationContext.getBean(Application.class);

        application.setup();
        application.start();
        application.stop();
    }
}
