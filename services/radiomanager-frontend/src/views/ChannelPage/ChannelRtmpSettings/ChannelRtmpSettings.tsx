import { ChangeEvent, FormEvent, useState } from 'react'
import { Channel } from '@/api'

interface Props {
  readonly channel: Channel
  onUpdateRtmpSettings(url: string, streamingKey: string): void
  onToggleDesiredLiveStatus(status: 'live' | 'preview'): void
  desiredLiveStatus: 'live' | 'preview'
  actualLiveStatus: 'preview' | 'starting' | 'live' | 'error'
}

export const ChannelRtmpSettings: React.FC<Props> = ({
  channel,
  onUpdateRtmpSettings,
  onToggleDesiredLiveStatus,
  desiredLiveStatus,
  actualLiveStatus,
}) => {
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

  const handleLiveStatusCheckboxChanged = (event: ChangeEvent<HTMLInputElement>) => {
    onToggleDesiredLiveStatus(event.target.checked ? 'live' : 'preview')
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
        <div className="flex items-center justify-between">
          <label className="relative inline-flex items-center cursor-pointer">
            <input
              type="checkbox"
              value=""
              className="sr-only peer"
              onChange={handleLiveStatusCheckboxChanged}
              checked={desiredLiveStatus === 'live'}
            />
            <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600" />
            <span className="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">
              {actualLiveStatus}
            </span>
          </label>
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
