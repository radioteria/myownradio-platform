IMAGE_ID="myownradio/stream-server"

clean:
	mvn clean

install:
	mvn install

build:
	docker build -t $(IMAGE_ID) .

deploy: install build
	docker push $(IMAGE_ID)

serve: install build
	docker run --rm -it -p 7778:7778 $(IMAGE_ID)

clean-deploy: clean deploy

clean-build: clean build
