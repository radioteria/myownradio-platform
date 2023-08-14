import exp from 'constants'

interface Props {
  size: number
}

export const ThreeDots: React.FC<Props> = ({ size }) => {
  return (
    <svg width={size} style={{ display: 'inline' }} viewBox="0 0 24 24">
      <circle cx={12} cy={12} r={3} fill="currentColor" />
      <circle cx={4} cy={12} r={3} fill="currentColor" />
      <circle cx={20} cy={12} r={3} fill="currentColor" />
    </svg>
  )
}
