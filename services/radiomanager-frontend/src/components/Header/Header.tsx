import cn from 'classnames'
import { User } from '@/api.types'

interface Props {
  user: User
}

export const Header: React.FC<Props> = ({ user }) => {
  return <div className={cn('flex')}>Hello, {user.name || user.login}</div>
}
