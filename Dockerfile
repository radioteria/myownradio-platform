FROM myownradio/ss-container

RUN mkdir -p /opt/stream-server/build

COPY . /opt/stream-server/build/

WORKDIR /opt/stream-server/build/

RUN mvn package

WORKDIR /opt/stream-server/build/target/

RUN mv -v stream-server.jar ../../

WORKDIR /opt/stream-server/

RUN rm -rfv build && \
    rm -rfv ~/.m2/*

COPY server.properties .

ENV MOR_CONFIG_FILE=/opt/stream-server/server.properties

ENTRYPOINT java -jar stream-server.jar
