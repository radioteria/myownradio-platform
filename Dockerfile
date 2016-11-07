FROM myownradio/streamer-image

EXPOSE 7778

WORKDIR /home/mor

COPY target/stream-server.jar .
COPY server.properties .

RUN chown -R mor:mor . && \
	mkdir /var/log/stream-server && chown -R mor:mor /var/log/stream-server

VOLUME ["/var/log/stream-server"]

ENV MOR_CONFIG_FILE=/home/mor/server.properties

CMD ["java", "-jar", "stream-server.jar"]

USER mor
