'use client'

import { LibraryLayout } from '@/components/layouts/LibraryLayout'
import { Header } from '@/components/Header'
import { Sidebar } from '@/components/Sidebar'
import { LibraryTracksList } from '@/components/LibraryTracksList/LibraryTracksList'
import { MediaUploaderComponent } from '@/modules/MediaUploader'
import { useLibraryPageStore } from './hooks/useLibraryPageStore'

import type { User, UserChannel, UserTrack } from '@/api'

interface Props {
  readonly user: User
  readonly initialTracks: readonly UserTrack[]
  readonly initialTotalCount: number
  readonly channels: readonly UserChannel[]
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
