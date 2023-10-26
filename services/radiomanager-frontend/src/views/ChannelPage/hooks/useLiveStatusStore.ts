import { useState } from 'react'
import makeDebug from 'debug'
import { ActualLiveStatus, DesiredLiveStatus } from '@/views/ChannelPage/types'
import {
  getOutgoingStream,
  OutgoingStream,
  startOutgoingStream,
  stopOutgoingStream,
} from '@/api/radiomanager'

const debug = makeDebug('useLiveStatusStore')

export const useLiveStatusStore = (channelId: number, initialOutgoingStream: OutgoingStream) => {
  const [outgoingStream, setOutgoingStream] = useState(initialOutgoingStream)

  const handleToggleDesiredLiveStatus = (status: DesiredLiveStatus) => {
    if (status === 'live') {
      startOutgoingStream(channelId)
        .then(() => getOutgoingStream(channelId))
        .then(setOutgoingStream)
        .catch((error) => {
          debug('Error: %s', error)
        })
    }

    if (status === 'preview') {
      stopOutgoingStream(channelId)
        .then(() => getOutgoingStream(channelId))
        .then(setOutgoingStream)
        .catch((error) => {
          debug('Error: %s', error)
        })
    }
  }

  const actualLiveStatus: ActualLiveStatus =
    outgoingStream.status === 'Stopped' ? 'preview' : 'live'

  return { actualLiveStatus, handleToggleDesiredLiveStatus }
}
