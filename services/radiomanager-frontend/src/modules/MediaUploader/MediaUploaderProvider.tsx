import { createContext, ReactNode, useContext, useEffect, useState } from 'react'
import { UserTrack } from '@/api/api.types'
import { File } from 'buffer'

interface UploadedTrack {
  readonly channelIds: readonly number[]
  readonly track: UserTrack
}

export enum UploadingStatus {
  IDLE = 'IDLE',
  UPLOADING = 'UPLOADING',
}

interface UploadingState {
  status: UploadingStatus
}

interface MediaUploaderService {
  lastUploadedTrack: UploadedTrack | null
  uploadingState: UploadingState
  upload(file: File): void
  abort(): void
}

const MediaUploaderContext = createContext<MediaUploaderService | null>(null)

export const useMediaUploader = () => {
  const ctx = useContext(MediaUploaderContext)

  if (!ctx) {
    throw new Error('MediaUploaderService has not been found in the Context')
  }

  return ctx
}

interface Props {
  readonly children?: ReactNode
}

export const MediaUploaderProvider: React.FC<Props> = ({ children }) => {
  const upload = (file: File) => {}
  const abort = () => {}

  const [lastUploadedTrack, setLastUploadedTrack] = useState<UploadedTrack | null>(null)
  const [uploadingState, setUploadingState] = useState<UploadingState>({
    status: UploadingStatus.IDLE,
  })

  const mediaUploaderService: MediaUploaderService = {
    upload,
    abort,
    lastUploadedTrack,
    uploadingState,
  }

  return (
    <MediaUploaderContext.Provider value={mediaUploaderService}>
      {children}
    </MediaUploaderContext.Provider>
  )
}
