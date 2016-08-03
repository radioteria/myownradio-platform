FROM myownradio/ss-container

RUN mkdir -p /opt/ss

ADD pom.xml src/ /opt/ss/

WORKDIR /opt/ss/

RUN mvn clean install

CMD mvn exec:java
