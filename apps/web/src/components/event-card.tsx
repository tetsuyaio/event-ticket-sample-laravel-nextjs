import Link from "next/link";
import { formatDate } from "@/lib/format";
import type { Event } from "@/types/api";

export function EventCard({ event }: { event: Event }) {
  const remaining = Math.max(0, event.capacity - event.reserved_count);
  return <article className="event-card"><div className="event-card-top"><span className={`status ${event.status.toLowerCase()}`}>{event.status}</span><span className="remaining">残り {remaining} 席</span></div><p className="event-date">{formatDate(event.starts_at)}</p><h2><Link href={`/events/${event.id}`}>{event.title}</Link></h2><p className="event-description">{event.description}</p><div className="event-meta"><span>⌖ {event.venue}</span><span>{event.reserved_count} / {event.capacity} reserved</span></div></article>;
}
