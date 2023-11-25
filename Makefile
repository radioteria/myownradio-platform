USER := $(shell id -u):$(shell id -g)

compose:
	mkdir -p services/backend/.cache/storage
	mkdir -p services/radio-streamer/.cargo-cache/git
	mkdir -p services/radio-streamer/.cargo-cache/registry
	mkdir -p services/radio-streamer/target
	mkdir -p services/radio-manager-backend/.cargo-cache/git
	mkdir -p services/radio-manager-backend/.cargo-cache/registry
	mkdir -p services/radio-manager-backend/target
	USER=$(USER) docker-compose up

clean:
	rm -rf services/backend/.cache/storage
	rm -rf services/radio-streamer/.cargo-cache/git
	rm -rf services/radio-streamer/.cargo-cache/registry
	rm -rf services/radio-streamer/target
	rm -rf services/radio-manager-backend/.cargo-cache/git
	rm -rf services/radio-manager-backend/.cargo-cache/registry
	rm -rf services/radio-manager-backend/target
	USER=$(USER) docker-compose down --volumes --remove-orphans --rmi all
