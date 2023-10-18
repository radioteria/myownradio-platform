import { StreamPlayer } from '@/components/StreamPlayer'
import { useState } from 'react'
import { DynamicFontSize } from '@/components/shared/DynamicFontSize/DynamicFontSize'

interface Props {
  readonly channelId: number
  readonly muted?: boolean
}

export const PlayerOverlay: React.FC<Props> = ({ channelId, muted = false }) => {
  const [title, setTitle] = useState<string | null>(null)

  return (
    <section className={'w-screen h-screen bg-black overflow-hidden relative'}>
      {title && (
        <DynamicFontSize
          className={'bottom-4 left-4 absolute'}
          formula={({ width }) => `${width * 0.02}pt`}
        >
          <span className={'text-gray-100 bg-morblue-800 px-4 py-2'}>{title}</span>
        </DynamicFontSize>
      )}
      <StreamPlayer channelId={channelId} onTrackChanged={setTitle} />
    </section>
  )
}
