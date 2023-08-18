import { TrackItem, CurrentTrack } from './types'
import { TrackListItem } from '@/components/common/TrackList/TrackListItem'
import { useEffect, useState } from 'react'

interface ListItem {
  track: TrackItem
  isSelected: boolean
}

interface Props {
  tracks: readonly TrackItem[]
  currentTrack: CurrentTrack | null
}

const mapTracksToListItems = (tracks: readonly TrackItem[]) => {
  return tracks.map((track) => ({ track, isSelected: false }))
}

export const TrackList: React.FC<Props> = ({ tracks, currentTrack }) => {
  const [listItems, setListItems] = useState(mapTracksToListItems(tracks))
  const [selectionCursor, setSelectionCursor] = useState<null | number>(null)

  // Reset selection on tracks list update.
  // TODO Preserve selection
  useEffect(() => setListItems(mapTracksToListItems(tracks)), [tracks])

  const selectTrack = (selectIndex: number) => {
    setListItems((items) =>
      items.map((item, index) => {
        return selectIndex === index ? { ...item, isSelected: true } : item
      }),
    )
  }

  const discardTrack = (discardIndex: number) => {
    setListItems((items) =>
      items.map((item, index) => {
        return discardIndex === index ? { ...item, isSelected: false } : item
      }),
    )
  }

  const selectTo = (endIndex: number) => {
    const startIndex = selectionCursor ?? 0

    setListItems((items) =>
      items.map((item, index) => {
        return startIndex <= index && index <= endIndex ? { ...item, isSelected: true } : item
      }),
    )
  }

  const resetSelection = () => {
    setListItems((items) => items.map(({ track }) => ({ track, isSelected: false })))
    setSelectionCursor(null)
  }

  return (
    <ul>
      <li className="flex text-gray-500">
        <div className="pl-4 pr-2 py-4 w-12 flex-shrink-0 text-right">#</div>
        <div className="px-2 py-4 w-full">Title</div>
        <div className="px-2 py-4 w-full hidden xl:block">Album</div>
        <div className="px-2 py-4 w-20 flex-shrink-0 text-right">⏱</div>
        <div className="pl-2 pr-4 py-4 w-10 flex-shrink-0 text-right" />
      </li>

      {listItems.map((item, index) => {
        return (
          <TrackListItem
            key={index}
            track={item.track}
            currentTrack={currentTrack}
            index={index}
            onRemoveFromLibrary={() => {}}
            onRemoveFromChannel={() => {}}
          />
        )
      })}
    </ul>
  )
}
