import cn from "classnames";

export default function Home() {
  return (
    <main className={cn("flex h-screen")}>
      <div className={cn("flex-1 flex flex-col overflow-hidden")}>
        <nav className={cn("flex h-16 bg-slate-800 text-gray-100")}>
          <div className={cn("flex")}>TODO: Menu</div>
        </nav>
        <div className={cn("flex h-full")}>
          <aside
            className={cn(
              "flex w-64 h-full from-gray-300 to-gray-100 bg-gradient-to-b",
            )}
          >
            TODO: Sidebar
          </aside>
          <div className={cn("flex flex-col w-full")}>
            <div>
              <h3 className={cn("text-xl")}>All tracks</h3>
            </div>
          </div>
        </div>
      </div>
    </main>
  );
}
