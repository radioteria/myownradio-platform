interface Props {
  size: number
}

export const PauseIcon: React.FC<Props> = ({ size }) => {
  return (
    <svg width={size} viewBox="0 0 24 24" fill="none">
      <path d="M7 5H10V19H7V5Z" fill="currentColor" />
      <path d="M14 5H17V19H14V5Z" fill="currentColor" />
    </svg>
  )
}
