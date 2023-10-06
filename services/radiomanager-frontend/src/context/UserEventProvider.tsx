import React, { createContext, useContext, useEffect, useMemo } from 'react'
import { UserEventSource } from '@/api/pubsub'

const UserEventContext = createContext<UserEventSource | null>(null)

export const UserEventProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const eventSource = useMemo(() => new UserEventSource(), [])

  useEffect(() => {
    eventSource.connect()

    return () => {
      eventSource.disconnect()
    }
  }, [eventSource])

  return <UserEventContext.Provider value={eventSource}>{children}</UserEventContext.Provider>
}

export const useUserEvent = () => {
  const ctx = useContext(UserEventContext)

  if (!ctx) {
    throw new Error('EventContext is not configured')
  }

  return ctx
}
