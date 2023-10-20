alter table `stream_destinations` add (
    `stream_id` int(11) not null,
    constraint `r_streams_sid` foreign key (`stream_id`) references mor.`r_streams` (`sid`) on delete cascade
);
