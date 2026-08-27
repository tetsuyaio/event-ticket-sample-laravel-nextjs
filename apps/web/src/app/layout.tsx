import type { Metadata } from "next";
import { Header } from "@/components/header";
import "./globals.css";

export const metadata: Metadata = { title: "Ticket Studio", description: "イベントチケット予約サービス" };

export default function RootLayout({ children }: LayoutProps<"/">) {
  return <html lang="ja"><body><Header /><main>{children}</main><footer>© Ticket Studio — Great moments start here.</footer></body></html>;
}
