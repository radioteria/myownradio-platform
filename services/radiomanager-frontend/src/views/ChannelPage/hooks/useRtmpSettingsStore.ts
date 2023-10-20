import { Channel } from '@/api'
import { useState } from 'react'
import { updateRtmpSettings } from '@/api/radiomanager'
import makeDebug from 'debug'

const debug = makeDebug('useRtmpSettingsStore')

export function useRtmpSettingsStore(initialChannel: Channel) {
  const [channel, setChannel] = useState(initialChannel)

  const handleUpdateRtmpSettings = (rtmpUrl: string, rtmpStreamingKey: string) => {
    const prevChannel = channel

    setChannel((channel) => ({ ...channel, rtmpUrl, rtmpStreamingKey }))

    updateRtmpSettings(channel.sid, rtmpUrl, rtmpStreamingKey).catch((error) => {
      debug(`Unable to update RTMP settings: %s`, error)

      setChannel(prevChannel)
    })
  }

  return { handleUpdateRtmpSettings, channel }
}
