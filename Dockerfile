FROM myownradio/streamer-image

EXPOSE 7778

WORKDIR /opt/stream-server/build/

COPY src src
COPY pom.xml .
COPY server.properties .

RUN mvn package

WORKDIR /opt/stream-server/build/target/

RUN mv -v stream-server.jar ../../

WORKDIR /opt/stream-server/

RUN rm -rf build && \
    rm -rf ~/.m2/*

COPY server.properties .

ENV MOR_CONFIG_FILE=/opt/stream-server/server.properties

ENTRYPOINT java -jar stream-server.jar
