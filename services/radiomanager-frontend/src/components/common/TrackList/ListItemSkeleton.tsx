export const ListItemSkeleton: React.FC = () => (
  <li className="flex items-center border-gray-800 h-12 relative cursor-pointer select-none">
    <div className="p-2 pl-4 w-14 flex-shrink-0 z-10 text-right">
      <div className={'w-4 h-5 bg-gray-300 rounded-md inline-block'} />
    </div>
    <div className="p-2 w-full z-10 min-w-0">
      <div className={'w-[75%] h-4 bg-gray-300 rounded-md mb-1'} />
      <div className={'w-[50%] h-3 bg-gray-300 rounded-md'} />
    </div>
    <div className="px-2 py-4 w-full hidden xl:block">
      <div className={'w-[75%] h-4 bg-gray-300 rounded-md inline-block'} />
    </div>
    <div className="p-2 w-20 flex-shrink-0 text-right z-10">
      <div className={'w-10 h-5 bg-gray-300 rounded-md inline-block'} />
    </div>
    <div className="pl-2 pr-4 py-4 w-10 flex-shrink-0 text-right" />
  </li>
)
