import cn from 'classnames'
import { UserStream } from '@/api/api.types'
import Link from 'next/link'

interface Props {
  channels: readonly UserStream[]
  channelId: number
}

export const Sidebar: React.FC<Props> = ({ channels, channelId }) => {
  return (
    <div>
      <h3 className={cn('text-lg p-4')}>Channels</h3>
      <ul>
        {channels.map((stream) => (
          <li
            key={stream.sid}
            className={cn('px-4 py-2', 'text-ellipsis overflow-hidden', {
              'bg-slate-500 text-gray-50': stream.sid === channelId,
            })}
          >
            <Link href={`/c/${stream.sid}`}>{stream.name}</Link>
          </li>
        ))}
      </ul>
    </div>
  )
}
