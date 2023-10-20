import { getChannels, getSelf } from '@/api'
import { UploadPageWithProviders } from '@/views/UploadPage'

export default async function Upload() {
  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  const channels = await getChannels()

  return <UploadPageWithProviders user={self.user} userChannels={channels} />
}
