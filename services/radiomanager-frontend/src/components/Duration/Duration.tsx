interface Props {
  millis: number
}

export const Duration: React.FC<Props> = ({ millis }) => {
  if (millis < 0) {
    return <>00:00</>
  }

  // Convert milliseconds to total seconds, minutes, and hours
  const totalSeconds = Math.floor(millis / 1000)
  const totalMinutes = Math.floor(totalSeconds / 60)
  const totalHours = Math.floor(totalMinutes / 60)

  // Calculate individual components
  const hours = totalHours
  const minutes = totalMinutes % 60
  const seconds = totalSeconds % 60

  // Convert each component to string and pad with 0 if necessary
  const paddedMinutes = String(minutes).padStart(2, '0')
  const paddedSeconds = String(seconds).padStart(2, '0')

  // Return the formatted string, with optional hours segment
  return hours > 0 ? (
    <>
      {hours}:{paddedMinutes}:{paddedSeconds}
    </>
  ) : (
    <>
      {paddedMinutes}:{paddedSeconds}
    </>
  )
}
