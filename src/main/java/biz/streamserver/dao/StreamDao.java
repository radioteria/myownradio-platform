package biz.streamserver.dao;

import biz.streamserver.entities.Stream;
import org.springframework.stereotype.Repository;

/**
 * Created by roman on 8/10/16
 */
@Repository
public class StreamDao extends AbstractDao<Stream> {
    public StreamDao() {
        super(Stream.class);
    }
}
