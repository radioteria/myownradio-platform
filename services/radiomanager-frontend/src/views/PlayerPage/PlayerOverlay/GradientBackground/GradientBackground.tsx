import { useEffect, useRef, useState } from 'react'

interface Props {
  readonly children?: React.ReactNode
}

const colors = [
  '#3B5998', // Darker Sky Blue
  '#004777', // Darker Ocean Blue
  '#004040', // Darker Teal
  '#1E487D', // Darker Royal Blue
  '#0F0E48', // Darker Midnight Blue
  '#B5B5D1', // Darker Lavender
  '#A347A6', // Darker Orchid
  '#B039B0', // Darker Violet
  '#9B819B', // Darker Plum
  '#CC00CC', // Darker Magenta
  '#CC0053', // Darker Rose
  '#CC6033', // Darker Coral
  '#CC9E99', // Darker Peach
  '#CC6600', // Darker Tangerine
  '#CC9900', // Darker Gold
  '#CCCC00', // Darker Lemon Yellow
  '#2D862D', // Darker Lime Green
  '#1B5C1B', // Darker Forest Green
  '#2D8F8F', // Darker Turquoise
  '#808080', // Darker Silver
]

export const GradientBackground: React.FC<Props> = ({ children }) => {
  const [color1, setColor1] = useState('#000000')
  const [color2, setColor2] = useState('#000000')
  const [color3, setColor3] = useState('#000000')
  const [color4, setColor4] = useState('#000000')

  const [color5, setColor5] = useState('#000000')
  const [color6, setColor6] = useState('#000000')
  const [color7, setColor7] = useState('#000000')
  const [color8, setColor8] = useState('#000000')

  const [activeLayer, setActiveLayer] = useState(0)

  useEffect(() => {
    if (activeLayer === 0) {
      setColor1(colors[Math.floor(Math.random() * colors.length)])
      setColor2(colors[Math.floor(Math.random() * colors.length)])
      setColor3(colors[Math.floor(Math.random() * colors.length)])
      setColor4(colors[Math.floor(Math.random() * colors.length)])
    } else {
      setColor5(colors[Math.floor(Math.random() * colors.length)])
      setColor6(colors[Math.floor(Math.random() * colors.length)])
      setColor7(colors[Math.floor(Math.random() * colors.length)])
      setColor8(colors[Math.floor(Math.random() * colors.length)])
    }

    let timeoutId = window.setTimeout(() => {
      setActiveLayer((layer) => 1 - layer)
    }, 1_000)

    return () => {
      window.clearTimeout(timeoutId)
    }
  }, [activeLayer])

  return (
    <>
      <style jsx>{`
        .gradient1 {
          opacity: ${activeLayer === 0 ? '1' : '0'};
          transition: opacity 1s linear;
          background: linear-gradient(to bottom left, ${color1}, transparent),
            linear-gradient(to bottom right, ${color2}, transparent),
            linear-gradient(to top left, ${color3}, transparent),
            linear-gradient(to top right, ${color4}, transparent);
        }
        .gradient2 {
          opacity: ${activeLayer === 1 ? '1' : '0'};
          transition: opacity 1s linear;
          background: linear-gradient(to bottom left, ${color5}, transparent),
            linear-gradient(to bottom right, ${color6}, transparent),
            linear-gradient(to top left, ${color7}, transparent),
            linear-gradient(to top right, ${color8}, transparent);
        }
      `}</style>
      <div className={'w-full h-full gradient1 absolute'} />
      <div className={'w-full h-full gradient2 absolute'} />
      {children}
    </>
  )
}
