package gemini.myownradio.tools;

import com.mchange.v2.c3p0.ComboPooledDataSource;

import javax.sql.DataSource;
import java.beans.PropertyVetoException;
import java.sql.Connection;
import java.sql.SQLException;

/**
 * Created by Roman on 30.09.14.
 */
public class ConnectionPool {

    final private static DataSource dataSource;

    static {

        try {

            ComboPooledDataSource cpds = new ComboPooledDataSource();

            cpds.setDriverClass(MORSettings.getFirstString("server", "jdbc_driver", "com.mysql.jdbc.Driver"));

            cpds.setJdbcUrl(String.format("jdbc:mysql://%s:3306/%s",
                    MORSettings.getFirstString("database", "db_hostname", "127.0.0.1"),
                    MORSettings.getFirstString("database", "db_database", "myownradio")));

            cpds.setUser(MORSettings.getFirstString("database", "db_login", "mor"));
            cpds.setPassword(MORSettings.getFirstString("database", "db_password", ""));

            cpds.setMinPoolSize(1);
            cpds.setMaxPoolSize(20);

            cpds.setAcquireIncrement(1);
            cpds.setMaxIdleTime(30);
            cpds.setMaxStatements(20);

            dataSource = cpds;

        } catch (PropertyVetoException e) {

            RuntimeException rte = new RuntimeException("ConnectionPool connection pool couldn't be initialized!");
            rte.addSuppressed(e);
            throw rte;

        }

    }

    public static Connection newConnection() throws SQLException {
        return dataSource.getConnection();
    }

}

