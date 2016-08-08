package biz.streamserver.db;

import java.sql.Connection;

/**
 * Created by roman on 8/8/16
 */
interface ConnectionProvider
{
     Connection getConnection();
}
