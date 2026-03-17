import { apiGet, apiPatch, apiPost } from '../lib/api'

export const leagueApi = {
  getTeams() {
    return apiGet('/teams')
  },
  updateTeamPower(teamId, power) {
    return apiPatch(`/teams/${teamId}`, { power })
  },
  generateFixtures() {
    return apiPost('/fixtures/generate')
  },
  getFixtures() {
    return apiGet('/fixtures')
  },
  getSimulationState() {
    return apiGet('/simulation/state')
  },
  playNextWeek() {
    return apiPost('/simulation/play-next-week')
  },
  playAllWeeks() {
    return apiPost('/simulation/play-all-weeks')
  },
  resetSimulation() {
    return apiPost('/simulation/reset')
  },
  updateMatchResult(matchId, { homeGoals, awayGoals }) {
    return apiPatch(`/matches/${matchId}`, { homeGoals, awayGoals })
  },
}
