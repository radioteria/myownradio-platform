import cn from 'classnames'
import { UserTrack } from '@/api.types'

interface Props {
  tracks: readonly UserTrack[]
  tracksCount: number
}

export const MediaTracksList: React.FC<Props> = ({ tracks, tracksCount }) => {
  return (
    <section>
      <h3 className={cn('text-xl')}>All tracks ({tracksCount})</h3>
      <ul>
        {tracks.map((track) => (
          <li key={track.tid}>{track.title}</li>
        ))}
      </ul>
    </section>
  )
}
