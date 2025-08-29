### 🔁 Docker général

up:
	docker-compose up -d --build

down:
	docker-compose down

restart:
	docker-compose down && docker-compose up -d --build

logs:
	docker-compose logs -f

ps:
	docker ps

### 🐘 PostgreSQL

psql:
	docker exec -it postgres psql -U postgres -d bd-app-conges

### 🐘 Laravel (backend)

bash:
	docker exec -it laravel bash

composer:
	docker exec -it laravel composer install

serve:
	docker exec -it laravel php artisan serve 
artisan:
	docker exec -it laravel php artisan

migrate:
	docker exec -it laravel php artisan migrate

seed:
	docker exec -it laravel php artisan db:seed
	
migrate-fresh:
	docker exec -it laravel php artisan migrate:fresh --seed


fresh:
	docker exec -it laravel php artisan migrate:fresh --seed

clear:
	docker exec -it laravel php artisan cache:clear
	docker exec -it laravel php artisan config:clear
	docker exec -it laravel php artisan route:clear
	docker exec -it laravel php artisan view:clear

### 🌐 Vue.js (frontend)

vue-bash:
	docker exec -it vue.js sh

vue-install:
	docker exec -it vue.js pnpm install

vue-dev:
	docker exec -it vue.js pnpm run dev

vue-build:
	docker exec -it vue.js pnpm run build

vue-lint:
	docker exec -it vue.js pnpm run lint

### 🌐 Nginx (reverse proxy)

nginx-bash:
	docker exec -it nginx sh

### 🛠️ pgAdmin

pgadmin-bash:
	docker exec -it pgadmin sh
