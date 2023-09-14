import { useEffect, useState } from 'react'

interface Props {
  readonly children: React.ReactNode
  readonly placeholder?: React.ReactNode
}

export const ClientSide: React.FC<Props> = ({ children, placeholder = null }) => {
  const [client, setClient] = useState(false)

  useEffect(() => {
    setClient(true)
  }, [])

  if (!client) {
    return placeholder
  }

  return children
}
