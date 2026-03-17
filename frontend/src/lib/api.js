const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || '/api').replace(/\/$/, '')

async function request(method, path, body) {
  const res = await fetch(`${API_BASE_URL}${path}`, {
    method,
    headers: {
      Accept: 'application/json',
      ...(body ? { 'Content-Type': 'application/json' } : {}),
    },
    body: body ? JSON.stringify(body) : undefined,
  })

  const text = await res.text()
  const json = text ? JSON.parse(text) : null

  if (!res.ok) {
    const message = json?.message || `Request failed (${res.status})`
    throw new Error(message)
  }

  return json?.data ?? json
}

export function apiGet(path) {
  return request('GET', path)
}

export function apiPost(path, body) {
  return request('POST', path, body)
}

export function apiPatch(path, body) {
  return request('PATCH', path, body)
}
