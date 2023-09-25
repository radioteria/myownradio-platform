use crate::config::Config;
use crate::data_structures::{TrackId, UserId};
use crate::http_server::response::Response;
use crate::mysql_client::MySqlClient;
use crate::services::ffmpeg_service;
use crate::storage::db::repositories::user_tracks;
use crate::storage::db::row_utils::GetFilePath;
use crate::utils::TeeResultUtils;
use actix_web::{web, HttpResponse};
use futures::channel::mpsc;
use serde::Deserialize;
use std::time::Duration;
use tracing::error;

#[derive(Deserialize, Clone)]
pub(crate) enum Format {
    #[serde(rename = "aac")]
    Aac,
    #[serde(rename = "vorbis")]
    Vorbis,
    #[serde(rename = "opus")]
    Opus,
}

impl Default for Format {
    fn default() -> Self {
        Format::Aac
    }
}

#[derive(Deserialize)]
#[serde(rename_all = "camelCase")]
pub(crate) struct TranscodeAudioTrackQuery {
    #[serde(default)]
    #[serde(with = "serde_millis")]
    pub(crate) initial_position: Duration,
    #[serde(default)]
    pub(crate) audio_format: Format,
}

pub(crate) async fn transcode_audio_track(
    user_id: UserId,
    path: web::Path<TrackId>,
    mysql_client: web::Data<MySqlClient>,
    config: web::Data<Config>,
    json: web::Query<TranscodeAudioTrackQuery>,
) -> Response {
    let mut connection = mysql_client.connection().await?;

    let track_id = path.into_inner();
    let track_row = match user_tracks::get_single_user_track(&mut connection, &track_id)
        .await
        .tee_err(|error| error!(?error, "Unable to get user track from database"))?
    {
        Some(track_row) => track_row,
        None => return Ok(HttpResponse::NotFound().finish()),
    };

    if track_row.track.uid != user_id {
        return Ok(HttpResponse::Forbidden().finish());
    }

    let source_path = track_row.get_file_path();
    let source_url = format!("{}audio/{}", config.file_server_endpoint, source_path);

    let (response_tx, response_rx) = mpsc::channel(32);

    actix_rt::spawn({
        let format = json.audio_format.clone();
        let position = json.initial_position.clone();

        async move {
            if let Err(error) = ffmpeg_service::transcode_audio_file(
                &source_url,
                response_tx,
                position,
                ffmpeg_service::TranscodeAudioFileFormat {
                    container: match format {
                        Format::Aac => ffmpeg_service::AudioContainer::Adts,
                        Format::Vorbis => ffmpeg_service::AudioContainer::Webm,
                        Format::Opus => ffmpeg_service::AudioContainer::Webm,
                    },
                    codec: match format {
                        Format::Aac => ffmpeg_service::AudioCodec::Aac,
                        Format::Vorbis => ffmpeg_service::AudioCodec::Vorbis,
                        Format::Opus => ffmpeg_service::AudioCodec::Opus,
                    },
                    channels: ffmpeg_service::AudioChannels::Stereo,
                    bitrate: 256_000,
                    sampling_rate: 48_000,
                },
            )
            .await
            {
                error!(?error, "Error occurred while transcoding audio");
            }
        }
    });

    use futures::StreamExt;

    let mut response = HttpResponse::Ok();

    response
        .content_type(match json.audio_format {
            Format::Aac => "audio/aac",
            Format::Vorbis => "audio/webm; codecs=\"vorbis\"",
            Format::Opus => "audio/webm; codecs=\"opus\"",
        })
        .force_close();

    Ok(response.streaming::<_, actix_web::Error>(response_rx.map(Ok)))
}
