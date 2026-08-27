"use client";

import { FormEvent, useState } from "react";
import { useRouter } from "next/navigation";
import { responseMessage } from "@/lib/client-response";
import { toDateTimeLocal } from "@/lib/format";
import type { Event, EventInput, EventStatus } from "@/types/api";

export function EventForm({ event }: { event?: Event }) {
  const router = useRouter(); const [pending, setPending] = useState(false); const [error, setError] = useState("");
  async function submit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault(); setPending(true); setError(""); const form = new FormData(e.currentTarget);
    const input: EventInput = { title: String(form.get("title")), description: String(form.get("description")), venue: String(form.get("venue")), starts_at: new Date(String(form.get("starts_at"))).toISOString(), ends_at: new Date(String(form.get("ends_at"))).toISOString(), capacity: Number(form.get("capacity")), status: String(form.get("status")) as EventStatus };
    const response = await fetch(event ? `/api/events/${encodeURIComponent(event.id)}` : "/api/events", { method: event ? "PATCH" : "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(input) });
    if (!response.ok) { setError(await responseMessage(response)); setPending(false); return; }
    router.push("/admin/events"); router.refresh();
  }
  return <form className="panel form-stack wide-form" onSubmit={submit}><label>イベント名<input name="title" defaultValue={event?.title} required /></label><label>説明<textarea name="description" rows={5} defaultValue={event?.description} required /></label><div className="form-grid"><label>会場<input name="venue" defaultValue={event?.venue} required /></label><label>定員<input name="capacity" type="number" min={1} defaultValue={event?.capacity ?? 100} required /></label><label>開始日時<input name="starts_at" type="datetime-local" defaultValue={event ? toDateTimeLocal(event.starts_at) : ""} required /></label><label>終了日時<input name="ends_at" type="datetime-local" defaultValue={event ? toDateTimeLocal(event.ends_at) : ""} required /></label><label>ステータス<select name="status" defaultValue={event?.status ?? "DRAFT"}>{(["DRAFT", "PUBLISHED", "CLOSED", "CANCELLED"] as EventStatus[]).map(status => <option key={status}>{status}</option>)}</select></label></div>{error && <p className="error" role="alert">{error}</p>}<div className="button-row"><button className="button primary" disabled={pending}>{pending ? "保存中…" : "保存する"}</button><button className="button secondary" type="button" onClick={() => router.back()}>戻る</button></div></form>;
}
