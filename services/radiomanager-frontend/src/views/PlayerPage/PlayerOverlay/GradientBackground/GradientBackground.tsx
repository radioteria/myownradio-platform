import { useEffect, useRef, useState } from 'react'

interface Props {
  readonly children?: React.ReactNode
  readonly initialColorIndex: number
}

const colors = [
  '#87CEEB', // Sky Blue
  '#006994', // Ocean Blue
  '#008080', // Teal
  '#4169E1', // Royal Blue
  '#191970', // Midnight Blue
  '#E6E6FA', // Lavender
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
  '#C0C0C0', // Silver
]

const getRandomColor = () => colors[Math.floor(Math.random() * colors.length)]

export const GradientBackground: React.FC<Props> = ({ children, initialColorIndex }) => {
  const [colorIndex1, setColorIndex1] = useState(initialColorIndex)
  const [colorIndex2, setColorIndex2] = useState(initialColorIndex)

  const [activeLayer, setActiveLayer] = useState(0)

  useEffect(() => {
    if (activeLayer === 0) {
      setColorIndex1((index) => (index + 1) % (colors.length - 3))
    } else {
      setColorIndex2((index) => (index + 1) % (colors.length - 3))
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
          background: linear-gradient(to bottom left, ${colors[colorIndex1]} 0%, transparent 75%),
            linear-gradient(to bottom right, ${colors[colorIndex1 + 1]} 0%, transparent 75%),
            linear-gradient(to top left, ${colors[colorIndex1 + 2]} 0%, transparent 75%),
            linear-gradient(to top right, ${colors[colorIndex1 + 3]} 0%, transparent 75%);
        }
        .gradient2 {
          opacity: ${activeLayer === 1 ? '1' : '0'};
          transition: opacity 45s linear;
          background: linear-gradient(to bottom left, ${colors[colorIndex2]} 0%, transparent 75%),
            linear-gradient(to bottom right, ${colors[colorIndex2 + 1]} 0%, transparent 75%),
            linear-gradient(to top left, ${colors[colorIndex2 + 2]} 0%, transparent 75%),
            linear-gradient(to top right, ${colors[colorIndex2 + 3]} 0%, transparent 75%);
        }
      `}</style>
      <div className={'w-full h-full gradient1 absolute'} />
      <div className={'w-full h-full gradient2 absolute'} />
      {children}
    </>
  )
}
