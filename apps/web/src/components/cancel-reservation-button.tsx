"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { responseMessage } from "@/lib/client-response";

export function CancelReservationButton({ reservationId }: { reservationId: string }) {
  const router = useRouter(); const [pending, setPending] = useState(false); const [error, setError] = useState("");
  return <div><button className="button danger" disabled={pending} onClick={async () => {
    if (!window.confirm("この予約をキャンセルしますか？")) return;
    setPending(true); setError("");
    const response = await fetch(`/api/me/reservations/${encodeURIComponent(reservationId)}`, { method: "DELETE" });
    if (!response.ok) setError(await responseMessage(response)); else router.refresh();
    setPending(false);
  }}>{pending ? "処理中…" : "予約をキャンセル"}</button>{error && <p className="error" role="alert">{error}</p>}</div>;
}
