interface Props {
  readonly channelId: number
}

export const StreamOverlay: React.FC<Props> = ({ channelId }) => {
  return (
    <div className={'bg-black aspect-video text-white flex items-center justify-center rounded-lg'}>
      OFFLINE
    </div>
  )
}
