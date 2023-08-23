import { useMediaUploader } from '@/modules/MediaUploader'
import useFileSelect from '@/hooks/useFileSelect'

interface Props {
  readonly targetChannelId?: number
}

const ACCEPT_CONTENT_TYPES = 'audio/*'

export const MediaUploader: React.FC<Props> = ({ targetChannelId }) => {
  const { upload, currentQueueItem, uploadQueue, uploadErrors } = useMediaUploader()
  const select = useFileSelect(ACCEPT_CONTENT_TYPES, (files) => files.forEach((f) => upload(f)))

  return (
    <>
      <button onClick={select}>Upload</button>
      <div>Current: {currentQueueItem?.file.name}</div>
      <h1>Queue:</h1>
      <ul>
        {uploadQueue.map((item, i) => (
          <li key={i}>{item.file.name}</li>
        ))}
      </ul>
      <h1>Errors:</h1>
      <ul>
        {uploadErrors.map((item, i) => (
          <li key={i}>{JSON.stringify(item.error)}</li>
        ))}
      </ul>
    </>
  )
}
