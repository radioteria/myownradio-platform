IMAGE=myownradio/ss-container

build:
	docker build --rm=false -t ${IMAGE} .

start:
	docker run -it -v $(shell pwd)/log:/opt/stream-server/log -p 7778:7778 --rm ${IMAGE}

pull:
	docker pull ${IMAGE}
