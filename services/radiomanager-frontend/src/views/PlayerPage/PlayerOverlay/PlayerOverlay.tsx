import { useMemo, useState } from 'react'
import { StreamPlayer } from '@/components/StreamPlayer'
import { DynamicFontSize } from '@/components/shared/DynamicFontSize/DynamicFontSize'
import { GradientBackground } from './GradientBackground/GradientBackground'
import { useNowPlaying } from '@/modules/NowPlaying'

interface Props {
  readonly channelId: number
  readonly muted?: boolean
}

export const PlayerOverlay: React.FC<Props> = ({ channelId, muted = false }) => {
  const [title, setTitle] = useState<string | null>(null)
  const { nowPlaying } = useNowPlaying()
  const time = nowPlaying?.time ?? 0

  return (
    <section className={'w-full h-full bg-black overflow-hidden relative'}>
      <GradientBackground timePosition={time}>
        {title && (
          <DynamicFontSize formula={({ width }) => `${width * 0.02}pt`}>
            <div className={'text-gray-100 bottom-0 left-0 p-[2%] absolute w-full truncate'}>
              <span className={'bg-morblue-800 px-[2%] py-[1%] inline-block'}>{title}</span>
            </div>
          </DynamicFontSize>
        )}
      </GradientBackground>
      <StreamPlayer muted={muted} channelId={channelId} onTrackChanged={setTitle} />
    </section>
  )
}
