interface Props {
  size: number
}

export const NextIcon: React.FC<Props> = ({ size }) => {
  return (
    <svg width={size} viewBox="0 0 24 24" fill="none">
      <path d="M16 5V19H18V5H16Z" fill="currentColor" />
      <path d="M15 12L8 5V19L15 12Z" fill="currentColor" />
    </svg>
  )
}
