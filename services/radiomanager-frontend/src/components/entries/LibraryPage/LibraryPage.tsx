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
  readonly initialTracks: readonly UserTrack[]
  readonly totalTracks: number
  readonly channels: readonly UserChannel[]
}

export const LibraryPage: React.FC<Props> = ({ user, initialTracks, totalTracks, channels }) => {
  const libraryPageStore = useLibraryPageStore(initialTracks)

  return (
    <>
      <LibraryLayout
        header={<Header user={user} />}
        sidebar={<Sidebar channels={channels} activeItem={['library']} />}
        content={
          <LibraryTracksList
            tracks={libraryPageStore.trackEntries}
            totalTracks={totalTracks}
            onDeleteTracks={libraryPageStore.handleDeletingTracks}
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
