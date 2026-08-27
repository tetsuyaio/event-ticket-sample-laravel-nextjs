import Link from "next/link";
import { DeleteEventButton } from "@/components/delete-event-button";
import { getAdminEvents } from "@/lib/api/events";
import { formatDate } from "@/lib/format";

export default async function AdminEventsPage() {
  const result = await getAdminEvents("per_page=100").catch(() => null);
  return <div className="page-shell section"><div className="section-heading"><div><p className="eyebrow">ADMIN CONSOLE</p><h1>イベント管理</h1></div><Link className="button primary" href="/admin/events/new">新規イベント</Link></div>{!result ? <div className="empty">管理データを表示できません。管理者としてログインしてください。</div> : <div className="table-wrap"><table><thead><tr><th>イベント</th><th>日時</th><th>ステータス</th><th>予約</th><th>操作</th></tr></thead><tbody>{result.data.map(event => <tr key={event.id}><td><strong>{event.title}</strong><small>{event.venue}</small></td><td>{formatDate(event.starts_at)}</td><td><span className={`status ${event.status.toLowerCase()}`}>{event.status}</span></td><td>{event.reserved_count} / {event.capacity}</td><td><div className="table-actions"><Link className="button secondary small" href={`/admin/events/${event.id}/edit`}>編集</Link><DeleteEventButton eventId={event.id} /></div></td></tr>)}</tbody></table></div>}</div>;
}
