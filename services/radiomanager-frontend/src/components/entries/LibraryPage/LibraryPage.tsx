'use client'

import { User, UserChannel, UserTrack } from '@/api/api.types'
import { useCallback, useState } from 'react'
import { getLibraryTracks, MAX_TRACKS_PER_REQUEST } from '@/api/api.client'
import { LibraryLayout } from '@/components/layouts/LibraryLayout'
import { Header } from '@/components/Header'
import { Sidebar } from '@/components/Sidebar'
import {
  LibraryTracksList,
  toLibraryTrackEntry,
} from '@/components/LibraryTracksList/LibraryTracksList'

interface Props {
  user: User
  userTracks: readonly UserTrack[]
  userChannels: readonly UserChannel[]
}

export const LibraryPage: React.FC<Props> = ({ user, userTracks, userChannels }) => {
  const initialTrackEntries = userTracks.map(toLibraryTrackEntry)
  const [trackEntries, setTrackEntries] = useState(initialTrackEntries)

  const addTrackEntry = useCallback((track: UserTrack) => {
    setTrackEntries((entries) => [...entries, toLibraryTrackEntry(track)])
  }, [])

  const removeTrackEntry = useCallback((indexToRemove: number) => {
    setTrackEntries((entries) => entries.filter((_, index) => index !== indexToRemove))
  }, [])

  const initialCanInfinitelyScroll = initialTrackEntries.length === MAX_TRACKS_PER_REQUEST
  const [canInfinitelyScroll, setCanInfinitelyScroll] = useState(initialCanInfinitelyScroll)

  const handleInfiniteScroll = () => {
    getLibraryTracks(trackEntries.length).then((tracks) => {
      const newEntries = tracks.map(toLibraryTrackEntry)
      setTrackEntries((entries) => [...entries, ...newEntries])

      if (MAX_TRACKS_PER_REQUEST > newEntries.length) {
        setCanInfinitelyScroll(newEntries.length === MAX_TRACKS_PER_REQUEST)
      }
    })
  }

  return (
    <LibraryLayout
      header={<Header user={user} />}
      sidebar={<Sidebar channels={userChannels} activeItem={['library']} />}
      content={
        <LibraryTracksList
          tracks={trackEntries}
          tracksCount={userTracks.length}
          canInfinitelyScroll={canInfinitelyScroll}
          onInfiniteScroll={handleInfiniteScroll}
        />
      }
      rightSidebar={null}
    />
  )
}
