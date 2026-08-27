import { authenticatedLaravelFetch, paginatedData } from "./client";
import type { Paginated, Reservation } from "@/types/api";

export async function getMyReservations(): Promise<Paginated<Reservation>> {
  const payload = await authenticatedLaravelFetch<unknown>(
    "/api/me/reservations",
  );
  return paginatedData<Reservation>(payload);
}
