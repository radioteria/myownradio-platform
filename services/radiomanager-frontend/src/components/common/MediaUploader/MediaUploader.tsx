import { useMediaUploader } from '@/modules/MediaUploader'
import useFileSelect from '@/hooks/useFileSelect'
import { scale } from '@/utils/math'

interface Props {
  readonly targetChannelId?: number
}

const ACCEPT_CONTENT_TYPES = 'audio/*'

export const MediaUploader: React.FC<Props> = ({ targetChannelId }) => {
  const { upload, uploadQueue, uploadErrors, uploadedTracks, currentQueueItem } = useMediaUploader()
  const select = useFileSelect(ACCEPT_CONTENT_TYPES, (files) =>
    files.forEach((f) => upload(f, targetChannelId)),
  )

  const uploadProgressTotal = uploadedTracks.length + uploadErrors.length + uploadQueue.length
  const uploadProgress = uploadProgressTotal - uploadQueue.length
  const uploadProgressPercent = scale(uploadProgress, uploadProgressTotal, 100)

  const numberOfUploadedTracks = uploadedTracks.length

  if (!currentQueueItem) {
    return (
      <div className={'fixed z-20 bottom-4 left-4 rounded-lg bg-gray-200 shadow p-2'}>
        <button onClick={select}>Upload...</button>
      </div>
    )
  }

  return (
    <div className={'fixed w-96 z-20 bottom-4 left-4 rounded-lg bg-gray-200 shadow p-2'}>
      <div className={'text-xs'}>Uploading...</div>
      <div className={'bg-gray-200 h-2 rounded my-2 relative'}>
        <div
          className={'h-2 rounded bg-orange-400 transition-width duration-300'}
          style={{
            width: `${uploadProgressPercent}%`,
          }}
        />
      </div>
      <div className={'text-xs'}>
        Uploaded <b>{numberOfUploadedTracks}</b> of <b>{uploadProgressTotal}</b> file(s)
      </div>
    </div>
  )
}
