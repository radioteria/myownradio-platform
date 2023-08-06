export default function Home() {
  return (
    <main className={"flex h-screen"}>
      <div className="flex-1 flex flex-col overflow-hidden">
        <nav className="flex h-24 bg-slate-800 text-gray-100">
          <div className="flex">TODO: Menu</div>
        </nav>
        <div className={"flex h-full"}>
          <aside className={"flex w-64 h-full bg-slate-300"}>
            TODO: Sidebar
          </aside>
          <div className={"flex flex-col w-full"}>TODO: Content</div>
        </div>
      </div>
    </main>
  );
}
