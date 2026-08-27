import "server-only";

import { NextRequest, NextResponse } from "next/server";
import { AUTH_COOKIE_NAME, authCookieOptions, laravelApiUrl } from "./client";

type JsonObject = Record<string, unknown>;

function isObject(value: unknown): value is JsonObject {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}

function tokenFrom(payload: unknown): string | undefined {
  if (!isObject(payload)) return undefined;
  if (typeof payload.token === "string") return payload.token;
  if (typeof payload.access_token === "string") return payload.access_token;
  return isObject(payload.data) ? tokenFrom(payload.data) : undefined;
}

function withoutToken(payload: unknown): unknown {
  if (!isObject(payload)) return payload;
  const rest = { ...payload };
  delete rest.token;
  delete rest.access_token;
  return isObject(rest.data) ? { ...rest, data: withoutToken(rest.data) } : rest;
}

export async function handleAuthRequest(request: NextRequest, path: string): Promise<NextResponse> {
  let upstream: Response;
  try {
    upstream = await fetch(laravelApiUrl(path), {
      method: "POST",
      headers: { Accept: "application/json", "Content-Type": request.headers.get("content-type") ?? "application/json" },
      body: await request.text(),
      cache: "no-store",
    });
  } catch {
    return NextResponse.json(
      { message: "Backend service is unavailable", code: "UPSTREAM_UNAVAILABLE", errors: null },
      { status: 502 },
    );
  }

  const payload: unknown = await upstream.json().catch(() => null);
  if (!upstream.ok) return NextResponse.json(payload, { status: upstream.status });
  const token = tokenFrom(payload);
  if (!token) {
    return NextResponse.json(
      { message: "Backend did not return an authentication token", code: "INVALID_UPSTREAM_RESPONSE", errors: null },
      { status: 502 },
    );
  }
  const response = NextResponse.json(withoutToken(payload), { status: upstream.status });
  response.cookies.set(AUTH_COOKIE_NAME, token, authCookieOptions(60 * 60 * 24 * 30));
  return response;
}
