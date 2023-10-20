import { useEffect, useRef, useState } from 'react'

interface Props {
  readonly children?: React.ReactNode
}

const colors = [
  '#87CEEB', // Sky Blue
  '#006994', // Ocean Blue
  '#008080', // Teal
  '#4169E1', // Royal Blue
  '#191970', // Midnight Blue
  '#DA70D6', // Orchid
  '#EE82EE', // Violet
  '#DDA0DD', // Plum
  '#FF00FF', // Magenta
  '#FF007F', // Rose
  '#FF7F50', // Coral
  '#FFDAB9', // Peach
  '#FFA500', // Tangerine
  '#FFD700', // Gold
  '#FFFF00', // Lemon Yellow
  '#32CD32', // Lime Green
  '#228B22', // Forest Green
  '#40E0D0', // Turquoise
]

const getRandomColor = () => colors[Math.floor(Math.random() * colors.length)]

export const GradientBackground: React.FC<Props> = ({ children }) => {
  const [color1, setColor1] = useState(() => getRandomColor())
  const [color2, setColor2] = useState(() => getRandomColor())
  const [color3, setColor3] = useState(() => getRandomColor())
  const [color4, setColor4] = useState(() => getRandomColor())

  const [color5, setColor5] = useState(() => getRandomColor())
  const [color6, setColor6] = useState(() => getRandomColor())
  const [color7, setColor7] = useState(() => getRandomColor())
  const [color8, setColor8] = useState(() => getRandomColor())

  const [activeLayer, setActiveLayer] = useState(0)

  useEffect(() => {
    if (activeLayer === 0) {
      setColor1(getRandomColor())
      setColor2(getRandomColor())
      setColor3(getRandomColor())
      setColor4(getRandomColor())
    } else {
      setColor5(getRandomColor())
      setColor6(getRandomColor())
      setColor7(getRandomColor())
      setColor8(getRandomColor())
    }

    let timeoutId = window.setTimeout(() => {
      setActiveLayer((layer) => 1 - layer)
    }, 60_000)

    return () => {
      window.clearTimeout(timeoutId)
    }
  }, [activeLayer])

  return (
    <>
      <style jsx>{`
        .gradient1 {
          opacity: ${activeLayer === 0 ? '1' : '0'};
          transition: opacity 45s linear;
          background: linear-gradient(to bottom left, ${color1} 0%, transparent 75%),
            linear-gradient(to bottom right, ${color2} 0%, transparent 75%),
            linear-gradient(to top left, ${color3} 0%, transparent 75%),
            linear-gradient(to top right, ${color4} 0%, transparent 75%);
        }
        .gradient2 {
          opacity: ${activeLayer === 1 ? '1' : '0'};
          transition: opacity 45s linear;
          background: linear-gradient(to bottom left, ${color5} 0%, transparent 75%),
            linear-gradient(to bottom right, ${color6} 0%, transparent 75%),
            linear-gradient(to top left, ${color7} 0%, transparent 75%),
            linear-gradient(to top right, ${color8} 0%, transparent 75%);
        }
      `}</style>
      <div className={'w-full h-full gradient1 absolute'} />
      <div className={'w-full h-full gradient2 absolute'} />
      {children}
    </>
  )
}
