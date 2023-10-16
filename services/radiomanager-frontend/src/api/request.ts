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
