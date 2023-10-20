'use client'

import { LibraryLayout, Header } from '@/layouts/LibraryLayout'
import { Sidebar } from '@/components/Sidebar'
import { LibraryTracksList } from './LibraryTracksList'
import { MediaUploaderComponent } from '@/modules/MediaUploader'
import { useLibraryPageStore } from './hooks/useLibraryPageStore'

import type { User, Channel, UserTrack } from '@/api'

interface Props {
  readonly user: User
  readonly initialTracks: readonly UserTrack[]
  readonly initialTotalCount: number
  readonly channels: readonly Channel[]
}

export const LibraryPage: React.FC<Props> = ({
  user,
  initialTracks,
  initialTotalCount,
  channels,
}) => {
  const libraryPageStore = useLibraryPageStore(initialTracks, initialTotalCount)

  return (
    <>
      <LibraryLayout
        header={<Header user={user} />}
        sidebar={<Sidebar channels={channels} activeItem={['library']} />}
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

export const LibraryPageWithProviders: React.FC<Props> = (props) => {
  return <LibraryPage {...props} />
}
