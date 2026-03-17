# League Simulator — Case Project

This repository contains a **Laravel (PHP)** backend and a **Vue (Vite)** frontend that simulate a 4‑team league, week by week, with Premier League scoring rules and late-stage championship predictions.

## Project Structure

- `backend/` — Laravel API + simulation domain/services + tests
- `frontend/` — Vue UI (component-based) consuming the API

## Assumptions / Rules

- **Teams**: 4 teams (seeded by default)
- **Format**: Double round-robin (home & away)
  - Total matches: 12
  - Weekly matches: 2
  - Total weeks: 6
- **Scoring**: Win = 3, Draw = 1, Loss = 0
- **Table order**: Points → Goal Difference → Goals For
- **Predictions**: Calculated when entering the **last 3 weeks** (Week 4+ for a 6-week league), using **Monte Carlo**

## Setup

### Backend (Laravel)

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Backend runs on `http://127.0.0.1:8000`.

### Frontend (Vue)

```bash
cd frontend
npm install
npm run dev
```

Frontend runs on `http://127.0.0.1:5174` (or the next available port). Dev server proxies `/api` to the backend to avoid CORS issues.

## Running Tests

```bash
cd backend
php artisan test
```

## Production-like Run (Single URL)

Build the SPA into the Laravel public directory and serve it via Laravel:

```bash
cd frontend
npm run build

cd ../backend
php artisan serve
```

Open `http://127.0.0.1:8000` (Laravel serves the SPA; API remains under `/api`).

## Deploy (Render)

This repo includes a root `Dockerfile` that builds the Vue SPA and serves it from Laravel.

1. Push the repository to GitHub.
2. In Render, create a **New → Web Service** and pick your GitHub repo.
3. Choose **Environment: Docker**.
4. Add these environment variables:
   - `APP_KEY` (generate locally with `cd backend && php artisan key:generate --show`)
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=<your Render service URL>` (optional but recommended)
5. Deploy. The container auto-runs `php artisan migrate --seed` on startup and listens on Render’s `$PORT`.

## API Endpoints (MVP)

- `GET /api/teams`
- `POST /api/fixtures/generate`
- `GET /api/fixtures`
- `GET /api/simulation/state`
- `POST /api/simulation/play-next-week`
- `POST /api/simulation/play-all-weeks`
- `POST /api/simulation/reset`
- `PATCH /api/matches/{id}` — update match result (`homeGoals`, `awayGoals`)

## Tuning (Env)

Backend `.env`:

- `LEAGUE_MAX_GOALS_PER_TEAM` (default `20`)
- `LEAGUE_PREDICTION_LAST_WEEKS` (default `3`)
- `LEAGUE_PREDICTION_SIMULATIONS` (default `10000`)
