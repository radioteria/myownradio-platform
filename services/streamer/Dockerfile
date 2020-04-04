FROM maven:3-jdk-8

MAINTAINER Roman Lakhtadyr <roman.lakhtadyr@gmail.com>

RUN apt-get update && apt-get install -y --no-install-recommends --no-install-suggests ffmpeg && apt-get clean

WORKDIR /usr/app

COPY . ./

RUN mvn package

ENV MOR_CONFIG_FILE=/usr/app/server.properties

CMD ["java", "-jar", "target/stream-server.jar"]

EXPOSE 7778
