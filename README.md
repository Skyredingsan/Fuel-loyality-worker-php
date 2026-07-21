# Fuel Points — Топливный Альянс

Программа лояльности и геймификации для территориальных менеджеров автозаправочных комплексов и станций.

---

## 📋 Оглавление

- [Стек технологий](#-стек-технологий)
- [Быстрый старт](#-быстрый-старт)
- [Демо-доступ](#-демо-доступ)
- [Лицензия](#-лицензия)

---

## 🛠 Стек технологий

| Компонент | Технология |
|-----------|------------|
| **Backend** | Laravel 13 |
| **Язык** | PHP 8.5 |
| **База данных** | PostgreSQL 17 |
| **Frontend** | React 18 |
| **Архитектура** | Domain-Driven Design (DDD) |
| **Контейнеризация** | Docker |

---

## 🚀 Быстрый старт

### 1️⃣ Клонирование репозитория

```bash
git clone <repo-url>
cd fuel-points
```

### 2️⃣ Настройка окружения

```bash
cp .env.example .env
```

### 3️⃣ Запуск контейнеров

```bash
docker compose up -d --build
```

### 4️⃣ Установка зависимостей

```bash
docker compose exec app composer install
cd frontend && npm install && npm run build && cd ..
```

### 5️⃣ Генерация ключей

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
```

### 6️⃣ Миграции и заполнение демо-данными

```bash
docker compose exec app php artisan migrate --seed
```

---

## 🔐 Демо-доступ

После выполнения команды `migrate --seed` доступны следующие аккаунты:

| Email | Пароль | Роль |
|-------|--------|------|
| `coordinator@demo.fuel` | `DemoCoord#2026` | Координатор |
| `expert@demo.fuel` | `DemoExpert#2026` | Эксперт |
| `tm@demo.fuel` | `DemoTM#2026` | Территориальный менеджер |


##  Лицензия

Проект распространяется под лицензией **Apache 2.0**. Подробности в файле `LICENSE`.