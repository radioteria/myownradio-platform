import cn from 'classnames'
import { UserTrack } from '@/api.types'
import { Duration } from '@/components/Duration/Duration'

interface Props {
  tracks: readonly UserTrack[]
  tracksCount: number
}

export const MediaTracksList: React.FC<Props> = ({ tracks, tracksCount }) => {
  return (
    <section>
      <ul className={'mt-8'}>
        <li className="flex text-gray-600">
          <div className="p-2 w-8 flex-shrink-0"></div>
          <div className="p-2 w-8 flex-shrink-0"></div>
          <div className="p-2 w-full">Title</div>
          <div className="p-2 w-full">Artist</div>
          <div className="p-2 w-full">Album</div>
          <div className="p-2 w-20 flex-shrink-0 text-right">⏱</div>
        </li>

        {tracks.map((track) => (
          <li key={track.tid} className={'flex border-gray-800'}>
            <div className="p-3 w-8 flex-shrink-0">▶️</div>
            <div className="p-3 w-8 flex-shrink-0">❤️</div>
            <div className="p-3 w-full">{track.title || track.filename}</div>
            <div className="p-3 w-full">{track.artist}</div>
            <div className="p-3 w-full">{track.album}</div>
            <div className="p-3 w-20 flex-shrink-0 text-right">
              <Duration millis={track.duration} />
            </div>
          </li>
        ))}
      </ul>
    </section>
  )
}
