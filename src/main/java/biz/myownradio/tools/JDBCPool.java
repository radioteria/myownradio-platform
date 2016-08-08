package biz.myownradio.tools;

import org.apache.commons.dbcp.BasicDataSource;

import javax.sql.DataSource;
import java.sql.Connection;
import java.sql.SQLException;

/**
 * Created by Roman on 29.10.14
 */
public class JDBCPool {

    private static DataSource dataSource;

    private static Logger logger = new Logger(Logger.MessageKind.SERVER);


    static {
        BasicDataSource ds = new BasicDataSource();

        ds.setDriverClassName(MORSettings.getString("jdbc.driver"));
        ds.setUrl(MORSettings.getString("jdbc.url"));

        String login = MORSettings.getString("jdbc.login");
        String password = MORSettings.getString("jdbc.password");

        ds.setUsername(login);
        ds.setPassword(password);

        logger.printf("Using login: %s", login);
        logger.printf("Using password: %s", new String(new char[password.length()]).replace('\0', '*'));

        ds.setMinIdle(1);
        ds.setMaxIdle(20);
        ds.setMaxOpenPreparedStatements(20);
        ds.setTestOnBorrow(true);
        ds.setDefaultTransactionIsolation(Connection.TRANSACTION_SERIALIZABLE);
        ds.setDefaultAutoCommit(false);

        dataSource = ds;
    }

    public static Connection getConnection() throws SQLException {
        return dataSource.getConnection();
    }

}

