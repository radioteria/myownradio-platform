'use client'

import { config } from '@/config'
import { useEffect, useRef, useState } from 'react'
import cn from 'classnames'
import { playAudio, stopAudio } from '@/utils/audio'

const BASE_URL = config.NEXT_PUBLIC_RADIOMANAGER_BACKEND_URL

interface Props {
  readonly channelId: number
}

export const StreamOverlay: React.FC<Props> = ({ channelId }) => {
  const audioUrl = `${BASE_URL}/getchunk/${channelId}`

  const [playing, setPlaying] = useState(false)
  // TODO Connect to WS to listen channel events
  // TODO Connect to scheduler to get now-playing data
  // TODO Integrate audio player to listen to audio
  // const { nowPlaying } = useNowPlaying(channelId)

  const playerRef = useRef<HTMLAudioElement | null>(null)

  useEffect(() => {
    const current = playerRef.current

    if (!current) {
      return
    }

    current.addEventListener('ended', () => {
      console.log('play again')
      playAudio(current, audioUrl)
    })

    current.addEventListener('error', () => {
      setTimeout(() => playAudio(current, audioUrl), 1_000)
    })
  }, [])

  useEffect(() => {
    const current = playerRef.current

    if (!current || !playing) {
      return
    }

    playAudio(current, audioUrl)

    return () => {
      stopAudio(current)
    }
  }, [playing, channelId])

  return (
    <>
      <div
        onClick={() => setPlaying((playing) => !playing)}
        className={cn([
          'flex items-center justify-center',
          'bg-black aspect-video text-white rounded-lg',
        ])}
      >
        OFFLINE
      </div>
      <audio ref={playerRef} />
    </>
  )
}
