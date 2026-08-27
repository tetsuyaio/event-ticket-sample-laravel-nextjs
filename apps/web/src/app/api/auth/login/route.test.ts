import { NextRequest } from "next/server";
import { afterEach, describe, expect, it, vi } from "vitest";
import { POST } from "./route";

describe("POST /api/auth/login", () => {
  afterEach(() => { vi.restoreAllMocks(); delete process.env.LARAVEL_API_URL; });
  it("Sanctum tokenをHttpOnly Cookieに保存し、レスポンスから除去する", async () => {
    process.env.LARAVEL_API_URL = "http://laravel.test";
    vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response(JSON.stringify({ data: { user: { id: "u1" }, token: "secret-token" } }), { status: 200, headers: { "Content-Type": "application/json" } }));
    const request = new NextRequest("http://next.test/api/auth/login", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ email: "u@example.com", password: "password123" }) });
    const response = await POST(request); const body = await response.json();
    expect(response.status).toBe(200); expect(response.headers.get("set-cookie")).toContain("event_ticket_token=secret-token"); expect(response.headers.get("set-cookie")).toContain("HttpOnly"); expect(JSON.stringify(body)).not.toContain("secret-token");
  });
  it("tokenがない成功レスポンスを502にする", async () => {
    process.env.LARAVEL_API_URL = "http://laravel.test"; vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response(JSON.stringify({ data: { user: { id: "u1" } } }), { status: 200 }));
    const response = await POST(new NextRequest("http://next.test/api/auth/login", { method: "POST", body: "{}" })); expect(response.status).toBe(502); expect(await response.json()).toMatchObject({ code: "INVALID_UPSTREAM_RESPONSE" });
  });
});
