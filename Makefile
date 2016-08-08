IMAGE_ID=myownradio/ss-container
CONTAINER_ID=streamserver

install:
	@ mkdir -p log; \
	  docker build -t $(IMAGE_ID) --rm=true .

start:
ifeq ([], $(shell docker inspect $(IMAGE_ID) 2> /dev/null))
	@ echo "Please, run 'make install' before 'make start'" >&2; exit 1;
else
	@ docker run -d \
		--name $(CONTAINER_ID) \
		-v $(CURDIR)/log:/opt/stream-server/log \
		-p 7778:7778 \
		$(IMAGE_ID)
endif

status:
	@ docker logs $(CONTAINER_ID)

follow:
	@ docker logs -f $(CONTAINER_ID)

stop:
	@ docker stop $(CONTAINER_ID) 2>&1 >/dev/null; \
	  docker rm $(CONTAINER_ID) 2>&1 >/dev/null; \
	  echo ""

deinstall: stop
	@ rm -rf log; \
	  docker rmi --force $(IMAGE_ID) 2>&1 >/dev/null; \
	  echo ""
