import { NextRequest } from "next/server";
import { proxyToLaravel } from "@/lib/api/proxy";

export async function POST(request: NextRequest, context: RouteContext<"/api/events/[eventId]/reservations">) {
  const { eventId } = await context.params;
  return proxyToLaravel(request, `/api/events/${encodeURIComponent(eventId)}/reservations`, {
    authenticated: true,
  });
}
