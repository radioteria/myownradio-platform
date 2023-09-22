import { useEffect, useState } from 'react'

export enum ClientServer {
  Client = 'Client',
  Server = 'Server',
}

export const useClientServer = (): ClientServer => {
  const [client, setClient] = useState(ClientServer.Server)

  useEffect(() => {
    setClient(ClientServer.Client)
  }, [])

  return client
}
