all:
	@if ! docker image inspect camagru >/dev/null 2>&1; then \
		echo "Image camagru non construite."; \
		echo "Lancement du build..."; \
		$(MAKE) build; \
	fi
	docker compose up -d
	@echo "http://localhost:8080"

build:
	docker compose build

front:
	@echo "Mise à jour du front..."
	docker compose build web
	docker compose up -d --no-deps web
	@echo "Front mis à jour."

back:
	@echo "Mise à jour du back..."
	docker compose build web
	docker compose up -d --no-deps web
	@echo "Back mis à jour."

re:
	docker compose down
	docker compose build
	docker compose up -d
	@echo "http://localhost:8080"

down:
	docker compose down

reset:
	@echo "Réinitialisation de la database."
	docker compose down -v
	docker compose up -d
	@echo "La base de données a été réinitialisée."

.PHONY: all build front back re down reset