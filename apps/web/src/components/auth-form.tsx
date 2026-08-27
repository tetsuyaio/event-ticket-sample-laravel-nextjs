"use client";

import { FormEvent, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import { responseMessage } from "@/lib/client-response";

interface AuthFormProps {
  mode: "login" | "signup";
}

export function AuthForm({ mode }: AuthFormProps) {
  const router = useRouter();
  const [pending, setPending] = useState(false);
  const [error, setError] = useState("");

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setPending(true);
    setError("");
    const form = new FormData(event.currentTarget);
    const payload = {
      ...(mode === "signup" ? { name: String(form.get("name") ?? "") } : {}),
      email: String(form.get("email") ?? ""),
      password: String(form.get("password") ?? ""),
      ...(mode === "signup" ? { password_confirmation: String(form.get("password_confirmation") ?? "") } : {}),
    };
    try {
      const response = await fetch(`/api/auth/${mode}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      if (!response.ok) {
        setError(await responseMessage(response));
        return;
      }
      router.push("/events");
      router.refresh();
    } catch {
      setError("ネットワークに接続できませんでした。");
    } finally {
      setPending(false);
    }
  }

  const signup = mode === "signup";
  return (
    <form className="panel form-stack auth-card" onSubmit={submit} aria-label={signup ? "新規登録フォーム" : "ログインフォーム"}>
      <div>
        <p className="eyebrow">{signup ? "CREATE ACCOUNT" : "WELCOME BACK"}</p>
        <h1>{signup ? "アカウントを作成" : "ログイン"}</h1>
        <p className="muted">イベントを見つけて、数クリックでチケットを予約できます。</p>
      </div>
      {signup && <label>お名前<input name="name" autoComplete="name" required /></label>}
      <label>メールアドレス<input name="email" type="email" autoComplete="email" required /></label>
      <label>パスワード<input name="password" type="password" autoComplete={signup ? "new-password" : "current-password"} minLength={8} required /></label>
      {signup && <label>パスワード（確認）<input name="password_confirmation" type="password" autoComplete="new-password" minLength={8} required /></label>}
      {error && <p className="error" role="alert">{error}</p>}
      <button className="button primary" disabled={pending}>{pending ? "送信中…" : signup ? "登録する" : "ログイン"}</button>
      <p className="muted center">{signup ? "すでに登録済みですか？" : "アカウントをお持ちでないですか？"} <Link href={signup ? "/login" : "/signup"}>{signup ? "ログイン" : "新規登録"}</Link></p>
    </form>
  );
}
