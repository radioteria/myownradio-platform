import { useNowPlaying } from '@/modules/NowPlaying'
import { ProgressBar } from './ProgressBar'
import { PlayerProgress } from './PlayerProgress'
import { PlayIcon } from './icons/PlayIcon'
import { PrevIcon } from './icons/PrevIcon'
import { NextIcon } from './icons/NextIcon'
import { PauseIcon } from './icons/PauseIcon'

interface Props {
  readonly channelId: number
}

export const ChannelControls: React.FC<Props> = ({ channelId }) => {
  const { nowPlaying } = useNowPlaying()

  return (
    <div className={'bg-gray-200 rounded-lg mt-2 p-4 flex flex-col'}>
      <div className={'truncate text-xs h-4 align-middle text-center mb-4'}>
        {nowPlaying?.currentTrack.title ?? ''}
      </div>

      <div className={'h-4 mb-2'}>
        {nowPlaying && (
          <PlayerProgress
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
