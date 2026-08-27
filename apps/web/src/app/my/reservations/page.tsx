import { CancelReservationButton } from "@/components/cancel-reservation-button";
import { getMyReservations } from "@/lib/api/reservations";
import { formatDate } from "@/lib/format";

export default async function ReservationsPage() {
  const result = await getMyReservations().catch(() => null);
  return <div className="page-shell section"><div className="section-heading"><div><p className="eyebrow">MY TICKETS</p><h1>予約一覧</h1></div></div>{!result ? <div className="empty">予約を表示できません。ログインしてください。</div> : result.data.length === 0 ? <div className="empty">予約はまだありません。</div> : <div className="reservation-list">{result.data.map(reservation => <article className="panel reservation" key={reservation.id}><div><span className={`status ${reservation.status.toLowerCase()}`}>{reservation.status}</span><h2>{reservation.event?.title ?? "イベント"}</h2><p>{reservation.event ? `${formatDate(reservation.event.starts_at)} · ${reservation.event.venue}` : `予約日 ${formatDate(reservation.reserved_at)}`}</p>{reservation.ticket && <p className="ticket-number">TICKET <strong>{reservation.ticket.ticket_number}</strong></p>}</div>{reservation.status === "RESERVED" && <CancelReservationButton reservationId={reservation.id} />}</article>)}</div>}</div>;
}
