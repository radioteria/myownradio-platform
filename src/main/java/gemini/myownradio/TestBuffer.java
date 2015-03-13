package gemini.myownradio;

import gemini.myownradio.tools.io.SharedFile.SharedFileReader;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;

/**
 * Created by Roman on 29.12.2014.
 */
public class TestBuffer {
    public static void main(String[] args) throws IOException, InterruptedException {

        int threadCount = 4;
        File file = new File("/Volumes/3TB/Фильмы/Jacques.Cousteau.The.Silent.World.1956.720p.BluRay.Rus.Fre.Eng.HDCLUB.mkv");

        Runnable r = () -> {
            try (InputStream is = new SharedFileReader(file).open()) {
                int length;
                byte[] buffer = new byte[4096];
                long count = 0;
                while ((length = is.read(buffer)) != 0) {
                    count += length;
                }
            } catch (IOException e) {
                e.printStackTrace();
            }
        };

        Thread[] threads = new Thread[threadCount];

        for (int i = 0; i < threads.length; i++) {
            threads[i] = new Thread(r);
            threads[i].start();
        }

        for (Thread t : threads) {
            t.join();
        }
    }
}
