import { fetch } from 'next/dist/compiled/@edge-runtime/primitives'
import z from 'zod'
import { shape } from 'prop-types'

const SESSION_COOKIE_NAME = 'secure_session'

interface InitParams {
  searchParams?: [string, string][]
  headers?: [string, string][]
  signal?: AbortSignal
  body?: BodyInit
  withCredentials?: boolean
}

export async function fetchAnyhow(url: string, initParams: InitParams = {}): Promise<Response> {
  const urlObject = new URL(url)
  const { searchParams = [], headers = [], signal, body, withCredentials = false } = initParams

  for (const [key, value] of searchParams ?? []) {
    urlObject.searchParams.set(key, value)
  }

  const isServer = typeof window === 'undefined'

  if (isServer && withCredentials) {
    const sessionCookie = require('next/headers').cookies().get(SESSION_COOKIE_NAME)

    if (sessionCookie) {
      headers.push(['Cookie', `${sessionCookie.name}=${sessionCookie.value}`])
    }

    return fetch(urlObject.toString(), {
      credentials: 'include',
      signal,
      headers,
      body,
    })
  }

  if (!isServer && withCredentials) {
    const token = new URL(window.location.href).searchParams.get('token')

    if (token) {
      headers.push(['Authentication', `Bearer ${token}`])
    }
  }

  return fetch(urlObject.toString(), {
    credentials: withCredentials ? 'include' : undefined,
    signal,
    headers,
    body,
  })
}

export async function fetchAnyhowWithSchema<T>(
  url: string,
  initParams: InitParams = {},
  schema: z.ZodType<T, any, unknown>,
) {
  return fetchAnyhow(url, initParams)
    .then((response) => response.json())
    .then((json) => schema.parse(json))
}
