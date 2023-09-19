import { useEffect } from 'react'
import { useMediaUploader } from '@/modules/MediaUploader'
import { UploadedTrackType } from '@/modules/MediaUploader/MediaUploaderTypes'
import { UserChannelTrack } from '@/api'

export const useHandleChannelLastUploadedTrack = (
  channelId: number,
  onLastUploadedTrack: (track: UserChannelTrack) => void,
) => {
  const { lastUploadedTrack } = useMediaUploader()

  useEffect(() => {
    // Skip if no track has been uploaded
    if (!lastUploadedTrack) {
      return
    }

    // Exit if the last uploaded track does not belong to the current channel
    if (
      lastUploadedTrack.type !== UploadedTrackType.CHANNEL ||
      lastUploadedTrack.channelId !== channelId
    ) {
      return
    }

    // Invoke the callback with the last uploaded track for this channel
    onLastUploadedTrack(lastUploadedTrack.track)
  }, [lastUploadedTrack, onLastUploadedTrack, channelId])
}
