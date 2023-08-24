import { atom, getDefaultStore } from 'jotai'
import PQueue from 'p-queue'
import {
  MediaUploadQueueItem,
  MediaUploadResult,
  MediaUploadResultStatus,
  UploadedMediaTrack,
  UploadedTrackType,
} from './MediaUploaderTypes'
import { uploadTrackToChannel, uploadTrackToLibrary } from '@/api/api.client'
import { popFromArrayAtom, pushToArrayAtom } from './MediaUploaderStore.utils'

const uploadPromiseQueue = new PQueue({ concurrency: 1 })

const isAborted = (error: unknown) => error instanceof DOMException && error.name === 'AbortError'

export const createMediaUploaderStore = () => {
  const uploadQueueAtom = atom<readonly MediaUploadQueueItem[]>([])
  const uploadResultsAtom = atom<readonly MediaUploadResult[]>([])

  const lastUploadedTrackAtom = atom<UploadedMediaTrack | null>(null)

  const currentQueueItemAtom = atom<MediaUploadQueueItem | null>(null)

  const uploaderStore = getDefaultStore()

  const addItemToQueue = (item: MediaUploadQueueItem) =>
    pushToArrayAtom(uploadQueueAtom, item, uploaderStore)
  const popFromQueue = (): MediaUploadQueueItem | null =>
    popFromArrayAtom(uploadQueueAtom, uploaderStore)
  const resetItemsQueue = () => uploaderStore.set(uploadQueueAtom, [])

  const addFailedUpload = (error: unknown, queueItem: MediaUploadQueueItem) => {
    const status = MediaUploadResultStatus.FAILED
    pushToArrayAtom(uploadResultsAtom, { status, error, queueItem }, uploaderStore)
  }

  const addSuccessfulUpload = (
    uploadedTrack: UploadedMediaTrack,
    queueItem: MediaUploadQueueItem,
  ) => {
    const status = MediaUploadResultStatus.UPLOADED
    pushToArrayAtom(uploadResultsAtom, { status, uploadedTrack, queueItem }, uploaderStore)
    setLastUploadedTrack(uploadedTrack)
  }

  const setLastUploadedTrack = (track: UploadedMediaTrack | null) => {
    uploaderStore.set(lastUploadedTrackAtom, track)
  }

  const setCurrentQueueItem = (item: MediaUploadQueueItem | null) => {
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
      const queueItem = popFromQueue()

      if (!queueItem) {
        setCurrentQueueItem(null)
        return
      }

      setCurrentQueueItem(queueItem)

      abortController = new AbortController()

      try {
        const { channelId, file } = queueItem

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

        addSuccessfulUpload(uploadedTrack, queueItem)
      } catch (error) {
        if (isAborted(error)) {
          resetItemsQueue()
          return
        }

        addFailedUpload(error, queueItem)
      } finally {
        abortController = null
      }
    }),
  )

  return {
    uploadQueueAtom,
    uploadResultsAtom,
    currentQueueItemAtom,
    lastUploadedTrackAtom,
    upload,
    abort,
  }
}
