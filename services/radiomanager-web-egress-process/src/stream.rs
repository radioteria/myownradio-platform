use crate::gstreamer_utils::make_element;
use crate::stream_utils::{make_audio_encoder, make_output, make_video_encoder};
use gstreamer::prelude::*;
use gstreamer::{
    Bin, BusSyncReply, Element, MessageView, PadProbeData, PadProbeReturn, PadProbeType, Pipeline,
    State,
};
use std::sync::mpsc::Sender;
use tracing::{info, trace, warn};

#[derive(Debug, thiserror::Error)]
pub(crate) enum CreateStreamError {}

#[derive(Debug)]
pub(crate) enum Error {
    PublishDenied,
    Other,
}

pub(crate) enum StreamEvent {
    Error(Error),
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
    pub(crate) video_encoder: VideoEncoder,
    pub(crate) audio_bitrate: u32,
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
        webpage_url: String,
        config: &StreamConfig,
        event_sender: Sender<StreamEvent>,
    ) -> Result<Stream, CreateStreamError> {
        let pipeline = Pipeline::new(None);

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
            &config.video_encoder,
        );
        let (audio_sink, audio_src) = make_audio_encoder(&pipeline, config.audio_bitrate);

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

        output_video_sink_pad
            .add_probe(PadProbeType::BUFFER, |_pad, info| {
                if let Some(PadProbeData::Buffer(buffer)) = &info.data {
                    if let Some(pts) = buffer.pts() {
                        trace!("Buffer pts={}", pts);
                    }
                }

                PadProbeReturn::Pass
            })
            .expect("Unable to add probe");

        pipeline
            .bus()
            .expect("Unable to get Pipeline Bus")
            .set_sync_handler({
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
