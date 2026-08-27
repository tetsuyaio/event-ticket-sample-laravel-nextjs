import { notFound } from "next/navigation";
import { EventForm } from "@/components/event-form";
import { getAdminEvent } from "@/lib/api/events";

export default async function EditEventPage({ params }: PageProps<"/admin/events/[eventId]/edit">) { const { eventId } = await params; const event = await getAdminEvent(eventId).catch(() => null); if (!event) notFound(); return <div className="page-shell section narrow"><p className="eyebrow">ADMIN CONSOLE</p><h1>イベントを編集</h1><EventForm event={event} /></div>; }
