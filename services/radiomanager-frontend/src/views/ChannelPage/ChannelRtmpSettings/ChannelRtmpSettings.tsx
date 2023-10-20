import { ChangeEvent, FormEvent, useState } from 'react'
import { Channel } from '@/api'

interface Props {
  readonly channel: Channel

  onUpdateRtmpSettings(url: string, streamingKey: string): void
}

export const ChannelRtmpSettings: React.FC<Props> = ({ channel, onUpdateRtmpSettings }) => {
  const [rtmpUrl, setRtmpUrl] = useState(() => channel.rtmpUrl)
  const [rtmpStreamingKey, setRtmpStreamingKey] = useState(() => channel.rtmpStreamingKey)

  const handleRtmpUrlChange = (event: ChangeEvent<HTMLInputElement>) => {
    setRtmpUrl(event.target.value)
  }

  const handleRtmpStreamingKeyChange = (event: ChangeEvent<HTMLInputElement>) => {
    setRtmpStreamingKey(event.target.value)
  }

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    onUpdateRtmpSettings(rtmpUrl, rtmpStreamingKey)
  }

  return (
    <form className={'block'} onSubmit={handleSubmit}>
      <div>
        <div>URL</div>
        <input className={'block w-full'} value={rtmpUrl} onChange={handleRtmpUrlChange} />
      </div>
      <div>
        <div>Streaming Key</div>
        <input
          className={'block w-full'}
          value={rtmpStreamingKey}
          type={'password'}
          onChange={handleRtmpStreamingKeyChange}
        />
      </div>
      <div>
        <button type={'submit'}>Save</button>
      </div>
    </form>
  )
}
