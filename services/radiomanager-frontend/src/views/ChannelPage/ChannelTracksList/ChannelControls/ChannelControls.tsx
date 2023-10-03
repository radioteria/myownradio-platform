import { useNowPlaying } from '@/modules/NowPlaying'
import { ProgressBar } from './ProgressBar'
import { TimePosition } from './TimePosition'
import { PrevIcon } from './icons/PrevIcon'
import { NextIcon } from './icons/NextIcon'
import { PauseIcon } from './icons/PauseIcon'
import { StreamOverlay } from './StreamOverlay'

interface Props {
  readonly channelId: number
}

export const ChannelControls: React.FC<Props> = ({ channelId }) => {
  const { nowPlaying } = useNowPlaying()

  return (
    <div className={'bg-morblue-950 text-gray-400 rounded-lg p-4 flex flex-col text-xs'}>
      <StreamOverlay channelId={channelId} />

      <div className={'truncate h-4 align-middle text-center my-4'}>
        {nowPlaying?.currentTrack.title ?? ''}
      </div>

      <div className={'h-4 mb-2'}>
        {nowPlaying && (
          <TimePosition
            position={nowPlaying?.currentTrack.offset}
            duration={nowPlaying?.currentTrack.duration}
            progressBar={
              <ProgressBar
                position={nowPlaying?.currentTrack.offset}
                duration={nowPlaying?.currentTrack.duration}
              />
            }
          />
        )}
      </div>

      <div className={'flex items-center justify-center gap-8'}>
        <PrevIcon size={28} />
        <PauseIcon size={48} />
        <NextIcon size={28} />
      </div>
    </div>
  )
}
