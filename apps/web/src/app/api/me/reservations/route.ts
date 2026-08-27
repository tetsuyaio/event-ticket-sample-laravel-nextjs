import { NextRequest } from "next/server";
import { appendSearch, proxyToLaravel } from "@/lib/api/proxy";

export function GET(request: NextRequest) {
  return proxyToLaravel(request, appendSearch("/api/me/reservations", request), { authenticated: true });
}
