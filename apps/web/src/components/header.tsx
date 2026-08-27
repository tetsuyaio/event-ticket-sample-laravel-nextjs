import Link from "next/link";
import { getCurrentUser } from "@/lib/api/auth";
import { LogoutButton } from "./logout-button";

export async function Header() {
  const user = await getCurrentUser().catch(() => null);
  return <header className="site-header"><div className="header-inner">
    <Link href="/" className="brand"><span className="brand-mark">T</span> TICKET STUDIO</Link>
    <nav aria-label="メインナビゲーション">
      <Link href="/events">イベント</Link>
      {user && <Link href="/my/reservations">予約</Link>}
      {user?.role === "ADMIN" && <Link href="/admin/events">管理</Link>}
      {user ? <LogoutButton /> : <Link href="/login">ログイン</Link>}
    </nav>
  </div></header>;
}
