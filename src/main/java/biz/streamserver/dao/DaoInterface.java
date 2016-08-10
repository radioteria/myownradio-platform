package biz.streamserver.dao;

import java.util.Optional;

/**
 * Created by roman on 8/10/16
 */
public interface DaoInterface<T>
{
    Optional<T> findById(long id);
}
