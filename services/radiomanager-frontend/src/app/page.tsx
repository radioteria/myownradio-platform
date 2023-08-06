import cn from "classnames";

export default function Home() {
  return (
    <main className={cn("flex h-screen")}>
      <div className={cn("flex-1 flex flex-col overflow-hidden")}>
        <nav className={cn("flex h-24 bg-slate-800 text-gray-100")}>
          <div className={cn("flex")}>TODO: Menu</div>
        </nav>
        <div className={cn("flex h-full")}>
          <aside className={cn("flex w-64 h-full bg-slate-300")}>
            TODO: Sidebar
          </aside>
          <div className={cn("flex flex-col w-full")}>TODO: Content</div>
        </div>
      </div>
    </main>
  );
}
