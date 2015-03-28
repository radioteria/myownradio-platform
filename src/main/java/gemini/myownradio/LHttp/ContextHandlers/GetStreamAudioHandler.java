package gemini.myownradio.LHttp.ContextHandlers;

import gemini.myownradio.LHttp.LHttpException;
import gemini.myownradio.LHttp.LHttpHandler;
import gemini.myownradio.LHttp.LHttpProtocol;
import gemini.myownradio.engine.AudioFlowBootstrap;
import gemini.myownradio.exception.RadioException;
import gemini.myownradio.ff.FFEncoderBuilder;
import gemini.myownradio.flow.AudioFormatsRegister;
import gemini.myownradio.tools.JDBCPool;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Created by Roman on 16.10.14.
 */
public class GetStreamAudioHandler implements LHttpHandler {

    public void handle(LHttpProtocol exchange) throws IOException {

        int stream;
        try {
            stream = Integer.parseInt(exchange.getParameter("s").orElseThrow(LHttpException::badRequest));
        } catch (NumberFormatException e) {
            throw LHttpException.badRequest();
        }
        boolean metadata = exchange.headerEquals("icy-metadata", "1");

        String format = exchange.getParameter("f", "mp3_128k");
        String clientId = exchange.getParameter("client_id", "");
        String clientIp = exchange.getClientIP();


        try {
            // Read current user from database
            PreparedStatement ps = null;
            ResultSet rs = null;
            int limitId = 1;

            try (Connection db = JDBCPool.getConnection();) {
                ps = db.prepareStatement("SELECT mor_plans.limit_id FROM mor_plans INNER JOIN mor_users_view ON mor_plans.plan_id = mor_users_view.plan_id INNER JOIN r_sessions ON r_sessions.uid = mor_users_view.uid WHERE r_sessions.client_id = ? AND r_sessions.ip = ?");
                ps.setString(1, clientId);
                ps.setString(2, clientIp);
                ps.execute();
                rs = ps.getResultSet();
                if (rs.next()) {
                    limitId = rs.getInt(1);
                }
            }

            FFEncoderBuilder decoder = AudioFormatsRegister.analyzeFormat(format, limitId);

            AudioFlowBootstrap radio = new AudioFlowBootstrap(exchange, stream, decoder, metadata);
            radio.startStreamer();

        } catch (SQLException | RadioException e) {
            e.printStackTrace();
        }

    }

}
