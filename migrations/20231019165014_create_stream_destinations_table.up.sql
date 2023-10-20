CREATE TABLE `stream_destinations`
(
    `id`               int(11)       not null auto_increment,
    `user_id`          int(11)       not null,
    `destination_json` varchar(4096) not null,
    `created_at`       datetime      not null,
    `updated_at`       datetime      not null,
    primary key (`id`),
    constraint `r_users_uid` FOREIGN KEY (`user_id`) REFERENCES mor.`r_users` (`uid`)
);
