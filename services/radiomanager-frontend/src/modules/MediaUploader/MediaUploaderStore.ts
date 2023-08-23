import { atom, getDefaultStore } from 'jotai/index'
import {
  QueueItem,
  UploadedTrack,
  UploadedTrackType,
  UploadErrorItem,
} from '@/modules/MediaUploader/MediaUploaderTypes'
import { uploadTrackToChannel, uploadTrackToLibrary } from '@/api/api.client'

const isAborted = (error: Error) => error instanceof DOMException && error.name === 'AbortError'

export const makeMediaUploaderStore = () => {
  const uploadQueueAtom = atom<readonly QueueItem[]>([])
  const uploadErrorsAtom = atom<readonly UploadErrorItem[]>([])
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

  const addUploadError = (error: Error, queueItem: QueueItem) => {
    uploaderStore.set(uploadErrorsAtom, [
      ...uploaderStore.get(uploadErrorsAtom),
      { queueItem, error },
    ])
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

  uploaderStore.sub(uploadQueueAtom, () => {
    const nextQueueItem = peekInQueue()

    if (!nextQueueItem || isUploading) {
      return
    }

    const { channelId, file } = nextQueueItem

    abortController = new AbortController()

    isUploading = true

    // Unify track prototypes
    const promise = channelId
      ? uploadTrackToChannel(channelId, file, abortController.signal).then((uploadedTrack) => {
          uploaderStore.set(lastUploadedTrackAtom, {
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
          uploaderStore.set(lastUploadedTrackAtom, {
            type: UploadedTrackType.LIBRARY,
            track: uploadedTrack,
          })
        })

    promise.finally(() => {
      isUploading = false
    })

    promise
      .then(() => {
        popFromQueue()
      })
      .catch((error) => {
        if (isAborted(error)) {
          resetItemsQueue()
          return
        }

        popFromQueue()
        addUploadError(error, nextQueueItem)
      })
  })

  return {
    upload,
    abort,
    uploadQueueAtom,
    uploadErrorsAtom,
    lastUploadedTrackAtom,
  }
}
