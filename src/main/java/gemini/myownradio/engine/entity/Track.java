package gemini.myownradio.engine.entity;

import gemini.myownradio.tools.MORSettings;

import java.io.File;
import java.io.FileNotFoundException;

/**
 * Created by Roman on 01.10.14.
 */
public class Track {
    private int track_id;
    private int user_id;
    private String filename;
    private String extension;
    private String artist;
    private String title;
    private long duration;
    private long fileSize;
    private String uniqueId;
    private long timeOffset;
    private long orderIndex;

    private int playlistTime;

    public Track(int track_id, int user_id, String filename, String extension, String artist, String title,
                 long duration, long fileSize, String uniqueId, long timeOffset, long orderIndex, int playlistTime) {
        this.track_id = track_id;
        this.user_id = user_id;
        this.filename = filename;
        this.extension = extension;
        this.artist = artist;
        this.title = title;
        this.duration = duration;
        this.fileSize = fileSize;
        this.uniqueId = uniqueId;
        this.timeOffset = timeOffset;
        this.orderIndex = orderIndex;
        this.playlistTime = playlistTime;
    }

    public String getTitle() {
        return this.artist + " - " + this.title;
    }

    public int getTrackId() {
        return track_id;
    }

    public int getUserId() {
        return user_id;
    }

    public String getExtension() {
        return extension;
    }

    public String getArtist() {
        return artist;
    }

    public long getDuration() {
        return duration;
    }

    public long getFileSize() {
        return fileSize;
    }

    public String getUniqueId() {
        return uniqueId;
    }

    public long getOrderIndex() {
        return orderIndex;
    }

    public long getTimeOffset() {
        return timeOffset;
    }

    public String getFilename() {
        return filename;
    }

    public File getPath() throws FileNotFoundException {
        File ff;

        ff = new File(String.format("%s/ui_%d/a_%03d_original.%s",
                MORSettings.getFirstString("content", "content_folder", "content"),
                this.getUserId(),
                this.getTrackId(),
                this.getExtension()
        ));
        if (ff.exists()) {
            return ff;
        }
        ff = new File(String.format("%s/ui_%d/lores_%03d.mp3",
                MORSettings.getFirstString("content", "content_folder", "content"),
                this.getUserId(),
                this.getTrackId()
        ));
        if (ff.exists()) {
            return ff;
        }
        throw new FileNotFoundException(ff.getPath());
    }

    public int getTrackOffset() {
        return this.playlistTime - (int) this.getTimeOffset();
    }

    public long getTimeRemainder() {
        return this.getDuration() - this.getTrackOffset();
    }
}
