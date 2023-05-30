use crate::VERSION;
use actix_web::http::{Method, StatusCode};
use prometheus::{Encoder, Gauge, HistogramVec, IntCounterVec, Opts, Registry, TextEncoder};
use std::collections::HashMap;
use std::time::Duration;

#[derive(Clone)]
pub struct Metrics {
    active_player_loops: Gauge,
    prometheus_registry: Registry,
    http_requests_total: IntCounterVec,
    http_requests_duration_seconds: HistogramVec,
}

impl Metrics {
    pub fn new() -> Self {
        let active_player_loops = Gauge::with_opts(Opts::new(
            "active_player_loops",
            "Number of active player loops",
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
            .register(Box::new(active_player_loops.clone()))
            .unwrap();
        prometheus_registry
            .register(Box::new(http_requests_total.clone()))
            .unwrap();
        prometheus_registry
            .register(Box::new(http_requests_duration_seconds.clone()))
            .unwrap();

        Self {
            active_player_loops,
            prometheus_registry,
            http_requests_total,
            http_requests_duration_seconds,
        }
    }

    pub fn inc_active_player_loops(&self) {
        self.active_player_loops.inc()
    }

    pub fn dec_active_player_loops(&self) {
        self.active_player_loops.dec()
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
