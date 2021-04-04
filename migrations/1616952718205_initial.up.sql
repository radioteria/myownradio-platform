alter table fs_file add column `file_extension` varchar(128) not null default '' AFTER `file_hash`;
update `fs_file` set `file_extension` = (select `ext` from `r_tracks` where `file_id` = `fs_file`.`file_id` LIMIT 1);
