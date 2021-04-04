package biz.myownradio.engine.entity;

import biz.myownradio.exception.RadioException;
import biz.myownradio.exception.RadioNothingPlayingException;
import biz.myownradio.exception.RadioStreamNotFoundException;
import biz.myownradio.tools.JDBCPool;
import biz.myownradio.tools.MORLogger;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Created by Roman on 30.09.14.
 */
public class Stream {

    // Stream object params
    private int stream_id;
    private long started;
    private int owner;
    private String access;
    private long started_from;
    private long stream_status;
    private int jingle_interval;

    private String stream_name;

    // Additional stream params
    private long duration;
    private long count;

    private int stream_link;
    private int max_clients;

    private final static MORLogger logger = new MORLogger(MORLogger.MessageKind.PLAYER);

    public String getName() {
        return stream_name;
    }

    public Stream(int stream_id) throws RadioException, SQLException {
        this.stream_link = stream_id;
        reload();
    }

    public Stream reload() throws RadioException, SQLException {

        logger.sprintf("Reloading stream %s", stream_link);

        Connection db = null;
        PreparedStatement ps = null;
        ResultSet rs = null;

        try {

            db = JDBCPool.getConnection();

            ps = db.prepareStatement("SELECT * FROM r_streams WHERE sid = ?");
            ps.setInt(1, this.stream_link);
            ps.execute();

            rs = ps.getResultSet();

            if (!rs.next()) {
                throw new RadioStreamNotFoundException(String.format("No stream %s found!", this.stream_link));
            }

            this.stream_id = rs.getInt("sid");
            this.started = rs.getLong("started");
            this.started_from = rs.getLong("started_from");
            this.stream_status = rs.getLong("status");
            this.stream_name = rs.getString("name");
            this.jingle_interval = rs.getInt("jingle_interval");
            this.owner = rs.getInt("uid");
            this.access = rs.getString("access");

            ps = db.prepareStatement("SELECT * FROM r_static_stream_vars WHERE stream_id = ?");
            ps.setLong(1, this.stream_id);
            ps.execute();

            rs = ps.getResultSet();

            if (!rs.next()) {
                this.duration = 0L;
                this.count = 0L;
            } else {
                this.duration = rs.getLong("tracks_duration");
                this.count = rs.getLong("tracks_count");
            }

            ps = db.prepareStatement("SELECT mor_limits.max_listeners FROM mor_limits INNER JOIN mor_plans ON mor_limits.limit_id = mor_plans.limit_id INNER JOIN mor_users_view ON mor_plans.plan_id = mor_users_view.plan_id WHERE mor_users_view.uid = ?");
            ps.setInt(1, owner);
            ps.execute();

            rs = ps.getResultSet();

            if (rs.next()) {
                this.max_clients = rs.getInt(1);
            } else {
                this.max_clients = 0;
            }

        } finally {

            if (db != null) {
                db.close();
            }

        }

        return this;

    }

    public Stream skipMilliseconds(long timeToSkip) throws SQLException {

        PreparedStatement ps = null;
        ResultSet rs = null;

        try(Connection db = JDBCPool.getConnection()) {
            ps = db.prepareStatement(
                    "UPDATE r_streams SET started_from = started_from + ? WHERE sid = ? AND started_from = ?");
            ps.setLong(1, timeToSkip);
            ps.setInt(2, this.stream_id);
            ps.setLong(3, this.started_from);
            ps.executeUpdate();
            db.commit();
            ps.close();
        }

        return this;

    }

    public long getStatus() {
        return this.stream_status;
    }

    public int getId() {
        return this.stream_id;
    }

    public long getTracksCount() {
        return this.count;
    }

    public long getTracksDuration() {
        return this.duration;
    }

    public int getJingleInterval() {
        return jingle_interval;
    }

    public long getCurrentTime(int offset) throws RadioNothingPlayingException {

        if (this.getStatus() == 0) {
            throw new RadioNothingPlayingException("Stream status off");
        }

        long time = System.currentTimeMillis();

        return Math.max((time - this.started + this.started_from - offset) % this.duration, 0);

    }

    public Track getNowPlaying(int offset) throws RadioNothingPlayingException, SQLException {

        Connection db = null;
        Track track = null;

        long streamPosition = this.getCurrentTime(offset);

        logger.sprintf("Current stream %s, position %d", stream_link, streamPosition);

        try {

            db = JDBCPool.getConnection();

            PreparedStatement statement = db.prepareStatement("SELECT a.*,b.*,c.* FROM mor_stream_tracklist_view a INNER JOIN fs_file b ON a.file_id = b.file_id INNER JOIN fs_list c ON b.server_id = c.fs_id WHERE a.time_offset <= ? AND a.time_offset + a.duration >= ? AND a.stream_id = ? ORDER BY a.t_order LIMIT 1");
            statement.setLong(1, streamPosition);
            statement.setLong(2, streamPosition);
            statement.setInt(3, stream_id);
            statement.execute();

            ResultSet rs = statement.getResultSet();

            if (!rs.next()) {
                RadioNothingPlayingException e = new RadioNothingPlayingException(String.format("No track near %d on %d", streamPosition, stream_id));
                logger.exception(e);
                db.close();
                throw e;
            }

            track = new Track(rs, (int) streamPosition);

            logger.sprintf("Now playing %s from %d ms", track.getTitle(), track.getTimeOffset());

        } finally {

            if (db != null) {
                db.close();
            }

        }

        return track;

    }

    public int getMaxClients() {
        return max_clients;
    }

    public String getAccess() {
        return access;
    }

    public int getOwner() {
        return owner;
    }
}
