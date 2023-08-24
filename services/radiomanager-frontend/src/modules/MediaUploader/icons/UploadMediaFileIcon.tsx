import * as React from 'react'

export const UploadMediaFileIcon: React.FC<React.SVGProps<SVGSVGElement>> = (props) => (
  <svg viewBox="0 0 24 22" fill="none" {...props}>
    <rect x="3" y="3" width="18" height="18" stroke="currentColor" strokeWidth="2" />
    <path d="M12 15V7" stroke="currentColor" strokeWidth="2" />
    <path d="M9 10L12 7L15 10" stroke="currentColor" strokeWidth="2" />
    <circle cx="12" cy="15" r="1" fill="currentColor" />
  </svg>
)
