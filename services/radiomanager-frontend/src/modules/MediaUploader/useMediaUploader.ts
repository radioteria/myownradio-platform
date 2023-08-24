import { useAtom } from 'jotai'
import { MediaUploadQueueItem, MediaUploadResult, UploadedMediaTrack } from './MediaUploaderTypes'
import { MediaUploaderStore } from './MediaUploaderStore'

export interface MediaUploader {
  readonly uploadQueue: readonly MediaUploadQueueItem[]
  readonly uploadResults: readonly MediaUploadResult[]
  readonly currentQueueItem: MediaUploadQueueItem | null
  readonly lastUploadedTrack: UploadedMediaTrack | null
  readonly upload: (file: File, channelId?: number) => void
  readonly abort: () => void
}

const store = new MediaUploaderStore()

store.init()

export const useMediaUploader = (): MediaUploader => {
  const [lastUploadedTrack] = useAtom(store.lastUploadedTrackAtom)
  const [uploadQueue] = useAtom(store.uploadQueueAtom)
  const [uploadResults] = useAtom(store.uploadResultsAtom)
  const [currentQueueItem] = useAtom(store.currentQueueItemAtom)

  const { upload, abort } = store

  return {
    uploadQueue,
    uploadResults,
    lastUploadedTrack,
    currentQueueItem,
    upload,
    abort,
  }
}
