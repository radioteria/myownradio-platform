import { createContext, ReactNode, useContext, useEffect, useState } from 'react'
import {
  MediaUploader,
  useMediaUploaderProvider,
} from '@/modules/MediaUploader/useMediaUploaderProvider'

const MediaUploaderContext = createContext<MediaUploader | null>(null)

interface Props {
  readonly children?: ReactNode
}

export const MediaUploaderProvider: React.FC<Props> = ({ children }) => {
  const mediaUploader = useMediaUploaderProvider()

  return (
    <MediaUploaderContext.Provider value={mediaUploader}>{children}</MediaUploaderContext.Provider>
  )
}

export const useMediaUploader = (): MediaUploader => {
  const ctx = useContext(MediaUploaderContext)

  if (!ctx) {
    throw new Error('MediaUploaderService has not been found in the Context')
  }

  return ctx
}
