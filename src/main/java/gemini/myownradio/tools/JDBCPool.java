package gemini.myownradio.tools;

import org.apache.commons.dbcp.BasicDataSource;

import javax.sql.DataSource;
import java.sql.Connection;
import java.sql.SQLException;

/**
 * Created by Roman on 29.10.14.
 */
public class JDBCPool {

    private static DataSource dataSource;

    static {
        BasicDataSource ds = new BasicDataSource();

        ds.setDriverClassName(MORSettings.getFirstString("server", "jdbc_driver", "com.mysql.jdbc.Driver"));
        ds.setUrl(String.format("jdbc:mysql://%s:3306/%s",
                MORSettings.getFirstString("database", "db_hostname", "myownradio.biz"),
                MORSettings.getFirstString("database", "db_database", "myownradio")));

        ds.setUsername(MORSettings.getFirstString("database", "db_login", "mor"));
        ds.setPassword(MORSettings.getFirstString("database", "db_password", "3bWdNNa0v"));

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

