"use client";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { responseMessage } from "@/lib/client-response";

export function DeleteEventButton({ eventId }: { eventId: string }) {
  const router = useRouter(); const [pending, setPending] = useState(false); const [error, setError] = useState("");
  return <div><button className="button danger small" disabled={pending} onClick={async () => { if (!window.confirm("このイベントを削除しますか？")) return; setPending(true); const response = await fetch(`/api/events/${encodeURIComponent(eventId)}`, { method: "DELETE" }); if (!response.ok) { setError(await responseMessage(response)); setPending(false); return; } router.refresh(); }}>{pending ? "削除中…" : "削除"}</button>{error && <span className="error">{error}</span>}</div>;
}
