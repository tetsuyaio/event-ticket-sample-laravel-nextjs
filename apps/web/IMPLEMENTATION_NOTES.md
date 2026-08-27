# Web 実装判断記録

- Laravel API の URL は server-only な `LARAVEL_API_URL`（例: `http://api:8000`。`/api` を含めない）だけで設定し、`NEXT_PUBLIC_*` には公開しない。
- Sanctum token は `event_ticket_token` HttpOnly Cookie に保存する。ログイン成功レスポンスは token を取り除いてブラウザへ返す。
- Cookie の Secure 属性は `AUTH_COOKIE_SECURE` で明示設定し、ローカル HTTP では `false`、HTTPS 環境では `true` にする。
- Server Component の読み取りは `src/lib/api`、ブラウザの変更操作は同一オリジン Route Handler を通す。
- Route Handler は認証情報と HTTP リクエスト／レスポンスの転送だけを行い、予約可否などのドメイン判断は Laravel に委ねる。
- Next.js 16 の production build は standalone 出力にする。検証環境で Turbopack の PostCSS worker がポート作成を拒否されたため、再現性を優先して build script は webpack を明示する。
