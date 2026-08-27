import { NextRequest } from "next/server";
import { appendSearch, proxyToLaravel } from "@/lib/api/proxy";

export function GET(request: NextRequest) {
  return proxyToLaravel(request, appendSearch("/api/events", request));
}

export function POST(request: NextRequest) {
  return proxyToLaravel(request, "/api/events", { authenticated: true });
}
