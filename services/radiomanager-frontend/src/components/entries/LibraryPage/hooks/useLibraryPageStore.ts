import { useCallback, useEffect, useRef, useState } from 'react'
import makeDebug from 'debug'
import { LibraryTrackEntry, toLibraryTrackEntry } from '@/components/LibraryTracksList'
import { deleteTracksById } from '@/api'
import { getUnusedUserTracksPage, getUserTracksPage } from '@/api/radiomanager'
import { quantise } from '@/utils/math'
import { remove } from '@/utils/arrays'
import { useHandleLibraryLastUploadedTrack } from '../hooks/useHandleLibraryLastUploadedTrack'

import type { UserTrack } from '@/api'

const debug = makeDebug('useLibraryPageStore')
const REQUEST_MORE_TRACKS_WINDOW = 100

interface StoreConfig {
  readonly filterUnusedTracks?: boolean
}

export const useLibraryPageStore = (
  initialTracks: readonly UserTrack[],
  initialTotalCount: number,
  config?: StoreConfig,
) => {
  const [trackEntries, setTrackEntries] = useState<readonly (LibraryTrackEntry | null)[]>(() => {
    const entries = new Array<LibraryTrackEntry | null>(initialTotalCount).fill(null)
    entries.splice(0, initialTracks.length, ...initialTracks.map(toLibraryTrackEntry))

    return entries
  })

  const [requestOffset, setRequestOffset] = useState<number | null>(null)

  const abortControllersRef = useRef<AbortController[]>([])

  const addTrackEntry = useCallback((track: UserTrack) => {
    setTrackEntries((entries) => [toLibraryTrackEntry(track), ...entries])
  }, [])

  useHandleLibraryLastUploadedTrack(addTrackEntry, config?.filterUnusedTracks ?? false)

  const handleDeletingTracks = (trackIds: readonly number[]) => {
    const idsSet = new Set(trackIds)
    const updatedTrackEntries = trackEntries.filter((item) => {
      if (!item) {
        return true
      }

      return !idsSet.has(item.trackId)
    })

    setTrackEntries(updatedTrackEntries)

    deleteTracksById(trackIds).catch((error) => {
      // Restore tracks after unsuccessful delete
      setTrackEntries(trackEntries)
    })
  }

  const handleRequestMoreTracks = useCallback((trackIndex: number) => {
    const quantisedIndex = quantise(trackIndex, REQUEST_MORE_TRACKS_WINDOW / 2)
    const offset = Math.max(0, quantisedIndex - REQUEST_MORE_TRACKS_WINDOW / 2)

    setRequestOffset(offset)
  }, [])

  useEffect(() => {
    if (requestOffset === null) return
    const offset = requestOffset

    const abortController = new AbortController()
    abortControllersRef.current.push(abortController)

    const requestOpts = {
      offset,
      limit: REQUEST_MORE_TRACKS_WINDOW,
      signal: abortController.signal,
    }
    const promise = config?.filterUnusedTracks
      ? getUnusedUserTracksPage(requestOpts)
      : getUserTracksPage(requestOpts)

    promise
      .then(({ items, totalCount }) => {
        setTrackEntries((prevEntries) => {
          let nextEntries = [...prevEntries]
          nextEntries.splice(offset, items.length, ...items.map(toLibraryTrackEntry))

          if (totalCount > nextEntries.length) {
            nextEntries.push(...new Array<null>(totalCount - nextEntries.length).fill(null))
          } else if (totalCount < nextEntries.length) {
            nextEntries.splice(totalCount)
          }

          return nextEntries
        })
      })
      .finally(() => {
        remove(abortControllersRef.current, abortController)
      })
  }, [requestOffset, config?.filterUnusedTracks])

  useEffect(() => {
    const current = abortControllersRef.current

    return () => {
      current.forEach((ctrl) => ctrl.abort())
    }
  }, [])

  const loadMoreTracks = useCallback(
    async (startIndex: number, endIndex: number, signal: AbortSignal) => {
      const requestOpts = {
        offset: startIndex,
        limit: endIndex - startIndex,
        signal,
      }

      const { items, totalCount } = config?.filterUnusedTracks
        ? await getUnusedUserTracksPage(requestOpts)
        : await getUserTracksPage(requestOpts)

      setTrackEntries((prevEntries) => {
        let nextEntries = [...prevEntries]
        nextEntries.splice(startIndex, items.length, ...items.map(toLibraryTrackEntry))

        if (totalCount > nextEntries.length) {
          nextEntries.push(...new Array<null>(totalCount - nextEntries.length).fill(null))
        } else if (totalCount < nextEntries.length) {
          nextEntries.splice(totalCount)
        }

        return nextEntries
      })
    },
    [config?.filterUnusedTracks],
  )

  return {
    trackEntries,
    loadMoreTracks,
    handleDeletingTracks,
    handleRequestMoreTracks,
  }
}
