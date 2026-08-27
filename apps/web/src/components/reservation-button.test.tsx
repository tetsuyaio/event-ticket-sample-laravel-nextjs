import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { ReservationButton } from "./reservation-button";

const push = vi.fn(); const refresh = vi.fn();
vi.mock("next/navigation", () => ({ useRouter: () => ({ push, refresh }) }));

describe("ReservationButton", () => {
  beforeEach(() => { vi.restoreAllMocks(); push.mockReset(); refresh.mockReset(); });
  it("BFFへ予約を作成して完了を通知する", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response(JSON.stringify({ data: { id: "r1" } }), { status: 201 }));
    render(<ReservationButton eventId="event/1" />); await userEvent.click(screen.getByRole("button", { name: "このイベントを予約" }));
    expect(await screen.findByRole("status")).toHaveTextContent("予約が完了しました");
    expect(fetchMock).toHaveBeenCalledWith("/api/events/event%2F1/reservations", { method: "POST" }); expect(refresh).toHaveBeenCalled();
  });
  it("未認証ならログイン画面へ遷移する", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(new Response(null, { status: 401 })); render(<ReservationButton eventId="e1" />); await userEvent.click(screen.getByRole("button"));
    expect(push).toHaveBeenCalledWith("/login?next=/events/e1");
  });
});
