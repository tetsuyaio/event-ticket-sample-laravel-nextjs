import { NextRequest } from "next/server";
import { proxyToLaravel } from "@/lib/api/proxy";

function path(eventId: string): string {
  return `/api/events/${encodeURIComponent(eventId)}`;
}

export async function GET(request: NextRequest, context: RouteContext<"/api/events/[eventId]">) {
  const { eventId } = await context.params;
  return proxyToLaravel(request, path(eventId));
}

export async function PATCH(request: NextRequest, context: RouteContext<"/api/events/[eventId]">) {
  const { eventId } = await context.params;
  return proxyToLaravel(request, path(eventId), { authenticated: true });
}

export async function DELETE(request: NextRequest, context: RouteContext<"/api/events/[eventId]">) {
  const { eventId } = await context.params;
  return proxyToLaravel(request, path(eventId), { authenticated: true });
}
