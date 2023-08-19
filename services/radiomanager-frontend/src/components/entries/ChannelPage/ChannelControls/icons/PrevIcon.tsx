interface Props {
  size: number
}

export const PrevIcon: React.FC<Props> = ({ size }) => {
  return (
    <svg width={size} viewBox="0 0 24 24" fill="none">
      <path d="M6 5H8V19H6V5Z" fill="currentColor" />
      <path d="M9 12L17 5V19L9 12Z" fill="currentColor" />
    </svg>
  )
}
