package biz.myownradio.LHttp.ContextHandlers;

import biz.myownradio.LHttp.LHttpException;
import biz.myownradio.LHttp.LHttpHandler;
import biz.myownradio.LHttp.LHttpProtocol;
import biz.myownradio.engine.AudioFlowBootstrap;
import biz.myownradio.exception.RadioException;
import biz.myownradio.ff.FFEncoderBuilder;
import biz.myownradio.flow.AudioFormatsRegister;
import biz.myownradio.tools.JDBCPool;
import biz.myownradio.tools.MORLogger;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Created by Roman on 16.10.14.
 */
public class GetStreamAudioHandler implements LHttpHandler {

    private static MORLogger logger = new MORLogger(MORLogger.MessageKind.SERVER);

    public void handle(LHttpProtocol exchange) throws IOException {

        logger.println("Parsing request");

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

        logger.sprintf("Stream ID: %s", stream);
        logger.sprintf("Use Metadata: %s", metadata);
        logger.sprintf("Client ID: %s", clientId);
        logger.sprintf("Client IP: %s", clientIp);

        try {
            // Read current user from database
            PreparedStatement ps;
            ResultSet rs;
            int limitId = 1;

            logger.println("Reading plans");

            try (Connection db = JDBCPool.getConnection()) {
                ps = db.prepareStatement("SELECT mor_plans.limit_id FROM mor_plans INNER JOIN mor_users_view ON mor_plans.plan_id = mor_users_view.plan_id INNER JOIN r_sessions ON r_sessions.uid = mor_users_view.uid WHERE r_sessions.client_id = ? AND r_sessions.ip = ? AND r_sessions.client_id != ''");
                ps.setString(1, clientId);
                ps.setString(2, clientIp);
                ps.execute();
                rs = ps.getResultSet();
                if (rs.next()) {
                    limitId = rs.getInt(1);
                }
            }

            logger.println("Initializing encoder");

            FFEncoderBuilder encoder = AudioFormatsRegister.analyzeFormat(format, limitId);

            logger.sprintf("Selected encoder: %s", encoder);

            AudioFlowBootstrap radio = new AudioFlowBootstrap(exchange, stream, encoder, metadata);

            radio.startStreamer();


        } catch (Exception e) {
            e.printStackTrace();
        }

    }

}
