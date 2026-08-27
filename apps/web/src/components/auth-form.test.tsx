import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { AuthForm } from "./auth-form";

const push = vi.fn(); const refresh = vi.fn();
vi.mock("next/navigation", () => ({ useRouter: () => ({ push, refresh }) }));

describe("AuthForm", () => {
  beforeEach(() => { vi.restoreAllMocks(); push.mockReset(); refresh.mockReset(); });
  it("入力値をログインBFFへ送り、成功時にイベントへ遷移する", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response(JSON.stringify({ data: { user: {} } }), { status: 200 }));
    render(<AuthForm mode="login" />); const user = userEvent.setup();
    await user.type(screen.getByLabelText("メールアドレス"), "user@example.com");
    await user.type(screen.getByLabelText("パスワード"), "password123");
    await user.click(screen.getByRole("button", { name: "ログイン" }));
    await waitFor(() => expect(push).toHaveBeenCalledWith("/events"));
    expect(fetchMock).toHaveBeenCalledWith("/api/auth/login", expect.objectContaining({ method: "POST", body: JSON.stringify({ email: "user@example.com", password: "password123" }) }));
  });

  it("APIエラーを表示する", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response(JSON.stringify({ message: "認証に失敗しました", code: "INVALID_CREDENTIALS", errors: null }), { status: 401 }));
    render(<AuthForm mode="login" />); const user = userEvent.setup();
    await user.type(screen.getByLabelText("メールアドレス"), "user@example.com"); await user.type(screen.getByLabelText("パスワード"), "password123"); await user.click(screen.getByRole("button", { name: "ログイン" }));
    expect(await screen.findByRole("alert")).toHaveTextContent("認証に失敗しました");
  });
});
