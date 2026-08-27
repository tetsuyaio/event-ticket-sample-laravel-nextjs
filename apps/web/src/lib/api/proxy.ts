import "server-only";

import { NextRequest, NextResponse } from "next/server";
import { AUTH_COOKIE_NAME, laravelApiUrl } from "./client";

interface ProxyOptions {
  authenticated?: boolean;
  method?: string;
}

export async function proxyToLaravel(
  request: NextRequest,
  path: string,
  options: ProxyOptions = {},
): Promise<NextResponse> {
  const headers = new Headers({ Accept: "application/json" });
  const contentType = request.headers.get("content-type");
  if (contentType) headers.set("Content-Type", contentType);
  if (options.authenticated) {
    const token = request.cookies.get(AUTH_COOKIE_NAME)?.value;
    if (!token) {
      return NextResponse.json(
        { message: "Authentication required", code: "UNAUTHORIZED", errors: null },
        { status: 401 },
      );
    }
    headers.set("Authorization", `Bearer ${token}`);
  }

  const method = options.method ?? request.method;
  const hasBody = method !== "GET" && method !== "HEAD";
  let upstream: Response;
  try {
    upstream = await fetch(laravelApiUrl(path), {
      method,
      headers,
      body: hasBody ? await request.text() : undefined,
      cache: "no-store",
    });
  } catch {
    return NextResponse.json(
      { message: "Backend service is unavailable", code: "UPSTREAM_UNAVAILABLE", errors: null },
      { status: 502 },
    );
  }

  const responseHeaders = new Headers();
  responseHeaders.set("Content-Type", upstream.headers.get("content-type") ?? "application/json");
  const requestId = upstream.headers.get("x-request-id");
  if (requestId) responseHeaders.set("X-Request-ID", requestId);
  return new NextResponse(upstream.status === 204 ? null : await upstream.arrayBuffer(), {
    status: upstream.status,
    headers: responseHeaders,
  });
}

export function appendSearch(path: string, request: NextRequest): string {
  return `${path}${request.nextUrl.search}`;
}
