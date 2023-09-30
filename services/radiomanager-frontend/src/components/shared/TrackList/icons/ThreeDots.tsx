interface Props {
  size: number
}

export const ThreeDots: React.FC<Props> = ({ size }) => {
  return (
    <svg width={size} style={{ display: 'inline' }} viewBox="0 0 28 24">
      <circle cx={4} cy={12} r={3} fill="currentColor" />
      <circle cx={14} cy={12} r={3} fill="currentColor" />
      <circle cx={24} cy={12} r={3} fill="currentColor" />
    </svg>
  )
}
