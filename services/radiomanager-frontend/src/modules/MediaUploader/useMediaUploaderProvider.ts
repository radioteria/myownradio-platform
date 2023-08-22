import { useCallback, useEffect, useRef, useState } from 'react'
import {
  QueueItem,
  UploadedTrack,
  UploadedTrackType,
  UploadingState,
  UploadingStatus,
} from './MediaUploaderTypes'
import { uploadTrackToChannel, uploadTrackToLibrary } from '@/api/api.client'

export interface MediaUploader {
  readonly uploadQueue: readonly QueueItem[]
  readonly lastUploadedTrack: UploadedTrack | null
  readonly uploadingState: UploadingState
  readonly upload: (file: File, channelId?: number) => void
  readonly abort: () => void
}

const isAborted = (error: Error) => error instanceof DOMException && error.name === 'AbortError'

export const useMediaUploaderProvider = (): MediaUploader => {
  const [lastUploadedTrack, setLastUploadedTrack] = useState<UploadedTrack | null>(null)
  const [uploadingState, setUploadingState] = useState<UploadingState>({
    status: UploadingStatus.IDLE,
  })

  const [uploadQueue, setUploadQueue] = useState<readonly QueueItem[]>([])

  let unmountedRef = useRef(false)

  useEffect(
    () => () => {
      unmountedRef.current = true
    },
    [],
  )

  useEffect(() => {
    if (uploadQueue.length === 0) {
      return
    }

    const [{ channelId, file }, ...restQueueItems] = uploadQueue
    const abortController = new AbortController()

    // Unify track prototypes
    const promise = channelId
      ? uploadTrackToChannel(channelId, file, abortController.signal).then((uploadedTrack) => {
          setLastUploadedTrack({
            channelId,
            type: UploadedTrackType.CHANNEL,
            track: {
              ...uploadedTrack,
              artist: uploadedTrack.artist ?? '',
              album: uploadedTrack.album ?? '',
              genre: uploadedTrack.genre ?? '',
              trackNumber: String(uploadedTrack.trackNumber),
            },
          })
        })
      : uploadTrackToLibrary(file, abortController.signal).then((uploadedTrack) => {
          setLastUploadedTrack({
            type: UploadedTrackType.LIBRARY,
            track: uploadedTrack,
          })
        })

    if (!unmountedRef.current) {
      promise
        .then(() => {
          setUploadQueue(restQueueItems)
        })
        .catch((error) => {
          setUploadQueue(isAborted(error) ? [] : restQueueItems)
        })
    }

    return () => {
      abortController.abort()
    }
  }, [uploadQueue])

  const upload = useCallback((file: File, channelId?: number) => {
    const queueItem = { file, channelId }

    setUploadQueue((queuedItems) => [...queuedItems, queueItem])
  }, [])
  const abort = useCallback(() => {}, [])

  return {
    uploadQueue,
    lastUploadedTrack,
    uploadingState,
    upload,
    abort,
  }
}
