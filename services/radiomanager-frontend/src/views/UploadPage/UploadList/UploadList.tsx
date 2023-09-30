import { MediaUploadQueueItem, MediaUploadResult } from '@/modules/MediaUploader'

interface Props {
  readonly uploadQueue: readonly MediaUploadQueueItem[]
  readonly uploadResults: readonly MediaUploadResult[]
}

export const UploadList: React.FC<Props> = ({ uploadQueue, uploadResults }) => {
  return (
    <section>
      <ul>
        <h2>Queue</h2>
        {uploadQueue.map((queueItem, index) => (
          <li key={index}>{queueItem.file.name}</li>
        ))}
        <h2>Results</h2>
        {uploadResults.map((result, index) => (
          <li key={index}>
            {result.status} {result.queueItem.file.name}
          </li>
        ))}
      </ul>
    </section>
  )
}
