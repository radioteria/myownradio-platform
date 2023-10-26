import { useState } from 'react'
import { ActualLiveStatus, DesiredLiveStatus } from '@/views/ChannelPage/types'

export const useLiveStatusStore = (
  channelId: number,
  initialActualLiveStatus: ActualLiveStatus,
) => {
  const [actualLiveStatus, setActualLiveStatus] =
    useState<ActualLiveStatus>(initialActualLiveStatus)
  const [desiredLiveStatus, setDesiredLiveStatus] = useState<DesiredLiveStatus>(
    actualLiveStatus === 'live' || actualLiveStatus === 'error' ? 'live' : 'preview',
  )

  const handleToggleDesiredLiveStatus = (status: DesiredLiveStatus) => {
    setDesiredLiveStatus(status)
  }

  return { desiredLiveStatus, actualLiveStatus, handleToggleDesiredLiveStatus }
}
