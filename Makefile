IMAGE_ID="myownradio/stream-server"
CONTAINER_ID="streamserver"

install:
	@ mkdir -p log; \
	  docker build -t $(IMAGE_ID) --rm=true .

start:
ifeq ([], $(shell docker inspect $(IMAGE_ID) 2> /dev/null))
	@ echo "Please, run 'make install' before 'make start'" >&2; exit 1;
else
	@ docker run -d \
		--restart=always \
		--name $(CONTAINER_ID) \
		-v $(CURDIR)/log:/opt/stream-server/log \
		-p 7778:7778 \
		$(IMAGE_ID)
endif

status:
	@ docker logs $(CONTAINER_ID)

follow:
	@ docker logs -f $(CONTAINER_ID)

attach:
	@ docker exec -it $(CONTAINER_ID) bash

stop:
	@ docker stop $(CONTAINER_ID) 2>&1 >/dev/null; \
	  docker rm $(CONTAINER_ID) 2>&1 >/dev/null; \
	  echo ""

deploy:
	@ docker push $(IMAGE_ID)

uninstall: stop
	@ rm -rf log; \
	  docker rmi --force $(IMAGE_ID) 2>&1 >/dev/null; \
	  echo ""

rebuild: stop pull build start

