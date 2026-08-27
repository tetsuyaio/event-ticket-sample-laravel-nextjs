import { notFound } from "next/navigation";
import { ReservationButton } from "@/components/reservation-button";
import { getEvent } from "@/lib/api/events";
import { formatDate } from "@/lib/format";

export default async function EventDetailPage({ params }: PageProps<"/events/[eventId]">) {
  const { eventId } = await params; const event = await getEvent(eventId).catch(() => null); if (!event) notFound();
  const remaining = Math.max(0, event.capacity - event.reserved_count);
  return <div className="page-shell section"><div className="detail-grid"><article><span className={`status ${event.status.toLowerCase()}`}>{event.status}</span><h1 className="detail-title">{event.title}</h1><p className="lead">{event.description}</p><dl className="detail-list"><div><dt>日時</dt><dd>{formatDate(event.starts_at)} — {formatDate(event.ends_at)}</dd></div><div><dt>会場</dt><dd>{event.venue}</dd></div><div><dt>残席</dt><dd>{remaining} / {event.capacity}</dd></div></dl></article><aside className="panel booking-box"><p className="eyebrow">RESERVE YOUR SEAT</p><h2>{remaining > 0 ? "席を確保しましょう" : "満席になりました"}</h2><p className="muted">決済はありません。予約後にチケット番号が発行されます。</p><ReservationButton eventId={event.id} disabled={event.status !== "PUBLISHED" || remaining === 0} /></aside></div></div>;
}
