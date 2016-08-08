package biz.streamserver;

import biz.streamserver.core.ApplicationInterface;
import org.springframework.context.ApplicationContext;
import org.springframework.context.support.ClassPathXmlApplicationContext;

/**
 * Created by roman on 8/8/16
 */
public class Bootstrap
{
    public static void main(String[] args)
    {
        ApplicationContext applicationContext =
                new ClassPathXmlApplicationContext("classpath:spring/application-context.xml");

        ApplicationInterface application = applicationContext.getBean(ApplicationInterface.class);

        System.err.println("Setting up application");
        application.setup();

        System.err.println("Starting application");
        application.start();

        System.err.println("Stopping application");
        application.stop();
    }
}
