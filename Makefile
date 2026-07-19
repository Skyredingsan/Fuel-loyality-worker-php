# Default shell внутри make
SHELL := /bin/bash

# Цвета для вывода
CYAN   := \033[36m
GREEN  := \033[32m
YELLOW := \033[33m
RED    := \033[31m
RESET  := \033[0m

# Префикс для docker compose
DC := docker compose
APP := $(DC) exec app

.PHONY: help install up down restart build logs ps \
        artisan tinker \
        migrate migrate-fresh migrate-rollback seed \
        test test-unit test-feature test-coverage \
        composer pint pint-fix \
        db-shell db-drop \
        clean

# ─── По умолчанию показываем help ───────────────────────────────
.DEFAULT_GOAL := help

## ─── Установка и запуск ────────────────────────────────────────

help: ## Показать список всех команд
	@echo ""
	@echo "$(CYAN)Fuel Points — доступные команды:$(RESET)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
        | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-22s$(RESET) %s\n", $$1, $$2}'
	@echo ""

install: ## Первичная установка: копирует .env, ставит зависимости, генерирует ключ
	@if [ ! -f .env ]; then cp .env.example .env; echo "$(GREEN)✓$(RESET) .env создан из .env.example"; fi
	$(DC) up -d --build
	@echo "$(YELLOW)Ждём 5 сек, пока поднимется Postgres...$(RESET)"
	@sleep 5
	$(APP) composer install
	$(APP) php artisan key:generate
	@echo ""
	@echo "$(GREEN)✓ Установка завершена. Проверь:$(RESET) curl http://localhost:8080/api/health"

up: ## Поднять контейнеры в фоне
	$(DC) up -d
	@echo "$(GREEN)✓ Контейнеры запущены$(RESET)"

down: ## Остановить контейнеры
	$(DC) down
	@echo "$(YELLOW)✓ Контейнеры остановлены$(RESET)"

restart: ## Перезапустить контейнеры
	$(DC) restart
	@echo "$(GREEN)✓ Контейнеры перезапущены$(RESET)"

build: ## Пересобрать образы
	$(DC) build

logs: ## Логи всех контейнеров (tail -f)
	$(DC) logs -f

logs-app: ## Логи только app-контейнера
	$(DC) logs -f app

logs-nginx: ## Логи только nginx
	$(DC) logs -f nginx

logs-pg: ## Логи только PostgreSQL
	$(DC) logs -f postgres

ps: ## Список запущенных контейнеров
	$(DC) ps

## ─── Artisan команды ──────────────────────────────────────────

artisan: ## Выполнить artisan команду: make artisan args="migrate"
	$(APP) php artisan $(args)

tinker: ## Запустить Laravel Tinker REPL
	$(APP) php artisan tinker

route-list: ## Список маршрутов
	$(APP) php artisan route:list

## ─── Миграции и сиды ───────────────────────────────────────────

migrate: ## Применить миграции
	$(APP) php artisan migrate
	@echo "$(GREEN)✓ Миграции применены$(RESET)"

migrate-fresh: ## Пересоздать БД и применить миграции с сидами
	$(APP) php artisan migrate:fresh --seed
	@echo "$(GREEN)✓ БД пересоздана, сиды загружены$(RESET)"

migrate-rollback: ## Откатить последнюю миграцию
	$(APP) php artisan migrate:rollback

seed: ## Запустить только сиды (без миграций)
	$(APP) php artisan db:seed

## ─── Тесты ────────────────────────────────────────────────────

test: ## Запустить все тесты
	$(APP) php artisan test

test-unit: ## Только unit-тесты
	$(APP) php artisan test --testsuite=Unit

test-feature: ## Только feature-тесты
	$(APP) php artisan test --testsuite=Feature

test-coverage: ## Тесты с покрытием кода (HTML-отчёт)
	$(APP) php artisan test --coverage-html=coverage

## ─── Качество кода ────────────────────────────────────────────

composer: ## Запустить composer: make composer args="require package/name"
	$(APP) composer $(args)

pint: ## Проверить стиль кода (Laravel Pint, без изменений)
	$(APP) vendor/bin/pint --test

pint-fix: ## Исправить стиль кода (Laravel Pint)
	$(APP) vendor/bin/pint

## ─── База данных ──────────────────────────────────────────────

db-shell: ## Открыть psql-клиент PostgreSQL
	$(DC) exec postgres psql -U fuel -d fuel_points

db-drop: ## ⚠️ ОСТОРОЖНО: удалить ВСЕ данные из БД
	$(APP) php artisan db:wipe
	@echo "$(RED)⚠ БД очищена!$(RESET)"

## ─── Очистка ──────────────────────────────────────────────────

clean: ## Удалить контейнеры, тома и кеши (⚠ удаляет данные БД!)
	$(DC) down -v
	rm -rf vendor storage/logs/*.log bootstrap/cache/*.php
	@echo "$(RED)⚠ Всё очищено. Запусти 'make install' заново$(RESET)"