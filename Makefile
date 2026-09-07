# InsuraLab developer commands.
#
# `make` is not required — every target mirrors an `npm run <script>` in the root
# package.json, which is the primary cross-platform path (works on Windows too):
#   npm run setup | start | stop | migrate | seed | test | lint | format | build
#
# Use this Makefile on Unix / CI where `make` is available.

BACKEND  = backend
FRONTEND = frontend

.PHONY: help install start stop migrate seed fresh test lint format format-check build db-up db-down

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN{FS=":.*?## "}{printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

install: ## Install backend + frontend dependencies
	composer --working-dir=$(BACKEND) install
	npm --prefix $(FRONTEND) install

start: ## Run API (:8000) and web (:3000) together
	node scripts/dev.mjs

stop: ## Stop dev servers on :3000 and :8000
	node scripts/stop.mjs

db-up: ## Start the self-contained PostgreSQL (profile: full, port 5434)
	docker compose --profile full up -d

db-down: ## Stop the self-contained PostgreSQL
	docker compose --profile full down

migrate: ## Run database migrations
	php $(BACKEND)/artisan migrate

seed: ## Run database seeders
	php $(BACKEND)/artisan db:seed

fresh: ## Drop everything, re-migrate, and re-seed
	php $(BACKEND)/artisan migrate:fresh --seed

test: ## Run the backend test suite (Pest)
	composer --working-dir=$(BACKEND) test

lint: ## Lint the frontend
	npm --prefix $(FRONTEND) run lint

format: ## Format backend (Pint) and frontend (Prettier)
	composer --working-dir=$(BACKEND) format
	npm --prefix $(FRONTEND) run format

format-check: ## Check formatting without writing
	composer --working-dir=$(BACKEND) format:check
	npm --prefix $(FRONTEND) run format:check

build: ## Production build of the frontend
	npm --prefix $(FRONTEND) run build
