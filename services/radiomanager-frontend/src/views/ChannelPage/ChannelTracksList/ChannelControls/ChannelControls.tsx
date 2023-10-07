import { useNowPlaying } from '@/modules/NowPlaying'
import { ProgressBar } from './ProgressBar'
import { TimePosition } from './TimePosition'
import { PrevIcon } from './icons/PrevIcon'
import { NextIcon } from './icons/NextIcon'
import { PauseIcon } from './icons/PauseIcon'
import { StreamOverlay } from './StreamOverlay'

interface Props {
  readonly channelId: number
  readonly onPlayNext: () => void
  readonly onPlayPrev: () => void
  readonly onPlay: () => void
  readonly onPause: () => void
  readonly onStop: () => void
}

export const ChannelControls: React.FC<Props> = ({
  channelId,
  onPlayNext,
  onPlayPrev,
  onPlay,
  onPause,
  onStop,
}) => {
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
            withProgressing={nowPlaying?.playbackStatus === 1}
            progressBar={
              <ProgressBar
                position={nowPlaying?.currentTrack.offset}
                duration={nowPlaying?.currentTrack.duration}
                withProgressing={nowPlaying?.playbackStatus === 1}
              />
            }
          />
        )}
      </div>

      <div className={'flex items-center justify-center gap-8'}>
        <button onClick={onPlayPrev}>
          <PrevIcon size={28} />
        </button>

        {!nowPlaying ? (
          <button onClick={onPlay}>
            <PauseIcon size={48} />
          </button>
        ) : nowPlaying.playbackStatus === 1 ? (
          <button onClick={onPause}>
            <PauseIcon size={48} />
          </button>
        ) : (
          <button onClick={onPlay}>
            <PauseIcon size={48} />
          </button>
        )}

        <button onClick={onPlayNext}>
          <NextIcon size={28} />
        </button>
      </div>
    </div>
  )
}
