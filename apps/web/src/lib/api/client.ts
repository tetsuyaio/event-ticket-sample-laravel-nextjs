import "server-only";

import { cookies } from "next/headers";
import type { ApiErrorBody, Paginated } from "@/types/api";

export const AUTH_COOKIE_NAME = "event_ticket_token";

export function authCookieOptions(maxAge: number) {
  const secure = process.env.AUTH_COOKIE_SECURE === undefined
    ? process.env.NODE_ENV === "production"
    : process.env.AUTH_COOKIE_SECURE === "true";
  return { httpOnly: true, secure, sameSite: "lax" as const, path: "/", maxAge };
}

export class ApiClientError extends Error {
  constructor(
    public readonly status: number,
    public readonly body: ApiErrorBody,
  ) {
    super(body.message);
    this.name = "ApiClientError";
  }
}

export function laravelApiUrl(path: string): string {
  const baseUrl = process.env.LARAVEL_API_URL;
  if (!baseUrl) {
    throw new Error("LARAVEL_API_URL is not configured");
  }
  return `${baseUrl.replace(/\/$/, "")}${path.startsWith("/") ? path : `/${path}`}`;
}

function isApiErrorBody(value: unknown): value is ApiErrorBody {
  return typeof value === "object" && value !== null && "message" in value && typeof value.message === "string";
}

export async function laravelFetch<T>(
  path: string,
  init: RequestInit = {},
  token?: string,
): Promise<T> {
  const headers = new Headers(init.headers);
  headers.set("Accept", "application/json");
  if (init.body && !headers.has("Content-Type")) headers.set("Content-Type", "application/json");
  if (token) headers.set("Authorization", `Bearer ${token}`);

  const response = await fetch(laravelApiUrl(path), {
    ...init,
    headers,
    cache: "no-store",
  });
  const payload: unknown = response.status === 204 ? null : await response.json().catch(() => null);
  if (!response.ok) {
    const fallback: ApiErrorBody = { message: "API request failed", code: "UPSTREAM_ERROR", errors: null };
    throw new ApiClientError(response.status, isApiErrorBody(payload) ? { ...fallback, ...payload } : fallback);
  }
  return payload as T;
}

export async function authenticatedLaravelFetch<T>(path: string, init: RequestInit = {}): Promise<T> {
  const token = (await cookies()).get(AUTH_COOKIE_NAME)?.value;
  return laravelFetch<T>(path, init, token);
}

export function resourceData<T>(payload: T | { data: T }): T {
  return typeof payload === "object" && payload !== null && "data" in payload
    ? (payload as { data: T }).data
    : payload;
}

export function paginatedData<T>(payload: unknown): Paginated<T> {
  if (Array.isArray(payload)) return { data: payload as T[] };
  if (typeof payload === "object" && payload !== null && "data" in payload) {
    const data = (payload as { data: unknown }).data;
    if (Array.isArray(data)) return payload as Paginated<T>;
    return paginatedData<T>(data);
  }
  throw new Error("Invalid paginated API response");
}
