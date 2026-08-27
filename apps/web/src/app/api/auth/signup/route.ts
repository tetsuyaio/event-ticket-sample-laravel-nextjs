import { NextRequest } from "next/server";
import { handleAuthRequest } from "@/lib/api/auth-handler";

export function POST(request: NextRequest) {
  return handleAuthRequest(request, "/api/auth/signup");
}
