import { PlayerPage } from '@/views/PlayerPage/PlayerPage'

export default async function ChannelPlayer({
  params: { id },
  searchParams: { token },
}: {
  params: { id: string }
  searchParams: { token?: string }
}) {
  const channelId = Number(id)

  return <PlayerPage channelId={channelId} />
}
