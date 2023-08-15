import cn from 'classnames'
import { Duration } from '@/components/Duration/Duration'
import { ProgressOverlay } from '@/components/ChannelTracksList/ProgressOverlay'
import AnimatedBars from '@/icons/AnimatedBars'
import { ThreeDots } from '@/icons/ThreeDots'
import { TrackItem, CurrentTrack } from './types'
import { TrackListItem } from '@/components/common/TrackList/TrackListItem'

interface Props {
  tracks: readonly TrackItem[]
  currentTrack: CurrentTrack | null
}

export const TrackList: React.FC<Props> = ({ tracks, currentTrack }) => {
  return (
    <ul>
      <li className="flex text-gray-500 h-12">
        <div className="pl-4 pr-2 py-4 w-12 flex-shrink-0 text-right">#</div>
        <div className="px-2 py-4 w-full">Title</div>
        <div className="px-2 py-4 w-full hidden xl:block">Album</div>
        <div className="px-2 py-4 w-20 flex-shrink-0 text-right">⏱</div>
        <div className="pl-2 pr-4 py-4 w-10 flex-shrink-0 text-right" />
      </li>

      {tracks.map((track, index) => {
        return <TrackListItem key={index} track={track} currentTrack={currentTrack} index={index} />
      })}
    </ul>
  )
}
