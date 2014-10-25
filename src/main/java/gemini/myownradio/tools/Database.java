package gemini.myownradio.tools;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/**
 * Created by Roman on 30.09.14.
 */
public class Database {

    private String db_host = "myownradio.biz";
    private String db_base = "myownradio";
    private String db_user = "mor";
    private String db_pass = "3bWdNNa0v";

    private Connection dbo;

    private static Database instance;

    public Database() throws SQLException {
        this.dbo = DriverManager.getConnection(
                String.format("jdbc:mysql://%s:3306/%s",
                        MORConfig.getRoot().getChild("database").getChild("hostname").getValue(),
                        MORConfig.getRoot().getChild("database").getChild("database").getValue()),
                MORConfig.getRoot().getChild("database").getChild("login").getValue(),
                MORConfig.getRoot().getChild("database").getChild("password").getValue()
        );
    }

    public static Database getInstance() throws SQLException {
        if (instance == null) {
            instance = new Database();
        }
        return instance;
    }

    public Connection dbo() {
        return this.dbo;
    }

}

