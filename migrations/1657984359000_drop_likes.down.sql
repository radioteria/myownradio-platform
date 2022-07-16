create table mor_track_like
(
    track_id int                                                not null,
    user_id  int                                                not null,
    relation enum ('like', 'dislike') default 'like'            not null,
    date     timestamp                default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    primary key (track_id, user_id),
    constraint mor_track_like_ibfk_1
        foreign key (track_id) references r_tracks (tid)
            on update cascade on delete cascade,
    constraint mor_track_like_ibfk_2
        foreign key (user_id) references r_users (uid)
            on update cascade on delete cascade
)
    charset = utf8mb3;

create index by_relation
    on mor_track_like (relation);

create index by_track
    on mor_track_like (track_id);

create index by_user
    on mor_track_like (user_id);

create trigger `like.when.added`
    after insert
    on mor_track_like
    for each row
    IF (NEW.relation = "like") THEN
        UPDATE mor_track_stat SET likes = likes + 1 WHERE track_id = NEW.track_id;
    ELSE
        UPDATE mor_track_stat SET dislikes = dislikes + 1 WHERE track_id = NEW.track_id;
    END IF;

create trigger `like.when.deleted`
    after delete
    on mor_track_like
    for each row
    IF (OLD.relation = "like") THEN
        UPDATE mor_track_stat SET likes = likes - 1 WHERE track_id = OLD.track_id;
    ELSE
        UPDATE mor_track_stat SET dislikes = dislikes - 1 WHERE track_id = OLD.track_id;
    END IF;

