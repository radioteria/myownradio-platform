import { StreamPlayer } from '@/components/StreamPlayer'
import { useState } from 'react'
import { DynamicFontSize } from '@/components/shared/DynamicFontSize/DynamicFontSize'
import { GradientBackground } from '@/views/PlayerPage/PlayerOverlay/GradientBackground/GradientBackground'

interface Props {
  readonly channelId: number
  readonly muted?: boolean
}

export const PlayerOverlay: React.FC<Props> = ({ channelId, muted = false }) => {
  const [title, setTitle] = useState<string | null>(null)

  return (
    <section className={'w-screen h-screen bg-black overflow-hidden relative'}>
      <GradientBackground>
        {title && (
          <DynamicFontSize formula={({ width }) => `${width * 0.02}pt`}>
            <div className={'text-gray-100 bottom-0 left-0 p-4 absolute w-full truncate'}>
              <span className={'bg-morblue-800 px-4 py-2'}>{title}</span>
            </div>
          </DynamicFontSize>
        )}
      </GradientBackground>
      <StreamPlayer channelId={channelId} onTrackChanged={setTitle} />
    </section>
  )
}
