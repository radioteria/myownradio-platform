package biz.myownradio.engine.entity;

import biz.myownradio.LHttp.LHttpProtocol;
import biz.myownradio.tools.JDBCPool;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Created by roman on 28.03.15.
 */
public class Client {

    private Integer user_id = null;

    public Client(LHttpProtocol protocol) {

        String client_id = protocol.getParameter("client_id", null);
        PreparedStatement ps;
        ResultSet rs;

        if (client_id == null) {
            return;
        }

        try (Connection db = JDBCPool.getConnection()) {

            ps = db.prepareStatement("SELECT uid FROM r_sessions WHERE client_id = ? AND client_id != ''");
            ps.setString(1, client_id);
            ps.execute();
            rs = ps.getResultSet();
            if (rs.next()) {
                user_id = rs.getInt(1);
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }

    }

    public Integer getUserId() {
        return user_id;
    }
}
