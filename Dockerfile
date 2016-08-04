FROM myownradio/ss-container

RUN mkdir -p /opt/stream-server/build

COPY . /opt/stream-server/build/

WORKDIR /opt/stream-server/build/

RUN mvn clean package && \
    mv target/stream-server.jar ..

WORKDIR /opt/stream-server/

RUN rm -rf build && \
    rm -rf ~/.m2/*

COPY server.properties .

ENV MOR_CONFIG_FILE=/opt/stream-server/server.properties

CMD java -jar stream-server.jar
