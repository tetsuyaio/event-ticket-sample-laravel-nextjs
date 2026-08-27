import { NextRequest } from "next/server";
import { proxyToLaravel } from "@/lib/api/proxy";

function path(reservationId: string): string {
  return `/api/me/reservations/${encodeURIComponent(reservationId)}`;
}

export async function GET(request: NextRequest, context: RouteContext<"/api/me/reservations/[reservationId]">) {
  const { reservationId } = await context.params;
  return proxyToLaravel(request, path(reservationId), { authenticated: true });
}

export async function DELETE(request: NextRequest, context: RouteContext<"/api/me/reservations/[reservationId]">) {
  const { reservationId } = await context.params;
  return proxyToLaravel(request, path(reservationId), { authenticated: true });
}
