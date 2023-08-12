import { getChannelTracks, getSelf } from '@/api/api.client'
import { Sidebar } from '@/components/Sidebar/Sidebar'
import { Header } from '@/components/Header/Header'
import { ChannelTracksList } from '@/components/ChannelTracksList/ChannelTracksList'
import { LibraryLayout } from '@/components/layouts/LibraryLayout'

export default async function UserChannel({ params: { id } }: { params: { id: string } }) {
  const channelId = Number(id)
  const [self, channelTracks] = await Promise.all([getSelf(), getChannelTracks(channelId)])

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  return (
    <LibraryLayout
      header={<Header user={self.user} />}
      sidebar={<Sidebar channels={self.streams} activeItem={['channel', channelId]} />}
      content={
        <ChannelTracksList
          channelId={channelId}
          tracks={channelTracks}
          tracksCount={channelTracks.length}
        />
      }
      rightSidebar={
        <>
          <div
            className={
              'bg-black aspect-video text-white flex items-center justify-center rounded-lg'
            }
          >
            OFFLINE
          </div>
          <div>TODO</div>
        </>
      }
    />
  )
}
