use crate::VERSION;
use prometheus::{Encoder, Gauge, Opts, Registry, TextEncoder};
use std::collections::HashMap;

#[derive(Clone)]
pub struct Metrics {
    streaming_in_progress: Gauge,
    track_start_lateness: Gauge,
    prometheus_registry: Registry,
}

impl Metrics {
    pub fn new() -> Self {
        let streaming_in_progress = Gauge::with_opts(Opts::new(
            "streaming_in_progress",
            "Number of streaming currently in progress",
        ))
        .unwrap();

        let track_start_lateness = Gauge::with_opts(Opts::new(
            "track_start_lateness",
            "How late is the next track",
        ))
        .unwrap();

        let prometheus_registry = Registry::new_custom(
            Some("radio_streamer".to_string()),
            Some({
                let mut labels = HashMap::new();
                labels.insert("server_version".to_string(), VERSION.to_string());
                labels
            }),
        )
        .unwrap();

        #[cfg(target_os = "linux")]
        {
            prometheus_registry
                .register(Box::new(
                    prometheus::process_collector::ProcessCollector::for_self(),
                ))
                .unwrap();
        }

        prometheus_registry
            .register(Box::new(streaming_in_progress.clone()))
            .unwrap();
        prometheus_registry
            .register(Box::new(track_start_lateness.clone()))
            .unwrap();

        Self {
            streaming_in_progress,
            track_start_lateness,
            prometheus_registry,
        }
    }

    pub fn inc_streaming_in_progress(&self) {
        self.streaming_in_progress.inc()
    }

    pub fn dec_streaming_in_progress(&self) {
        self.streaming_in_progress.dec()
    }

    pub fn set_track_start_lateness(&self, delay: &usize) {
        self.track_start_lateness.set(delay.clone() as f64);
    }

    pub fn gather(&self) -> Vec<u8> {
        let mut buffer = vec![];
        let encoder = TextEncoder::new();
        let metric_families = self.prometheus_registry.gather();
        encoder.encode(&metric_families, &mut buffer).unwrap();
        buffer
    }
}
