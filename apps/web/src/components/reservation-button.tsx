"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { responseMessage } from "@/lib/client-response";

export function ReservationButton({ eventId, disabled = false }: { eventId: string; disabled?: boolean }) {
  const router = useRouter();
  const [pending, setPending] = useState(false);
  const [message, setMessage] = useState("");
  const [error, setError] = useState(false);

  async function reserve() {
    setPending(true); setMessage(""); setError(false);
    try {
      const response = await fetch(`/api/events/${encodeURIComponent(eventId)}/reservations`, { method: "POST" });
      if (response.status === 401) { router.push(`/login?next=/events/${encodeURIComponent(eventId)}`); return; }
      if (!response.ok) { setError(true); setMessage(await responseMessage(response)); return; }
      setMessage("予約が完了しました。マイ予約からチケットを確認できます。");
      router.refresh();
    } catch { setError(true); setMessage("ネットワークに接続できませんでした。"); }
    finally { setPending(false); }
  }
  return <div className="action-stack">
    <button className="button primary" onClick={reserve} disabled={disabled || pending}>{pending ? "予約処理中…" : disabled ? "満席です" : "このイベントを予約"}</button>
    {message && <p role="status" className={error ? "error" : "success"}>{message}</p>}
  </div>;
}
