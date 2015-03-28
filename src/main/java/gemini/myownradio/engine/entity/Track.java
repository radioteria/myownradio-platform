package gemini.myownradio.engine.entity;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.net.URL;
import java.sql.ResultSet;
import java.sql.SQLException;

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

    public Track(ResultSet rs, int playlistTime) throws SQLException {
        this.track_id =  rs.getInt("track_id");
        this.user_id =  rs.getInt("uid");
        this.filename = rs.getString("filename");
        this.extension = rs.getString("ext");
        this.artist = rs.getString("artist");
        this.title = rs.getString("title");
        this.duration = rs.getLong("duration");
        this.fileSize = rs.getLong("filesize");
        this.uniqueId = rs.getString("unique_id");
        this.timeOffset = rs.getLong("time_offset");
        this.orderIndex = rs.getLong("t_order");
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

    public String getPath() throws FileNotFoundException {

        String link;
        URL ff;

        String template = "ftp://morstorage:3bWdNNa0v@myownradio.biz/content";

        try {

            link = String.format("%s/ui_%d/a_%03d_original.%s",
                    template,
                    this.getUserId(),
                    this.getTrackId(),
                    this.getExtension()
            );
            ff = new URL(link);

            try (InputStream is = ff.openConnection().getInputStream()) {
                    return link;
            } catch (IOException e) {
                /* NOP */
            }

            link = String.format("%s/ui_%d/lores_%03d.mp3",
                    template,
                    this.getUserId(),
                    this.getTrackId()
            );
            ff = new URL(link);

            try (InputStream is = ff.openConnection().getInputStream()) {
                return link;
            }

        } catch (IOException e) {
            throw new FileNotFoundException();
        }

    }

    public InputStream openStream() throws FileNotFoundException {
        return new FileInputStream(this.getPath());
    }

    public int getTrackOffset() {
        return this.playlistTime - (int) this.getTimeOffset();
    }

    public long getTimeRemainder() {
        return this.getDuration() - this.getTrackOffset();
    }
}
