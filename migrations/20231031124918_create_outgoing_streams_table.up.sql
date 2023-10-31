CREATE TABLE `outgoing_streams`
(
    `id`               int(11)       not null auto_increment,
    `user_id`          int(11)       not null,
    `channel_id`       int(11)       not null,
    `stream_id`        varchar(36)   not null,
    `duration`         bigint        not null,
    `byte_count`       bigint        not null,
    `created_at`       datetime      not null,
    `updated_at`       datetime      not null,
    primary key (`id`),
    constraint `outgoing_streams_r_users_uid` FOREIGN KEY (`user_id`) REFERENCES `mor`.`r_users` (`uid`),
    constraint `outgoing_streams_r_streams_sid` FOREIGN KEY (`channel_id`) REFERENCES `mor`.`r_streams` (`sid`)
);
