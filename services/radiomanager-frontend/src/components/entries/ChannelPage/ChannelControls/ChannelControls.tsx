import { useNowPlaying } from '@/modules/NowPlaying'
import { ProgressBar } from '@/components/entries/ChannelPage/ChannelControls/ProgressBar'
import { PlayerProgress } from '@/components/entries/ChannelPage/ChannelControls/PlayerProgress'

interface Props {
  readonly channelId: number
}

export const ChannelControls: React.FC<Props> = ({ channelId }) => {
  const { nowPlaying } = useNowPlaying()

  return (
    <div className={'bg-gray-200 rounded-lg mt-2 text-center'}>
      <div className={'truncate p-2'}>{nowPlaying?.currentTrack.title}</div>

      {nowPlaying && (
        <div className={'p-2'}>
          <PlayerProgress
            position={nowPlaying.currentTrack.offset}
            duration={nowPlaying.currentTrack.duration}
            progressBar={
              <ProgressBar
                position={nowPlaying.currentTrack.offset}
                duration={nowPlaying.currentTrack.duration}
              />
            }
          />
        </div>
      )}
      <div className={'p-2'}>TODO: CONTROLS</div>
    </div>
  )
}
