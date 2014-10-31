package gemini.myownradio.engine.entity;import gemini.myownradio.exception.RadioException;import gemini.myownradio.exception.RadioNothingPlayingException;import gemini.myownradio.exception.RadioStreamNotFoundException;import gemini.myownradio.tools.JDBCPool;import java.sql.Connection;import java.sql.PreparedStatement;import java.sql.ResultSet;import java.sql.SQLException;/** * Created by Roman on 30.09.14. */public class Stream {    // Stream object params    private int stream_id;    private long started;    private long started_from;    private long stream_status;    private String stream_name;    // Additional stream params    private long duration;    private long count;    private String stream_link;    public String getName() {        return stream_name;    }    public Stream(String stream_id) throws RadioException, SQLException {        this.stream_link = stream_id;        reload();    }    public Stream reload() throws RadioException, SQLException {        Connection db = null;        PreparedStatement ps = null;        ResultSet rs = null;        try {            db = JDBCPool.getConnection();            ps = db.prepareStatement("SELECT * FROM r_streams WHERE sid = ?");            ps.setString(1, this.stream_link);            ps.execute();            rs = ps.getResultSet();            if (!rs.next()) {                throw new RadioStreamNotFoundException();            }            this.stream_id      = rs.getInt("sid");            this.started        = rs.getLong("started");            this.started_from   = rs.getLong("started_from");            this.stream_status  = rs.getLong("status");            this.stream_name    = rs.getString("name");            ps = db.prepareStatement("SELECT * FROM r_static_stream_vars WHERE stream_id = ?");            ps.setLong(1, this.stream_id);            ps.execute();            rs = ps.getResultSet();            if (!rs.next()) {                this.duration = 0L;                this.count = 0L;            } else {                this.duration       = rs.getLong("tracks_duration");                this.count          = rs.getLong("tracks_count");            }        } finally {            if (db != null) {                db.close();            }        }        return this;    }    public long getStatus() {        return this.stream_status;    }    public int getId() {        return this.stream_id;    }    public long getTracksCount() {        return this.count;    }    public long getTracksDuration() {        return this.duration;    }    public long getCurrentTime(int offset) throws RadioNothingPlayingException {        if (this.getStatus() == 0) {            throw new RadioNothingPlayingException();        }        long time = System.currentTimeMillis();        return (time - this.started + this.started_from - offset) % this.duration;    }    public Track getNowPlaying() throws RadioNothingPlayingException, SQLException {        return getNowPlaying(0);    }    public Track getNowPlaying(int offset) throws RadioNothingPlayingException, SQLException {        Connection db = null;        Track track = null;        long streamPosition = this.getCurrentTime(offset);        try {            db = JDBCPool.getConnection();            PreparedStatement statement = db.prepareStatement("SELECT a.*, b.unique_id, b.t_order, b.time_offset FROM r_tracks a, r_link b WHERE b.time_offset <= ? AND b.time_offset + a.duration >= ? AND a.tid = b.track_id AND b.stream_id = ? AND a.lores = 1 ORDER BY b.t_order LIMIT 1");            statement.setLong(1, streamPosition);            statement.setLong(2, streamPosition);            statement.setLong(3, stream_id);            statement.execute();            ResultSet rs = statement.getResultSet();            if (!rs.next()) {                db.close();                throw new RadioNothingPlayingException();            }            track = new Track(                    rs.getInt("a.tid"),                    rs.getInt("a.uid"),                    rs.getString("a.filename"),                    rs.getString("a.ext"),                    rs.getString("a.artist"),                    rs.getString("a.title"),                    rs.getLong("a.duration"),                    rs.getLong("a.filesize"),                    rs.getString("b.unique_id"),                    rs.getLong("b.time_offset"),                    rs.getLong("b.t_order"),                    (int) streamPosition            );        } finally {            if (db != null) {                db.close();            }        }        return track;    }}