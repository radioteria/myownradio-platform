--
-- Fix user static counters
--

UPDATE r_static_user_vars SET
  tracks_count = IFNULL((SELECT COUNT(*) FROM r_tracks WHERE uid = r_static_user_vars.user_id), 0),
  tracks_duration = IFNULL((SELECT SUM(r_tracks.duration) FROM r_tracks WHERE uid = r_static_user_vars.user_id), 0),
  tracks_size = IFNULL((SELECT SUM(r_tracks.filesize) FROM r_tracks WHERE uid = r_static_user_vars.user_id), 0)
