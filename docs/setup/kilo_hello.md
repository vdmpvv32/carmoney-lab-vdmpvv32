# kilo_hello

1) Учебный сервис предварительной оценки заявки на заём под ПТС: принимает заявку (VIN, год, пробег, оценочная стоимость, сумма, срок), считает LTV и возвращает решение approve / review / reject (PHP 8.3 + Slim, данные синтетические).
2) Makefile: `make up`, `make down`, `make test`, `make lint`, `make seed`, `make logs`, `make ps`, `make install`, `make help`; docker-compose.yml поднимает `backend` (PHP-Slim на 8080) и `db` (MySQL 8, healthcheck `mysqladmin ping`).
3) В `backend/src/Domain/` — правила расчёта и решения: `LtvCalculator.php`, `DecisionEngine.php`, `AssessmentService.php`, `ApplicationValidator.php`, `VinValidator.php`, `VehicleAge.php` (HTTP-слой в `backend/src/Http/`, пороги и лимиты в `backend/config/rules.php`).

модель: training-2026-09-minimax-m3