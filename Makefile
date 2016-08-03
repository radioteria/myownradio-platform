build:
	docker build -t myownradio/ss .

start:
	docker run -it --rm myownradio/ss
