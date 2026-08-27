import { authenticatedLaravelFetch, laravelFetch, paginatedData, resourceData } from "./client";
import type { Event, Paginated } from "@/types/api";

export async function getEvents(search = "status=PUBLISHED"): Promise<Paginated<Event>> {
  const suffix = search ? `?${search}` : "";
  const payload = await laravelFetch<unknown>(`/api/events${suffix}`);
  return paginatedData<Event>(payload);
}

export async function getEvent(id: string): Promise<Event> {
  const payload = await laravelFetch<Event | { data: Event }>(`/api/events/${encodeURIComponent(id)}`);
  return resourceData(payload);
}

export async function getAdminEvent(id: string): Promise<Event> {
  const payload = await authenticatedLaravelFetch<Event | { data: Event }>(`/api/events/${encodeURIComponent(id)}`);
  return resourceData(payload);
}

export async function getAdminEvents(search = ""): Promise<Paginated<Event>> {
  const suffix = search ? `?${search}` : "";
  const payload = await authenticatedLaravelFetch<unknown>(`/api/events${suffix}`);
  return paginatedData<Event>(payload);
}
