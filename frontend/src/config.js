const rawMaxGoals = Number(import.meta.env.VITE_MAX_GOALS_PER_TEAM)

export const MAX_GOALS_PER_TEAM = Number.isFinite(rawMaxGoals) && rawMaxGoals > 0 ? rawMaxGoals : 20

