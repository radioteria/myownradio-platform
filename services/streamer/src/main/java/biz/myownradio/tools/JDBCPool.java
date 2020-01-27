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

    private static MORLogger logger = new MORLogger(MORLogger.MessageKind.SERVER);


    static {
        BasicDataSource ds = new BasicDataSource();

        ds.setDriverClassName(MORSettings.getString("jdbc.driver").orElse("com.mysql.jdbc.Driver"));
        ds.setUrl(MORSettings.getString("jdbc.url").orElse("jdbc:mysql://localhost:3306/myownradio"));

        String login = MORSettings.getString("jdbc.login").orElse("root");
        String password = MORSettings.getString("jdbc.password").orElse("");

        ds.setUsername(login);
        ds.setPassword(password);

        logger.sprintf("Using login: %s", login);
        logger.sprintf("Using password: %s", new String(new char[password.length()]).replace('\0', '*'));

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

