'use client'

import { User, UserChannel } from '@/api'
import { MediaUploaderComponent, useMediaUploader } from '@/modules/MediaUploader'
import { LibraryLayout, Header } from '@/layouts/LibraryLayout'
import { Sidebar } from '@/components/Sidebar'
import { UploadList } from './UploadList'

interface Props {
  readonly user: User
  readonly userChannels: readonly UserChannel[]
}

export const UploadPage: React.FC<Props> = ({ user, userChannels }) => {
  const mediaUploader = useMediaUploader()

  return (
    <>
      <LibraryLayout
        header={<Header user={user} />}
        sidebar={<Sidebar channels={userChannels} activeItem={['upload']} />}
        content={
          <UploadList
            uploadQueue={mediaUploader.uploadQueue}
            uploadResults={mediaUploader.uploadResults}
          />
        }
        rightSidebar={null}
      />
      <MediaUploaderComponent />
    </>
  )
}

export const UploadPageWithProviders: React.FC<Props> = (props) => {
  return <UploadPage {...props} />
}
