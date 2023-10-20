alter table mor.`r_streams` add column (
    `rtmp_url` varchar(4096) not null default '',
    `rtmp_streaming_key` varchar(4096) not null default ''
);
