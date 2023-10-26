import { useState } from 'react'
import { ActualLiveStatus, DesiredLiveStatus } from '@/views/ChannelPage/types'

export const useLiveStatusStore = (
  channelId: number,
  initialDesiredLiveStatus: DesiredLiveStatus,
) => {
  const [desiredLiveStatus, setDesiredLiveStatus] =
    useState<DesiredLiveStatus>(initialDesiredLiveStatus)
  const [actualLiveStatus, setActualLiveStatus] =
    useState<ActualLiveStatus>(initialDesiredLiveStatus)

  const handleToggleDesiredLiveStatus = (status: DesiredLiveStatus) => {
    setDesiredLiveStatus(status)
  }

  return { desiredLiveStatus, actualLiveStatus, handleToggleDesiredLiveStatus }
}
