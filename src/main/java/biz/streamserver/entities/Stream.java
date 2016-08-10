package biz.streamserver.entities;

import javax.persistence.*;

/**
 * Created by roman on 8/10/16
 */
@Entity
@Table(name="r_streams")
public class Stream
{
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "sid")
    private Long id;

    @Column(name = "uid")
    private Long userId;

    @Column(name = "access")
    private String access;

    @Column(name = "name", length = 32)
    private String name;

    @Column(name = "jingle_interval")
    private Integer jingleInterval;

    @Column(name = "status")
    private Integer status;

    @Column(name = "started")
    private Long started;

    @Column(name = "started_from")
    private Long startedFrom;

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public Long getUserId() {
        return userId;
    }

    public void setUserId(Long userId) {
        this.userId = userId;
    }

    public String getAccess() {
        return access;
    }

    public void setAccess(String access) {
        this.access = access;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public Integer getJingleInterval() {
        return jingleInterval;
    }

    public void setJingleInterval(Integer jingleInterval) {
        this.jingleInterval = jingleInterval;
    }

    public Integer getStatus() {
        return status;
    }

    public void setStatus(Integer status) {
        this.status = status;
    }

    public Long getStarted() {
        return started;
    }

    public void setStarted(Long started) {
        this.started = started;
    }

    public Long getStartedFrom() {
        return startedFrom;
    }

    public void setStartedFrom(Long startedFrom) {
        this.startedFrom = startedFrom;
    }
}
