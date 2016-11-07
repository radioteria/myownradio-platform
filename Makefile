IMAGE_ID="myownradio/stream-server"

clean:
	mvn clean

install:
	mvn install

build:
	docker build -t $(IMAGE_ID) .

deploy:
	docker push $(IMAGE_ID)

serve:
	docker run --rm -it -p 7778:7778 $(IMAGE_ID)

clean-deploy: clean install build deploy

clean-build: clean install build
