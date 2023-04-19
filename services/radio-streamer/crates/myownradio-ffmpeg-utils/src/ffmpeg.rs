use ffmpeg_next::format::sample::Type;
use ffmpeg_next::{decoder, filter, format, ChannelLayout, Error};

pub(crate) fn setup_resampling_filter(
    sample_rate: u32,
    decoder: &decoder::Audio,
) -> Result<filter::Graph, Error> {
    let mut filter = filter::Graph::new();
    let input_spec = format!(
        "time_base={}:sample_rate={}:sample_fmt={}:channel_layout=0x{:x}",
        decoder.time_base(),
        decoder.rate(),
        decoder.format().name(),
        decoder.channel_layout().bits()
    );

    let filter_spec = format!("aresample={}", sample_rate);

    filter.add(&filter::find("abuffer").unwrap(), "in", &input_spec)?;
    filter.add(&filter::find("abuffersink").unwrap(), "out", "")?;

    {
        let mut out = filter.get("out").unwrap();

        out.set_sample_format(format::Sample::I16(Type::Packed));
        out.set_channel_layout(ChannelLayout::STEREO);
        out.set_sample_rate(sample_rate);
    }

    filter
        .output("in", 0)?
        .input("out", 0)?
        .parse(&filter_spec)?;
    filter.validate()?;

    Ok(filter)
}
