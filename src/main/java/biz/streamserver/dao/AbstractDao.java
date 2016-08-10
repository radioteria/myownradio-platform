package biz.streamserver.dao;

import org.hibernate.Session;
import org.hibernate.SessionFactory;

import javax.annotation.Resource;
import java.util.Optional;

/**
 * Created by roman on 8/8/16
 */
public abstract class AbstractDao<T> implements DaoInterface<T>
{
    @Resource
    protected SessionFactory sessionFactory;

    private Class<T> tClass;

    public AbstractDao(Class<T> tClass)
    {
        this.tClass = tClass;
    }

    protected Session getSession()
    {
        return sessionFactory.getCurrentSession();
    }

    public Optional<T> findById(long id)
    {
        T t = getSession().get(tClass, id);

        return Optional.ofNullable(t);
    }
}
