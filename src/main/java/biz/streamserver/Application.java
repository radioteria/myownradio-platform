package biz.streamserver;

import org.springframework.stereotype.Service;

@Service
public class Application
{
    void setup()
    {
        System.err.println("Setting up application");
    }

    void start()
    {
        System.err.println("Starting application");
    }

    void stop()
    {
        System.err.println("Stopping application");
    }
}
