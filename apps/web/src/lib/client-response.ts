import type { ApiErrorBody } from "@/types/api";

export async function responseMessage(response: Response): Promise<string> {
  const body: unknown = await response.json().catch(() => null);
  if (typeof body === "object" && body !== null && "message" in body && typeof body.message === "string") {
    const error = body as Partial<ApiErrorBody>;
    if (error.errors) {
      const first = Object.values(error.errors).flat()[0];
      if (first) return first;
    }
    return body.message;
  }
  return "処理に失敗しました。時間をおいて再度お試しください。";
}
