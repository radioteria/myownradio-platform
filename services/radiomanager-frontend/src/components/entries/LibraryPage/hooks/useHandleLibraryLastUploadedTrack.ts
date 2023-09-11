import { useEffect } from 'react'
import { UserTrack } from '@/api'
import { UploadedTrackType, useMediaUploader } from '@/modules/MediaUploader'

export const useHandleLibraryLastUploadedTrack = (
  onLastUploadedTrack: (track: UserTrack) => void,
  filterUnusedTracks: boolean,
) => {
  const { lastUploadedTrack } = useMediaUploader()

  useEffect(() => {
    if (!lastUploadedTrack) {
      return
    }

    if (filterUnusedTracks && lastUploadedTrack.type === UploadedTrackType.CHANNEL) {
      return
    }

    onLastUploadedTrack(lastUploadedTrack.track)
  }, [lastUploadedTrack, onLastUploadedTrack, filterUnusedTracks])
}
