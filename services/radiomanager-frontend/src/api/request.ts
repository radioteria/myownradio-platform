import z from 'zod'
import { isomorphicFetch } from '@/api/isomorphicFetch'

export class Request {
  private readonly url: URL
  private readonly headers: [string, string][] = []

  private method = 'GET'
  private signal: AbortSignal | null = null
  private body: BodyInit | null = null
  private shouldEnableServerSideAuth: boolean = false
  private shouldIncludeCredentials: boolean = false

  constructor(url: string | URL) {
    this.url = typeof url === 'string' ? new URL(url) : url
  }

  public setMethod(method: string): Request {
    this.method = method
    return this
  }

  public setQueryParam(name: string, value: string): Request {
    this.url.searchParams.set(name, value)
    return this
  }

  public addHeader(name: string, value: string): Request {
    this.headers.push([name, value])
    return this
  }

  public setAbortSignal(signal: AbortSignal): Request {
    this.signal = signal
    return this
  }

  public setBody(body: BodyInit): Request {
    this.body = body
    return this
  }

  public withTokenAuth(): Request {
    const token = new URL(window.location.href).searchParams.get('token')

    if (token) {
      this.addHeader('Authentication', `Bearer ${token}`)
    }

    return this
  }

  public withServerSideAuth(): Request {
    this.shouldEnableServerSideAuth = true
    return this
  }

  public withCredentials(): Request {
    this.shouldIncludeCredentials = true
    return this
  }

  public fetch(): Promise<Response> {
    const fetchClient = this.shouldEnableServerSideAuth ? isomorphicFetch : fetch
    const credentials = this.shouldIncludeCredentials ? 'include' : undefined
    const { method, signal, body, headers } = this

    return fetchClient(this.url, {
      credentials,
      method,
      signal,
      body,
      headers,
    })
  }

  public fetchWithSchema<T>(schema: z.ZodType<T, any, unknown>): Promise<T> {
    return this.fetch()
      .then((r) => r.json())
      .then((data) => schema.parse(data))
  }
}

interface RequestParams {
  method?: string
  headers?: [string, string][]
  signal?: AbortSignal
  body?: BodyInit
}

export function makePublicRequest(url: URL | string, params?: RequestParams): Promise<Response> {
  return fetch(url, {
    method: params?.method,
    signal: params?.signal,
    headers: params?.headers,
    body: params?.body,
  })
}

export function makeClientRequest(url: URL | string, params?: RequestParams): Promise<Response> {
  const headers = params?.headers ?? []
  const token = new URL(window.location.href).searchParams.get('token')

  if (token) {
    headers.push(['Authentication', `Bearer ${token}`])
  }

  return fetch(url, {
    credentials: 'include',
    method: params?.method,
    signal: params?.signal,
    headers: params?.headers,
    body: params?.body,
  })
}
