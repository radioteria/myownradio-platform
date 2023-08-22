import { useMediaUploader } from '@/modules/MediaUploader'
import useFileSelect from '@/hooks/useFileSelect'

interface Props {
  readonly targetChannelId?: number
}

const ACCEPT_CONTENT_TYPES = 'audio/*'

export const MediaUploader: React.FC<Props> = ({ targetChannelId }) => {
  const { upload, lastUploadedTrack, uploadQueue, uploadErrors } = useMediaUploader()
  const select = useFileSelect(ACCEPT_CONTENT_TYPES, (files) => files.forEach(upload))

  return null
}
