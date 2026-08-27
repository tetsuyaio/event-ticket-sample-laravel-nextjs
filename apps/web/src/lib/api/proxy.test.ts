import { NextRequest } from "next/server";
import { afterEach, describe, expect, it, vi } from "vitest";
import { proxyToLaravel } from "./proxy";

describe("proxyToLaravel", () => {
  afterEach(() => { vi.restoreAllMocks(); delete process.env.LARAVEL_API_URL; });
  it("認証CookieをBearer tokenとしてのみ転送する", async () => {
    process.env.LARAVEL_API_URL = "http://laravel.test"; const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response(JSON.stringify({ data: [] }), { status: 200 }));
    const request = new NextRequest("http://next.test/api/me/reservations", { headers: { cookie: "event_ticket_token=top-secret" } }); const response = await proxyToLaravel(request, "/api/me/reservations", { authenticated: true });
    expect(response.status).toBe(200); const init = fetchMock.mock.calls[0][1]; const headers = new Headers(init?.headers); expect(headers.get("Authorization")).toBe("Bearer top-secret"); expect(headers.get("cookie")).toBeNull();
  });
  it("CookieがなければLaravelを呼ばず401を返す", async () => {
    process.env.LARAVEL_API_URL = "http://laravel.test"; const fetchMock = vi.spyOn(globalThis, "fetch"); const response = await proxyToLaravel(new NextRequest("http://next.test/api/auth/me"), "/api/auth/me", { authenticated: true }); expect(response.status).toBe(401); expect(fetchMock).not.toHaveBeenCalled();
  });
});
