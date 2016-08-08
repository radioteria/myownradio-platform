package biz.streamserver;

import biz.streamserver.core.Application;
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

        Application application = applicationContext.getBean(Application.class);

        application.start();
    }
}
