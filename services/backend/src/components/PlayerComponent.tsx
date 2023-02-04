import React, { useEffect, useRef } from 'react'

interface PlayerComponentProps {
  src: null | string
  onError: () => void
  onBufferingStatusChange: (buffering: 'waiting' | 'playing') => void
  onBufferedAmountChange: (timeSeconds: number) => void
  onCurrentTimeChange: (timeSeconds: number) => void
}

export const PlayerComponent: React.FC<PlayerComponentProps> = ({
  src,
  onError,
  onBufferingStatusChange,
  onBufferedAmountChange,
  onCurrentTimeChange,
}) => {
  const playerRef = useRef<HTMLMediaElement>(null)

  useEffect(() => {
    const updateBufferedAmount = () => {
      if (!playerRef.current) {
        return
      }

      if (playerRef.current.buffered.length > 0) {
        onBufferedAmountChange(
          playerRef.current.buffered.end(playerRef.current.buffered.length - 1),
        )
      }
      onCurrentTimeChange(playerRef.current.currentTime)
    }

    const intervalId = window.setInterval(updateBufferedAmount, 1000)

    return () => window.clearInterval(intervalId)
  }, [src])

  if (!src) {
    return null
  }

  return (
    <audio
      ref={playerRef}
      loop
      autoPlay
      onError={() => onError()}
      onWaiting={() => onBufferingStatusChange('waiting')}
      onPlaying={() => onBufferingStatusChange('playing')}
    >
      <source src={src} />
      Your browser does not support the audio element.
    </audio>
  )
}
