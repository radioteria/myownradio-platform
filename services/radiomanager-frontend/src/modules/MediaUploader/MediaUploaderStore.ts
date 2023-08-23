import { atom, getDefaultStore } from 'jotai'
import PQueue from 'p-queue'
import {
  QueueItem,
  UploadedTrack,
  UploadedTrackType,
  UploadErrorItem,
} from '@/modules/MediaUploader/MediaUploaderTypes'
import { uploadTrackToChannel, uploadTrackToLibrary } from '@/api/api.client'

const uploadPromiseQueue = new PQueue({ concurrency: 1 })

const isAborted = (error: unknown) => error instanceof DOMException && error.name === 'AbortError'

export const createMediaUploaderStore = () => {
  const uploadQueueAtom = atom<readonly QueueItem[]>([])
  const uploadErrorsAtom = atom<readonly UploadErrorItem[]>([])
  const uploadedTracksAtom = atom<readonly UploadedTrack[]>([])
  const lastUploadedTrackAtom = atom<UploadedTrack | null>(null)
  const currentQueueItemAtom = atom<QueueItem | null>(null)

  const uploaderStore = getDefaultStore()

  const addItemToQueue = (item: QueueItem) => {
    uploaderStore.set(uploadQueueAtom, [...uploaderStore.get(uploadQueueAtom), item])
  }

  const popFromQueue = (): QueueItem | null => {
    const [head, ...rest] = uploaderStore.get(uploadQueueAtom)

    if (!head) {
      return null
    }

    uploaderStore.set(uploadQueueAtom, rest)

    return head
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

  const setCurrentQueueItem = (item: QueueItem | null) => {
    uploaderStore.set(currentQueueItemAtom, item)
  }

  let abortController: AbortController | null = null

  const abort = () => {
    abortController?.abort()
  }

  const upload = (file: File, channelId?: number) => {
    const queueItem = { file, channelId }

    addItemToQueue(queueItem)
  }

  uploaderStore.sub(uploadQueueAtom, () =>
    uploadPromiseQueue.add(async () => {
      const nextQueueItem = popFromQueue()

      if (!nextQueueItem) {
        setCurrentQueueItem(null)
        return
      }

      setCurrentQueueItem(nextQueueItem)

      abortController = new AbortController()

      try {
        const { channelId, file } = nextQueueItem

        const uploadedTrack = channelId
          ? await uploadTrackToChannel(channelId, file, abortController.signal).then((track) => ({
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
          : await uploadTrackToLibrary(file, abortController.signal).then((track) => ({
              type: UploadedTrackType.LIBRARY as const,
              track,
            }))

        setLastUploadedTrack(uploadedTrack)
        addUploadedTrack(uploadedTrack)
      } catch (error) {
        if (isAborted(error)) {
          resetItemsQueue()
          return
        }

        addUploadError(error, nextQueueItem)
      } finally {
        abortController = null
      }
    }),
  )

  return {
    upload,
    abort,
    currentQueueItemAtom,
    uploadQueueAtom,
    uploadErrorsAtom,
    lastUploadedTrackAtom,
    uploadedTracksAtom,
  }
}
