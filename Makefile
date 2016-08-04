build:
	docker build -t myownradio/ss .

start:
	docker run -it -v $(shell pwd)/log:/opt/stream-server/log -p 7778:7778 --rm myownradio/ss bash
