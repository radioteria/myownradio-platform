import { useCallback, useState } from 'react'
import { UploadedTrack, UploadingState, UploadingStatus } from './MediaUploaderTypes'

export interface MediaUploader {
  readonly lastUploadedTrack: UploadedTrack | null
  readonly uploadingState: UploadingState
  readonly upload: () => void
  readonly abort: () => void
}

export const useMediaUploaderProvider = (): MediaUploader => {
  const [lastUploadedTrack, setLastUploadedTrack] = useState<UploadedTrack | null>(null)
  const [uploadingState, setUploadingState] = useState<UploadingState>({
    status: UploadingStatus.IDLE,
  })

  const [queue, setQueue] = useState<readonly File[]>([])

  const upload = useCallback(() => {}, [])
  const abort = useCallback(() => {}, [])

  return {
    lastUploadedTrack,
    uploadingState,
    upload,
    abort,
  }
}
