package biz.myownradio.engine.entity;

import biz.myownradio.tools.MORSettings;

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
    private String fileServerHost;
    private String fileHash;

    private int playlistTime;

    public Track(ResultSet rs, int playlistTime) throws SQLException {
        this.track_id =  rs.getInt("a.track_id");
        this.user_id =  rs.getInt("a.uid");
        this.filename = rs.getString("a.filename");
        this.extension = rs.getString("a.ext");
        this.artist = rs.getString("a.artist");
        this.title = rs.getString("a.title");
        this.duration = rs.getLong("a.duration");
        this.fileSize = rs.getLong("a.filesize");
        this.uniqueId = rs.getString("a.unique_id");
        this.timeOffset = rs.getLong("a.time_offset");
        this.orderIndex = rs.getLong("a.t_order");
        this.fileServerHost = rs.getString("c.fs_host");
        this.fileHash = rs.getString("b.file_hash");
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

    public String getFileUrl() {

        return String.format(
                "https://s3.%s.amazonaws.com/%s/audio/%s/%s/%s",
                MORSettings.getStringNow("aws.s3.region"),
                MORSettings.getStringNow("aws.s3.bucket"),
                fileHash.charAt(0),
                fileHash.charAt(1),
                fileHash
        );

    }

    public int getTrackOffset() {
        return this.playlistTime - (int) this.getTimeOffset();
    }

    public long getTimeRemainder() {
        return this.getDuration() - this.getTrackOffset();
    }
}
