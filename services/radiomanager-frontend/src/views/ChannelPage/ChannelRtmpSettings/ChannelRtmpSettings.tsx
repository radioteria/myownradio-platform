import { ChangeEvent, FormEvent, useState } from 'react'
import { Channel } from '@/api'

interface Props {
  readonly channel: Channel

  onUpdateRtmpSettings(url: string, streamingKey: string): void
}

export const ChannelRtmpSettings: React.FC<Props> = ({ channel, onUpdateRtmpSettings }) => {
  const [rtmpUrl, setRtmpUrl] = useState(() => channel.rtmpUrl)
  const [rtmpStreamKey, setRtmpStreamKey] = useState(() => channel.rtmpStreamingKey)

  const handleRtmpUrlChange = (event: ChangeEvent<HTMLInputElement>) => {
    setRtmpUrl(event.target.value)
  }

  const handleRtmpStreamingKeyChange = (event: ChangeEvent<HTMLInputElement>) => {
    setRtmpStreamKey(event.target.value)
  }

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    onUpdateRtmpSettings(rtmpUrl, rtmpStreamKey)
  }

  return (
    <div className={'bg-morblue-950 rounded-lg p-4 mt-2 w-full'}>
      <form className={''} onSubmit={handleSubmit}>
        <div className={'mb-4'}>
          <label className="block text-gray-400 text-sm font-bold mb-2" htmlFor="rtmpUrl">
            RTMP URL
          </label>
          <input
            className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            id="rtmpUrl"
            type="text"
            placeholder="rtmp://..."
            value={rtmpUrl}
            onChange={handleRtmpUrlChange}
          />
        </div>
        <div className={'mb-6'}>
          <label className="block text-gray-400 text-sm font-bold mb-2" htmlFor="streamKey">
            Stream Key
          </label>
          <input
            className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            id={'streamKey'}
            value={rtmpStreamKey}
            type={'password'}
            onChange={handleRtmpStreamingKeyChange}
          />
        </div>
        <div className="flex items-center justify-end">
          <button
            className="bg-blue-400 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
            type="submit"
          >
            Save
          </button>
        </div>
      </form>
    </div>
  )
}
