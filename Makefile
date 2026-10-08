
all:
	@if ! docker image inspect camagru-web >/dev/null 2>&1; then \
		echo "Image camagru-web non construite."; \
		echo "Lancement du build..."; \
		$(MAKE) build; \
	fi
	docker compose up -d
	@echo "http://localhost:8080"

build:
	docker compose build

down:
	docker compose down

re:
	docker compose down
	docker compose build
	docker compose up -d
	@echo "http://localhost:8080"

.PHONY: all build down re