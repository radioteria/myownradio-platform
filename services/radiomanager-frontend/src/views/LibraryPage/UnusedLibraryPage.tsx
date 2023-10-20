'use client'

import { LibraryLayout, Header } from '@/layouts/LibraryLayout'
import { Sidebar } from '@/components/Sidebar'
import { LibraryTracksList } from '@/views/LibraryPage/LibraryTracksList/LibraryTracksList'
import { MediaUploaderComponent } from '@/modules/MediaUploader'
import { useLibraryPageStore } from './hooks/useLibraryPageStore'

import type { User, Channel, UserTrack } from '@/api'

interface Props {
  readonly user: User
  readonly initialTracks: readonly UserTrack[]
  readonly initialTotalCount: number
  readonly userChannels: readonly Channel[]
}

export const UnusedLibraryPage: React.FC<Props> = ({
  user,
  initialTracks,
  initialTotalCount,
  userChannels,
}) => {
  const libraryPageStore = useLibraryPageStore(initialTracks, initialTotalCount, {
    filterUnusedTracks: true,
  })

  return (
    <>
      <LibraryLayout
        header={<Header user={user} />}
        sidebar={<Sidebar channels={userChannels} activeItem={['unused']} />}
        content={
          <LibraryTracksList
            tracks={libraryPageStore.trackEntries}
            onDeleteTracks={libraryPageStore.handleDeletingTracks}
            loadMoreTracks={libraryPageStore.loadMoreTracks}
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
