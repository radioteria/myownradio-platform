FROM myownradio/ss-container

RUN mkdir -p /opt/ss

ADD src/ manifest/ pom.xml /opt/ss/

WORKDIR /opt/ss/

RUN mvn clean install

CMD mvn exec:exec
