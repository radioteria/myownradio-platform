import cn from 'classnames'
import { UserStream } from '@/api/api.types'
import Link from 'next/link'

type ActiveItem = readonly ['channel', number] | readonly ['library']

interface Props {
  channels: readonly UserStream[]
  activeItem: ActiveItem | null
}

export const Sidebar: React.FC<Props> = ({ channels, activeItem }) => {
  const activeChannelId = activeItem?.[0] === 'channel' ? activeItem[1] : null

  return (
    <div className={'py-0'}>
      <h3 className={'text-md text-gray-500 px-4 py-4'}>LIBRARY</h3>
      <ul>
        <li
          className={cn('px-4 py-2', 'text-ellipsis overflow-hidden', {
            'bg-morblue-400 text-gray-50': activeItem?.[0] === 'library',
          })}
        >
          <Link className={'block'} href={`/`}>
            All Tracks
          </Link>
        </li>

        <li className={'py-2'} />

        {channels.map((channel) => {
          return (
            <li
              key={channel.sid}
              className={cn('px-4 py-2', 'text-ellipsis overflow-hidden', {
                'bg-morblue-400 text-gray-50': activeChannelId === channel.sid,
              })}
            >
              <Link className={'block truncate'} href={`/c/${channel.sid}`}>
                {channel.name}
              </Link>
            </li>
          )
        })}
      </ul>
    </div>
  )
}
