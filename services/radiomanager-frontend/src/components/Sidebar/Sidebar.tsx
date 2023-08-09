import cn from 'classnames'
import { UserStreamSchema } from '@/api.types'

interface Props {
  channels: readonly UserStreamSchema[]
}

export const Sidebar: React.FC<Props> = ({ channels }) => {
  return (
    <div>
      <h3 className={cn('text-xl')}>All Channels ({channels.length})</h3>
      <ul>
        {channels.map((stream) => (
          <li key={stream.sid}>{stream.name}</li>
        ))}
      </ul>
    </div>
  )
}
