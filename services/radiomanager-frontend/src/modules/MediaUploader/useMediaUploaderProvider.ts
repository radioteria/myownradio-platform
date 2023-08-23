import { QueueItem, UploadedTrack, UploadErrorItem } from './MediaUploaderTypes'
import { useAtom } from 'jotai'
import { makeMediaUploaderStore } from '@/modules/MediaUploader/MediaUploaderStore'

export interface MediaUploader {
  readonly uploadQueue: readonly QueueItem[]
  readonly uploadErrors: readonly UploadErrorItem[]
  readonly lastUploadedTrack: UploadedTrack | null
  readonly upload: (file: File, channelId?: number) => void
  readonly abort: () => void
}

const store = makeMediaUploaderStore()

export const useMediaUploaderProvider = (): MediaUploader => {
  const [lastUploadedTrack] = useAtom(store.lastUploadedTrackAtom)
  const [uploadQueue] = useAtom(store.uploadQueueAtom)
  const [uploadErrors] = useAtom(store.uploadErrorsAtom)

  return { uploadQueue, uploadErrors, lastUploadedTrack, upload: store.upload, abort: store.abort }
}
