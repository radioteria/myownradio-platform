use crate::VERSION;
use actix_web::http::{Method, StatusCode};
use prometheus::{Encoder, Gauge, HistogramVec, IntCounterVec, Opts, Registry, TextEncoder};
use std::collections::HashMap;
use std::time::Duration;

#[derive(Clone)]
pub struct Metrics {
    spawned_decoder_processes: Gauge,
    spawned_encoder_processes: Gauge,
    player_loops_active: Gauge,
    streams_in_progress: Gauge,
    prometheus_registry: Registry,
    http_requests_total: IntCounterVec,
    http_requests_duration_seconds: HistogramVec,
}

impl Metrics {
    pub fn new() -> Self {
        let spawned_decoder_processes = Gauge::with_opts(Opts::new(
            "spawned_decoder_processes",
            "Number of spawned decoder processes",
        ))
        .unwrap();

        let spawned_encoder_processes = Gauge::with_opts(Opts::new(
            "spawned_encoder_processes",
            "Number of spawned encoder processes",
        ))
        .unwrap();

        let player_loops_active = Gauge::with_opts(Opts::new(
            "player_loops_active",
            "Number of started player loops",
        ))
        .unwrap();

        let streaming_in_progress = Gauge::with_opts(Opts::new(
            "streaming_in_progress",
            "Number of streaming currently in progress",
        ))
        .unwrap();

        let http_requests_total = IntCounterVec::new(
            Opts::new("http_requests_total", "Total number of HTTP requests"),
            &["endpoint", "method", "status"],
        )
        .unwrap();

        let http_requests_duration_seconds = HistogramVec::new(
            Opts::new(
                "http_requests_duration_seconds",
                "HTTP request duration in seconds for all requests",
            )
            .into(),
            &["endpoint", "method", "status"],
        )
        .unwrap();

        let prometheus_registry = Registry::new_custom(
            Some("myownradio_radio_streamer".to_string()),
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
            .register(Box::new(spawned_decoder_processes.clone()))
            .unwrap();
        prometheus_registry
            .register(Box::new(spawned_encoder_processes.clone()))
            .unwrap();
        prometheus_registry
            .register(Box::new(player_loops_active.clone()))
            .unwrap();
        prometheus_registry
            .register(Box::new(streaming_in_progress.clone()))
            .unwrap();
        prometheus_registry
            .register(Box::new(http_requests_total.clone()))
            .unwrap();
        prometheus_registry
            .register(Box::new(http_requests_duration_seconds.clone()))
            .unwrap();

        Self {
            spawned_decoder_processes,
            spawned_encoder_processes,
            player_loops_active,
            streams_in_progress: streaming_in_progress,
            prometheus_registry,
            http_requests_total,
            http_requests_duration_seconds,
        }
    }

    pub fn inc_spawned_decoder_processes(&self) {
        self.spawned_decoder_processes.inc()
    }

    pub fn dec_spawned_decoder_processes(&self) {
        self.spawned_decoder_processes.dec()
    }

    pub fn inc_spawned_encoder_processes(&self) {
        self.spawned_encoder_processes.inc()
    }

    pub fn dec_spawned_encoder_processes(&self) {
        self.spawned_encoder_processes.dec()
    }

    pub fn inc_player_loops_active(&self) {
        self.player_loops_active.inc()
    }

    pub fn dec_player_loops_active(&self) {
        self.player_loops_active.dec()
    }

    pub fn inc_streams_in_progress(&self) {
        self.streams_in_progress.inc()
    }

    pub fn dec_streams_in_progress(&self) {
        self.streams_in_progress.dec()
    }

    pub fn update_http_request_total(
        &self,
        path: &str,
        method: &Method,
        status: StatusCode,
        duration: Duration,
    ) {
        let method = method.to_string();
        let status = status.as_u16().to_string();

        self.http_requests_duration_seconds
            .with_label_values(&[&path, &method, &status])
            .observe(duration.as_secs_f64());

        self.http_requests_total
            .with_label_values(&[&path, &method, &status])
            .inc();
    }

    pub fn gather(&self) -> Vec<u8> {
        let mut buffer = vec![];
        let encoder = TextEncoder::new();
        let metric_families = self.prometheus_registry.gather();
        encoder.encode(&metric_families, &mut buffer).unwrap();
        buffer
    }
}
