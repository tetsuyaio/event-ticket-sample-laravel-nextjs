import { NextRequest, NextResponse } from "next/server";
import { AUTH_COOKIE_NAME, authCookieOptions } from "@/lib/api/client";
import { proxyToLaravel } from "@/lib/api/proxy";

export async function POST(request: NextRequest): Promise<NextResponse> {
  const response = await proxyToLaravel(request, "/api/auth/logout", { authenticated: true });
  response.cookies.set(AUTH_COOKIE_NAME, "", authCookieOptions(0));
  return response;
}
