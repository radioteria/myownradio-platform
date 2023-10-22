use crate::gstreamer_utils::make_element;
use crate::stream_utils::{make_audio_encoder, make_output, make_video_encoder};
use gstreamer::prelude::*;
use gstreamer::{
    Bin, BusSyncReply, ClockTime, Element, MessageView, PadProbeData, PadProbeReturn, PadProbeType,
    Pipeline, State,
};
use std::sync::atomic::{AtomicU64, Ordering};
use std::sync::mpsc::Sender;
use std::sync::Arc;
use std::time::Duration;
use tracing::{debug, info, warn};

#[derive(Debug, thiserror::Error)]
pub(crate) enum CreateStreamError {}

#[derive(Debug)]
pub(crate) enum Error {
    PublishDenied,
    Other,
}

#[derive(Debug)]
pub(crate) enum StreamEvent {
    Error(Error),
    Stats { time_position: u64, byte_count: u64 },
}

pub(crate) enum StreamOutput {
    RTMP { url: String, stream_key: String },
}

pub(crate) enum VideoEncoder {
    Software,
    VA,
}

pub(crate) struct StreamConfig {
    pub(crate) output: StreamOutput,
    pub(crate) video_width: u32,
    pub(crate) video_height: u32,
    pub(crate) video_bitrate: u32,
    pub(crate) video_framerate: u32,
    pub(crate) video_profile: Option<String>,
    pub(crate) video_encoder: VideoEncoder,
    pub(crate) audio_bitrate: u32,
    pub(crate) audio_channels: u32,
    pub(crate) cef_gpu_enabled: bool,
}

pub(crate) struct Stream {
    pipeline: Pipeline,
}

impl Drop for Stream {
    fn drop(&mut self) {
        let _ = self.pipeline.set_state(State::Null);
    }
}

impl Stream {
    pub(crate) fn create(
        stream_id: String,
        webpage_url: String,
        config: &StreamConfig,
        event_sender: Sender<StreamEvent>,
    ) -> Result<Stream, CreateStreamError> {
        let pipeline_name = format!("web-egress-{}", stream_id);
        let pipeline = Pipeline::new(Some(&pipeline_name));

        let audiomixer = make_element("audiomixer");

        let cefbin = make_element("cefbin");
        let cefsrc = cefbin
            .downcast_ref::<Bin>()
            .unwrap()
            .by_name("cefsrc")
            .unwrap();
        cefsrc.set_property("url", webpage_url);
        if config.cef_gpu_enabled {
            info!("Enabling GPU acceleration");
            cefsrc.set_property("gpu", &true);
        }

        pipeline
            .add_many(&[&cefbin, &audiomixer])
            .expect("Unable to add elements to pipeline");

        cefbin.link(&audiomixer).unwrap();

        let (video_sink, video_src) = make_video_encoder(
            &pipeline,
            config.video_width,
            config.video_height,
            config.video_bitrate,
            config.video_framerate,
            &config.video_profile,
            &config.video_encoder,
        );
        let (audio_sink, audio_src) =
            make_audio_encoder(&pipeline, config.audio_bitrate, config.audio_channels);

        Element::link_many(&[&cefbin, &video_sink]).expect("Unable to link elements");
        Element::link_many(&[&audiomixer, &audio_sink]).expect("Unable to link elements");

        let (output_video_sink_pad, output_audio_sink_pad) = make_output(&pipeline, &config.output);

        video_src
            .static_pad("src")
            .expect("Unable to get video pad")
            .link(&output_video_sink_pad)
            .expect("Unable to link pads");
        audio_src
            .static_pad("src")
            .expect("Unable to get audio pad")
            .link(&output_audio_sink_pad)
            .expect("Unable to link pads");

        {
            let byte_count = Arc::new(AtomicU64::new(0));

            std::thread::spawn({
                let pipeline = pipeline.downgrade();
                let byte_count = byte_count.clone();
                let event_sender = event_sender.clone();

                move || {
                    while let Some(pipeline) = pipeline.upgrade() {
                        let position = pipeline.query_position::<ClockTime>();

                        if let Some(time) = position {
                            let byte_count = byte_count.load(Ordering::Relaxed);
                            let time_position = time.mseconds();

                            if let Err(_) = event_sender.send(StreamEvent::Stats {
                                time_position,
                                byte_count,
                            }) {
                                break;
                            }
                        }

                        std::thread::sleep(Duration::from_secs(5));
                    }

                    debug!("Stats thread finished");
                }
            });

            output_video_sink_pad
                .add_probe(PadProbeType::BUFFER, {
                    let byte_count = byte_count.clone();

                    move |_pad, info| {
                        if let Some(PadProbeData::Buffer(buffer)) = &info.data {
                            byte_count.fetch_add(buffer.size() as u64, Ordering::Relaxed);
                        }

                        PadProbeReturn::Ok
                    }
                })
                .expect("Unable to add probe");

            output_audio_sink_pad
                .add_probe(PadProbeType::BUFFER, {
                    let byte_count = byte_count.clone();

                    move |_pad, info| {
                        if let Some(PadProbeData::Buffer(buffer)) = &info.data {
                            byte_count.fetch_add(buffer.size() as u64, Ordering::Relaxed);
                        }

                        PadProbeReturn::Ok
                    }
                })
                .expect("Unable to add probe");
        }

        pipeline
            .bus()
            .expect("Unable to get Pipeline Bus")
            .set_sync_handler({
                let event_sender = event_sender.clone();

                move |_bus, message| {
                    match message.view() {
                        MessageView::Warning(warning) => {
                            warn!("{:?}", warning.debug())
                        }
                        MessageView::Error(err) => {
                            let error_message = format!("{:?}", err.error());
                            let debug_message = format!("{:?}", err.debug());
                            warn!(
                                "Streaming failed: error={}, debug={}",
                                error_message, debug_message
                            );

                            if debug_message.contains("publish failed") {
                                let _ = event_sender.send(StreamEvent::Error(Error::PublishDenied));
                            } else {
                                let _ = event_sender.send(StreamEvent::Error(Error::Other));
                            }
                        }
                        _ => {}
                    };
                    BusSyncReply::Drop
                }
            });

        pipeline
            .set_state(State::Playing)
            .expect("Unable to start pipeline");

        Ok(Self { pipeline })
    }
}
