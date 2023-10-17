import z from 'zod'
import makeDebug from 'debug'

const debug = makeDebug('fetchAnyhow')

const SESSION_COOKIE_NAME = 'secure_session'

interface InitParams {
  method?: string
  searchParams?: [string, string][]
  headers?: [string, string][]
  signal?: AbortSignal
  body?: BodyInit
  withCredentials?: boolean
}

export async function fetchAnyhow(url: string, initParams: InitParams = {}): Promise<Response> {
  const urlObject = new URL(url)
  const {
    method = 'GET',
    searchParams = [],
    headers = [],
    signal,
    body,
    withCredentials = false,
  } = initParams

  for (const [key, value] of searchParams ?? []) {
    urlObject.searchParams.set(key, value)
  }

  const isServer = typeof window === 'undefined'

  if (isServer && withCredentials) {
    const sessionCookie = require('next/headers').cookies().get(SESSION_COOKIE_NAME)

    if (sessionCookie) {
      debug('Adding Cookie header to the request')
      headers.push(['Cookie', `${sessionCookie.name}=${sessionCookie.value}`])
    }
  }

  if (!isServer && withCredentials) {
    const token = new URL(window.location.href).searchParams.get('token')

    if (token) {
      debug('Adding Authentication header to the request')
      headers.push(['Authentication', `Bearer ${token}`])
    }
  }

  const credentials = withCredentials ? 'include' : undefined

  return fetch(urlObject.toString(), { credentials, method, signal, headers, body })
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

export async function fetchAnyhowWithDataSchema<T>(
  url: string,
  initParams: InitParams = {},
  schema: z.ZodType<{ data: T }, any, unknown>,
): Promise<T> {
  return fetchAnyhow(url, initParams)
    .then((response) => response.json())
    .then((json) => schema.parse(json))
    .then(({ data }) => data)
}
