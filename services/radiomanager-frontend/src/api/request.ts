import z from 'zod'

export class Request {
  private readonly url: URL
  private readonly headers: [string, string][] = []

  private method = 'GET'
  private signal: AbortSignal | null = null
  private body: BodyInit | null = null

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

  public fetch(): Promise<Response> {
    return fetch(this.url, {
      method: this.method,
      signal: this.signal,
      body: this.body,
      headers: this.headers,
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
