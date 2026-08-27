"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";

export function LogoutButton() {
  const router = useRouter();
  const [pending, setPending] = useState(false);
  return <button className="link-button" disabled={pending} onClick={async () => {
    setPending(true);
    await fetch("/api/auth/logout", { method: "POST" });
    router.push("/login");
    router.refresh();
  }}>{pending ? "ログアウト中…" : "ログアウト"}</button>;
}
