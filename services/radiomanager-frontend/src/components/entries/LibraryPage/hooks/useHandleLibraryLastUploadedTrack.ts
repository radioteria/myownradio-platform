import { useEffect } from 'react'
import { UserTrack } from '@/api'
import { useMediaUploader } from '@/modules/MediaUploader'

export const useHandleLibraryLastUploadedTrack = (
  onLastUploadedTrack: (track: UserTrack) => void,
) => {
  const { lastUploadedTrack } = useMediaUploader()

  useEffect(() => {
    if (!lastUploadedTrack) {
      return
    }

    onLastUploadedTrack(lastUploadedTrack.track)
  }, [lastUploadedTrack, onLastUploadedTrack])
}
