import { atom, getDefaultStore } from 'jotai/index'
import {
  QueueItem,
  UploadedTrack,
  UploadedTrackType,
  UploadErrorItem,
} from '@/modules/MediaUploader/MediaUploaderTypes'
import { uploadTrackToChannel, uploadTrackToLibrary } from '@/api/api.client'

const isAborted = (error: unknown) => error instanceof DOMException && error.name === 'AbortError'

export const makeMediaUploaderStore = () => {
  const uploadQueueAtom = atom<readonly QueueItem[]>([])
  const uploadErrorsAtom = atom<readonly UploadErrorItem[]>([])
  const uploadedTracksAtom = atom<readonly UploadedTrack[]>([])
  const lastUploadedTrackAtom = atom<UploadedTrack | null>(null)

  const uploaderStore = getDefaultStore()

  const addItemToQueue = (item: QueueItem) => {
    uploaderStore.set(uploadQueueAtom, [...uploaderStore.get(uploadQueueAtom), item])
  }

  const peekInQueue = (): QueueItem | null => {
    const [headItem] = uploaderStore.get(uploadQueueAtom)

    return headItem ?? null
  }

  const popFromQueue = () => {
    const [_head, ...rest] = uploaderStore.get(uploadQueueAtom)
    uploaderStore.set(uploadQueueAtom, rest)
  }

  const resetItemsQueue = () => {
    uploaderStore.set(uploadQueueAtom, [])
  }

  const addUploadError = (error: unknown, queueItem: QueueItem) => {
    uploaderStore.set(uploadErrorsAtom, [
      ...uploaderStore.get(uploadErrorsAtom),
      { queueItem, error },
    ])
  }

  const addUploadedTrack = (track: UploadedTrack) => {
    uploaderStore.set(uploadedTracksAtom, [...uploaderStore.get(uploadedTracksAtom), track])
  }

  const setLastUploadedTrack = (track: UploadedTrack | null) => {
    uploaderStore.set(lastUploadedTrackAtom, track)
  }

  let abortController: AbortController | null = null

  const abort = () => {
    abortController?.abort()
  }

  const upload = (file: File, channelId?: number) => {
    const queueItem = { file, channelId }

    addItemToQueue(queueItem)
  }

  let isUploading = false

  uploaderStore.sub(uploadQueueAtom, async () => {
    const nextQueueItem = peekInQueue()

    if (!nextQueueItem || isUploading) {
      return
    }

    const { channelId, file } = nextQueueItem

    abortController = new AbortController()

    isUploading = true

    // Unify track prototypes
    const promise = channelId
      ? uploadTrackToChannel(channelId, file, abortController.signal).then((track) => ({
          channelId,
          type: UploadedTrackType.CHANNEL as const,
          track: {
            ...track,
            artist: track.artist ?? '',
            album: track.album ?? '',
            genre: track.genre ?? '',
            trackNumber: String(track.trackNumber),
          },
        }))
      : uploadTrackToLibrary(file, abortController.signal).then((track) => ({
          type: UploadedTrackType.LIBRARY as const,
          track,
        }))

    try {
      const uploadedTrack = await promise

      popFromQueue()
      setLastUploadedTrack(uploadedTrack)
      addUploadedTrack(uploadedTrack)
    } catch (error) {
      if (isAborted(error)) {
        resetItemsQueue()
        return
      }

      popFromQueue()
      addUploadError(error, nextQueueItem)
    } finally {
      isUploading = false
    }
  })

  return {
    upload,
    abort,
    uploadQueueAtom,
    uploadErrorsAtom,
    lastUploadedTrackAtom,
  }
}
