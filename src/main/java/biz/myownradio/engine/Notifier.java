package biz.myownradio.engine;

import com.fasterxml.jackson.databind.ObjectMapper;

import java.io.IOException;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.concurrent.BlockingQueue;
import java.util.concurrent.LinkedBlockingQueue;

/**
 * Created by LRU on 01.05.2015.
 */
public class Notifier {

    public static class Event {

        private Object subject;
        private String event;
        private Object data;

        public Event(String event, Object subject, Object data) {
            this.event = event;
            this.subject = subject;
            this.data = data;
        }

        public Event(String event, Object subject) {
            this(event, subject, null);
        }

        public Object getSubject() {
            return subject;
        }

        public String getEvent() {
            return event;
        }

        public Object getData() {
            return data;
        }

        public void send(String keys) {
            event(keys, this);
        }

        public void queue(String keys) {
            jobs.add(() -> event(keys, this));
        }

    }

    final private static String NOTIFY_URL = "http://myownradio.biz:8080/notif1er/notify?app=mor";

    final private static BlockingQueue<Runnable> jobs = new LinkedBlockingQueue<>();

    static {
        Thread thread = new Thread(() -> {
            while (true) {
                try {
                    jobs.take().run();
                } catch (InterruptedException e) {
                    break;
                }
            }
        });
        thread.setName("Event Pool");
        thread.start();
    }

    public static void notify(String key, Object data) {
        ObjectMapper mapper = new ObjectMapper();
        try {
            URL url = new URL(NOTIFY_URL + "&keys=" + key);
            HttpURLConnection connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.setDoOutput(true);
            connection.setRequestProperty("Content-Type", "text/plain");
            connection.setRequestProperty("charset", "utf-8");
            connection.setConnectTimeout(1000);
            try (OutputStream os = connection.getOutputStream()) {
                mapper.writeValue(os, data);
            }
            connection.getContent();
            connection.disconnect();
        } catch (IOException e) {
            /* NOP */
        }
    }

    public static void event(String keys, Event event) {
        notify(keys, event);
    }

    public static int queueSize() {
        return  jobs.size();
    }

}
