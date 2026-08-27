import { authenticatedLaravelFetch, resourceData } from "./client";
import type { User } from "@/types/api";

export async function getCurrentUser(): Promise<User> {
  const payload = await authenticatedLaravelFetch<User | { data: User }>("/api/auth/me");
  return resourceData(payload);
}
