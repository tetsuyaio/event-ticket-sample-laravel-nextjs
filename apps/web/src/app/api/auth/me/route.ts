import { NextRequest } from "next/server";
import { proxyToLaravel } from "@/lib/api/proxy";

export function GET(request: NextRequest) {
  return proxyToLaravel(request, "/api/auth/me", { authenticated: true });
}
