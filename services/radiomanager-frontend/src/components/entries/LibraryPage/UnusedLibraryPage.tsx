'use client'

import { User, UserChannel, UserTrack } from '@/api'
import { LibraryLayout } from '@/components/layouts/LibraryLayout'
import { Header } from '@/components/Header'
import { Sidebar } from '@/components/Sidebar'
import { LibraryTracksList } from '@/components/LibraryTracksList/LibraryTracksList'
import { MediaUploaderComponent } from '@/modules/MediaUploader'
import { useLibraryPageStore } from './hooks/useLibraryPageStore'

interface Props {
  readonly user: User
  readonly userTracks: readonly UserTrack[]
  readonly userChannels: readonly UserChannel[]
}

export const UnusedLibraryPage: React.FC<Props> = ({ user, userTracks, userChannels }) => {
  const libraryPageStore = useLibraryPageStore(userTracks, {
    filterUnusedTracks: true,
  })

  return (
    <>
      <LibraryLayout
        header={<Header user={user} />}
        sidebar={<Sidebar channels={userChannels} activeItem={['unused']} />}
        content={
          <LibraryTracksList
            totalTracks={
              libraryPageStore.isFetching ? user.tracksCount : libraryPageStore.trackEntries.length
            }
            tracks={libraryPageStore.trackEntries}
            onDeleteTracks={libraryPageStore.handleDeletingTracks}
          />
        }
        rightSidebar={null}
      />
      <MediaUploaderComponent />
    </>
  )
}

export const UnusedLibraryPageWithProviders: React.FC<Props> = (props) => {
  return <UnusedLibraryPage {...props} />
}
