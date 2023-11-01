import { useColorsAnimation } from './hooks/useColorsAnimation'

interface Props {
  readonly children?: React.ReactNode
  readonly timePosition: number
}

export const GradientBackground: React.FC<Props> = ({ children, timePosition }) => {
  const colorsState = useColorsAnimation(timePosition)

  return (
    <>
      <style jsx>{`
        .gradient1 {
          opacity: ${colorsState.frameAOpacity};
          transition: opacity 250ms linear;
          background: linear-gradient(
              to bottom left,
              ${colorsState.frameAColors[0]} 0%,
              transparent 75%
            ),
            linear-gradient(to bottom right, ${colorsState.frameAColors[1]} 0%, transparent 75%),
            linear-gradient(to top left, ${colorsState.frameAColors[2]} 0%, transparent 75%),
            linear-gradient(to top right, ${colorsState.frameAColors[3]} 0%, transparent 75%);
        }
        .gradient2 {
          opacity: ${colorsState.frameBOpacity};
          transition: opacity 250ms linear;
          background: linear-gradient(
              to bottom left,
              ${colorsState.frameBColors[0]} 0%,
              transparent 75%
            ),
            linear-gradient(to bottom right, ${colorsState.frameBColors[1]} 0%, transparent 75%),
            linear-gradient(to top left, ${colorsState.frameBColors[2]} 0%, transparent 75%),
            linear-gradient(to top right, ${colorsState.frameBColors[3]} 0%, transparent 75%);
        }
      `}</style>
      <div className={'w-full h-full gradient1 absolute'} />
      <div className={'w-full h-full gradient2 absolute'} />
      {children}
    </>
  )
}
