package biz.myownradio;

import io.prometheus.client.Counter;
import io.prometheus.client.Gauge;


public class Metrics {
    private static final String prefix = "myownradio_streamer_";

    public static final Counter httpRequests = Counter.build()
            .name(prefix + "http_requests")
            .help("Number of requests to http server")
            .labelNames("path", "status_code")
            .register();

    public static final Gauge activeStreams = Gauge.build()
            .name(prefix + "active_streams")
            .help("Number of active audio streams")
            .labelNames("audio_format")
            .register();
}
