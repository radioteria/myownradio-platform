import { useAtom } from 'jotai'
import { QueueItem, UploadedTrack, UploadErrorItem } from './MediaUploaderTypes'
import { createMediaUploaderStore } from './MediaUploaderStore'

export interface MediaUploader {
  readonly uploadQueue: readonly QueueItem[]
  readonly uploadErrors: readonly UploadErrorItem[]
  readonly currentQueueItem: QueueItem | null
  readonly lastUploadedTrack: UploadedTrack | null
  readonly upload: (file: File, channelId?: number) => void
  readonly abort: () => void
}

const store = createMediaUploaderStore()

export const useMediaUploader = (): MediaUploader => {
  const [lastUploadedTrack] = useAtom(store.lastUploadedTrackAtom)
  const [uploadQueue] = useAtom(store.uploadQueueAtom)
  const [uploadErrors] = useAtom(store.uploadErrorsAtom)
  const [currentQueueItem] = useAtom(store.currentQueueItemAtom)

  const { upload, abort } = store

  return {
    uploadQueue,
    uploadErrors,
    lastUploadedTrack,
    currentQueueItem,
    upload,
    abort,
  }
}
