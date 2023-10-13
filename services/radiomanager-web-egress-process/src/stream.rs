use crate::gstreamer_utils::make_element;
use crate::stream_utils::{make_aac_encoder, make_h264_encoder, make_output};
use gstreamer::prelude::*;
use gstreamer::{Bin, Element, PadProbeData, PadProbeReturn, PadProbeType, Pipeline, State};
use tracing::trace;

#[derive(Debug, thiserror::Error)]
pub(crate) enum CreateStreamError {}

pub(crate) enum StreamOutput {
    RTMP { url: String, stream_key: String },
}

pub(crate) struct StreamConfig {
    pub(crate) output: StreamOutput,
    pub(crate) video_width: u32,
    pub(crate) video_height: u32,
    pub(crate) video_bitrate: u32,
    pub(crate) video_framerate: u32,
    pub(crate) audio_bitrate: u32,
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

        pipeline
            .add_many(&[&cefbin, &audiomixer])
            .expect("Unable to add elements to pipeline");

        cefbin.link(&audiomixer).unwrap();

        let (video_sink, video_src) = make_h264_encoder(
            &pipeline,
            config.video_width,
            config.video_height,
            config.video_bitrate,
            config.video_framerate,
        );
        let (audio_sink, audio_src) = make_aac_encoder(&pipeline, config.audio_bitrate);

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
            .set_state(State::Playing)
            .expect("Unable to start pipeline");

        Ok(Self { pipeline })
    }
}
