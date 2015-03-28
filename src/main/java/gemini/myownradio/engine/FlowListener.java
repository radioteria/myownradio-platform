package gemini.myownradio.engine;

import gemini.myownradio.tools.JDBCPool;
import gemini.myownradio.tools.MORLogger;

import java.sql.*;

/**
 * Created by Roman on 11.12.14.
 *
 * Statistical object
 */
public class FlowListener {
    private int listener_id;
    private String client_ip;
    private String client_ua;
    private int stream_id;
    private String quality;

    private static MORLogger logger = new MORLogger(MORLogger.MessageKind.SERVER);

    static {
        init();
    }

    public FlowListener(String client_ip, String client_ua, String quality, int stream_id) throws SQLException {
        this.client_ip = client_ip;
        this.client_ua = client_ua;
        this.quality = quality;
        this.stream_id = stream_id;
        this.newListenerId();
    }

    private void newListenerId() throws SQLException {
        PreparedStatement ps;
        ResultSet rs;
        try (Connection connection = JDBCPool.getConnection()) {
            ps = connection.prepareStatement(
                    "INSERT INTO r_listener (client_ip, client_ua, stream, quality, started, finished) VALUES (?, ?, ?, ?, NOW(), NULL)",
                    Statement.RETURN_GENERATED_KEYS);

            ps.setString(1, this.client_ip);
            ps.setString(2, this.client_ua);
            ps.setInt(3, this.stream_id);
            ps.setString(4, this.quality);
            ps.executeUpdate();

            rs = ps.getGeneratedKeys();
            rs.next();

            this.listener_id = rs.getInt(1);

            connection.commit();

            logger.sprintf("New listener obtained ID #%d", this.listener_id);
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void finish() throws SQLException {
        PreparedStatement ps;
        try (Connection connection = JDBCPool.getConnection()) {
            ps = connection.prepareStatement("UPDATE r_listener SET finished = NOW() WHERE client_id = ?");
            ps.setInt(1, this.listener_id);
            ps.executeUpdate();

            connection.commit();
            logger.sprintf("Finishing listener #%d session", this.listener_id);
        }
    }

    public static void init() {
        PreparedStatement ps;
        try (Connection connection = JDBCPool.getConnection()) {
            ps = connection.prepareStatement("UPDATE r_listener SET finished = NOW() WHERE finished IS NULL");
            ps.executeUpdate();
            connection.commit();
        } catch (SQLException e) {
            logger.exception(e);
        }
    }
}
