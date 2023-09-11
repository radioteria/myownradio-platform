import { SkeletonItem } from '@/components/common/TrackList/SkeletonItem'

interface Props {
  readonly length: number
}

export const SkeletonList: React.FC<Props> = ({ length }) => (
  <>
    {new Array(length).fill(null).map((_, index) => (
      <SkeletonItem key={index} />
    ))}
  </>
)
