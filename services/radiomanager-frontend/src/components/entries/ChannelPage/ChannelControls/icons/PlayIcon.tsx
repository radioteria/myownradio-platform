interface Props {
  size: number
}

export const PlayIcon: React.FC<Props> = ({ size }) => {
  return (
    <svg width={size} viewBox="0 0 24 24" fill="none">
      <path d="M8 5V19L19 12L8 5Z" fill="currentColor" />
    </svg>
  )
}
