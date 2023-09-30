interface Props {
  size: number
}

export const AnimatedBars: React.FC<Props> = ({ size }) => {
  return (
    <>
      <svg
        width={size}
        style={{ display: 'inline' }}
        viewBox="0 0 15 16"
        xmlns="http://www.w3.org/2000/svg"
        fill={'currentcolor'}
      >
        <rect className="bar bar1" x="0" y="16" width="3" />
        <rect className="bar bar2" x="4" y="16" width="3" />
        <rect className="bar bar3" x="8" y="16" width="3" />
        <rect className="bar bar4" x="12" y="16" width="3" />
      </svg>
      <style jsx>{`
        .bar {
          animation: growBar 0.35s infinite alternate;
          height: 100%;
        }

        .bar1 {
          animation-delay: 0ms;
        }

        .bar2 {
          animation-delay: 300ms;
        }

        .bar3 {
          animation-delay: 200ms;
        }

        .bar4 {
          animation-delay: 500ms;
        }

        @keyframes growBar {
          from {
            transform: translateY(0);
          }
          to {
            transform: translateY(-16px);
          }
        }
      `}</style>
    </>
  )
}

export default AnimatedBars
