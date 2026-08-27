import { EventCard } from "@/components/event-card";
import { getEvents } from "@/lib/api/events";

export default async function EventsPage({ searchParams }: PageProps<"/events">) {
  const params = await searchParams; const query = new URLSearchParams();
  if (typeof params.keyword === "string") query.set("keyword", params.keyword);
  query.set("status", "PUBLISHED"); if (typeof params.sort === "string") query.set("sort", params.sort); if (typeof params.page === "string") query.set("page", params.page);
  const result = await getEvents(query.toString()).catch(() => null);
  return <div className="page-shell section"><div className="section-heading"><div><p className="eyebrow">UPCOMING EVENTS</p><h1>イベントを見つける</h1></div><form className="search" action="/events"><input name="keyword" defaultValue={typeof params.keyword === "string" ? params.keyword : ""} placeholder="キーワードで検索" /><button className="button secondary">検索</button></form></div>{!result ? <div className="empty">イベント情報を取得できませんでした。APIの接続設定をご確認ください。</div> : result.data.length === 0 ? <div className="empty">条件に一致するイベントはありません。</div> : <div className="event-grid">{result.data.map(event => <EventCard key={event.id} event={event} />)}</div>}</div>;
}
