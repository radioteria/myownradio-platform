const SESSION_COOKIE_NAME = 'secure_session'

export const isomorphicFetch = (
  input: RequestInfo | URL,
  init?: RequestInit,
): Promise<Response> => {
  const isServer = typeof window === 'undefined'

  if (isServer) {
    const sessionCookie = require('next/headers').cookies().get(SESSION_COOKIE_NAME)

    if (sessionCookie) {
      const headers = {
        Cookie: `${sessionCookie.name}=${sessionCookie.value}`,
        ...(init?.headers ?? {}),
      }
      return fetch(input, {
        credentials: 'include',
        cache: 'no-store',
        headers,
        ...(init ?? {}),
      })
    }
  }

  return fetch(input, init)
}
