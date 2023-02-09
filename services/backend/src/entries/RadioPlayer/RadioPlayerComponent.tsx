import React, { useEffect, useRef } from 'react'

interface RadioPlayerComponentProps {
  src: null | string
  onBufferingStatusChange: (buffering: 'waiting' | 'playing') => void
  onBufferedAmountChange: (timeSeconds: number) => void
  onCurrentTimeChange: (timeSeconds: number) => void
}

export const RadioPlayerComponent: React.FC<RadioPlayerComponentProps> = ({
  src,
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

  const handleError = () => {
    playerRef.current?.play().catch()
  }

  if (!src) {
    return null
  }

  return (
    <audio
      ref={playerRef}
      loop
      autoPlay
      onError={handleError}
      onWaiting={() => onBufferingStatusChange('waiting')}
      onPlaying={() => onBufferingStatusChange('playing')}
    >
      <source src={src} />
      Your browser does not support the audio element.
    </audio>
  )
}
