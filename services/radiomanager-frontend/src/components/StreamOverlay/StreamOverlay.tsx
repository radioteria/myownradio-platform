interface Props {
  readonly channelId: number
}

export const StreamOverlay: React.FC<Props> = ({ channelId }) => {
  // TODO Connect to WS to listen channel events
  // TODO Connect to scheduler to get now-playing data
  // TODO Integrate audio player to listen to audio

  return (
    <div className={'bg-black aspect-video text-white flex items-center justify-center rounded-lg'}>
      OFFLINE
    </div>
  )
}
