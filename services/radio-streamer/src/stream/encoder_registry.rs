use crate::audio_formats::AudioFormat;
use crate::metrics::Metrics;
use crate::stream::channel_encoder::{ChannelEncoder, ChannelEncoderError};
use crate::stream::player_registry::PlayerRegistryError;
use crate::PlayerRegistry;
use slog::{debug, Logger};
use std::collections::hash_map::Entry;
use std::collections::HashMap;
use std::sync::{Arc, Mutex};

#[derive(Debug, Hash, Eq, PartialEq, Clone)]
struct EncoderEntry(usize, Option<String>, AudioFormat);

#[derive(Debug)]
pub(crate) enum EncoderRegistryError {
    PlayerRegistryError(PlayerRegistryError),
    ChannelEncoderError(ChannelEncoderError),
}

#[derive(Clone)]
pub(crate) struct EncoderRegistry {
    path_to_ffmpeg: String,
    logger: Logger,
    metrics: Arc<Metrics>,
    encoders_map: Arc<Mutex<HashMap<EncoderEntry, ChannelEncoder>>>,
    player_registry: PlayerRegistry,
}

impl EncoderRegistry {
    pub fn new(
        path_to_ffmpeg: String,
        logger: Logger,
        metrics: Arc<Metrics>,
        player_registry: PlayerRegistry,
    ) -> Self {
        let encoders_map = Arc::default();

        Self {
            path_to_ffmpeg,
            logger,
            metrics,
            encoders_map,
            player_registry,
        }
    }

    pub async fn get_encoder(
        &self,
        channel_id: &usize,
        client_id: &Option<String>,
        format: &AudioFormat,
    ) -> Result<ChannelEncoder, EncoderRegistryError> {
        let key = EncoderEntry(channel_id.clone(), client_id.clone(), format.clone());

        let encoder = match self.encoders_map.lock().unwrap().entry(key.clone()) {
            Entry::Occupied(entry) => {
                debug!(self.logger, "Reusing existing channel encoder"; "channel_id" => ?channel_id, "format" => ?format);

                entry.get().clone()
            }
            Entry::Vacant(entry) => {
                debug!(self.logger, "Creating new channel encoder"; "channel_id" => ?channel_id, "format" => ?format);

                let player = self
                    .player_registry
                    .get_player(channel_id, client_id)
                    .await
                    .map_err(|err| EncoderRegistryError::PlayerRegistryError(err))?;

                let encoder = ChannelEncoder::create(
                    &player,
                    format,
                    &self.path_to_ffmpeg.clone(),
                    &self.logger,
                    self.metrics.clone(),
                    {
                        let encoders_map = self.encoders_map.clone();

                        move || {
                            encoders_map.lock().unwrap().remove(&key);
                        }
                    },
                )
                .await
                .map_err(|err| EncoderRegistryError::ChannelEncoderError(err))?;

                entry.insert(encoder.clone());

                encoder
            }
        };

        Ok(encoder)
    }
}
