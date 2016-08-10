package biz.streamserver.services;

import biz.streamserver.dao.StreamDao;
import biz.streamserver.entities.Stream;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import javax.annotation.Resource;
import java.util.Optional;

/**
 * Created by roman on 8/10/16
 */
@Service
@Transactional
public class StreamService {

    @Resource
    StreamDao streamDao;

    public Optional<Stream> findById(Long id) {
        return streamDao.findById(id);
    }
}
