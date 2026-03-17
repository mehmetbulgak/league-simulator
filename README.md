# League Simulator — Case Project

Live Link: https://league-simulator-gs4s.onrender.com/

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
