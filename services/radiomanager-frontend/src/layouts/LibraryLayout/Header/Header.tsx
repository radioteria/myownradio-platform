import cn from 'classnames'
import { User } from '@/api'
import Local from 'next/font/local'
import Image from 'next/image'
import Logo from './logo.png'

const logoFont = Local({ src: '../../../fonts/MYRIAD/MYRIAD-THIN.otf' })

interface Props {
  user: User
}

export const Header: React.FC<Props> = ({ user }) => {
  return (
    <div className={cn('flex justify-between items-center h-full')}>
      <div className={cn('flex items-center pl-4')}>
        <Image src={Logo.src} alt={'logo'} width={40} height={40} />
        <span className={cn(logoFont.className, 'text-[32px] mt-1')}>RADIOTERIO</span>
      </div>
      <nav className={'pr-4'}>Hello, {user.name || user.login}</nav>
    </div>
  )
}
