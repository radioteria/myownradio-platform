import { useMediaUploader } from '@/modules/MediaUploader/index'
import useFileSelect from '@/hooks/useFileSelect'
import { scale } from '@/utils/math'
import { UploadMediaFileIcon } from '@/modules/MediaUploader/icons/UploadMediaFileIcon'
import cn from 'classnames'

interface Props {
  readonly targetChannelId?: number
}

const ACCEPT_CONTENT_TYPES = 'audio/*'

export const MediaUploaderComponent: React.FC<Props> = ({ targetChannelId }) => {
  const { upload, uploadQueue, uploadResults, currentQueueItem } = useMediaUploader()
  const select = useFileSelect(ACCEPT_CONTENT_TYPES, (files) =>
    files.forEach((f) => upload(f, targetChannelId)),
  )

  const totalNumberOfUploads =
    uploadResults.length + uploadQueue.length + (currentQueueItem ? 1 : 0)
  const uploadsProgressPercent = scale(uploadResults.length, totalNumberOfUploads, 100)
  const numberOfUploadedTracks = uploadResults.length

  if (!currentQueueItem) {
    return (
      <div
        className={cn([
          'fixed z-20 bottom-4 left-4 rounded-lg bg-gray-200 shadow p-2',
          'opacity-70 hover:opacity-100 transition-opacity duration-300',
        ])}
      >
        <button onClick={select} title={'Upload media file...'}>
          <UploadMediaFileIcon className={'w-10'} />
        </button>
      </div>
    )
  }

  return (
    <div className={'fixed w-96 z-20 bottom-4 left-4 rounded-lg bg-gray-200 shadow p-2'}>
      <div className={'truncate'}>
        Uploading <b>{currentQueueItem.file.name}</b>...
      </div>
      <div className={'bg-gray-300 h-2 rounded my-2 relative'}>
        <div
          className={'h-2 rounded bg-orange-400 transition-width duration-300'}
          style={{
            width: `${uploadsProgressPercent}%`,
          }}
        />
      </div>
      <div className={''}>
        Uploaded <b>{numberOfUploadedTracks}</b> of <b>{totalNumberOfUploads}</b> file(s)
      </div>
    </div>
  )
}
