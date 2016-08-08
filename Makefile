IMAGE_ID=myownradio/ss-container
CONTAINER_ID=streamserver

install:
	mkdir -p log
	docker build --rm=false -t $(IMAGE_ID) .

start:
	docker run -d \
		--name $(CONTAINER_ID) \
		-v $(CURDIR)/log:/opt/stream-server/log \
		-p 7778:7778 \
		$(IMAGE_ID)

last:
	docker logs $(CONTAINER_ID)

stop:
	docker stop $(CONTAINER_ID)
	docker rm $(CONTAINER_ID)

pull:
	docker pull $(IMAGE)
