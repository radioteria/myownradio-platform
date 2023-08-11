import { UserTrack } from '@/api/api.types'
import { Duration } from '@/components/Duration/Duration'

interface Props {
  tracks: readonly UserTrack[]
  tracksCount: number
}

export const LibraryTracksList: React.FC<Props> = ({ tracks, tracksCount }) => {
  return (
    <section>
      <ul className={'mt-2'}>
        <li className="flex text-gray-600">
          <div className="p-2 w-8 flex-shrink-0"></div>
          <div className="p-2 w-full">Title</div>
          <div className="p-2 w-full hidden lg:block">Album</div>
          <div className="p-2 w-20 flex-shrink-0 text-right">⏱</div>
        </li>

        {tracks.map((track) => (
          <li key={track.tid} className={'flex border-gray-800'}>
            <div className="p-2 w-8 flex-shrink-0">▶️</div>
            <div className="p-2 w-full text-ellipsis overflow-hidden">
              <div className={'whitespace-nowrap'}>{track.title || track.filename}</div>
              {track.artist && <div className={'text-xs whitespace-nowrap'}>{track.artist}</div>}
            </div>
            <div className="p-2 w-full text-ellipsis overflow-hidden hidden lg:block">
              {track.album}
            </div>
            <div className="p-2 w-20 flex-shrink-0 text-right">
              <Duration millis={track.duration} />
            </div>
          </li>
        ))}
      </ul>
    </section>
  )
}
