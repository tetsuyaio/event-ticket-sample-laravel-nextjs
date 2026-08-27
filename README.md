# Event Ticket Reservation App

Laravel 13 と Next.js App Router で構築したイベント・チケット予約のリファレンス実装です。ブラウザは Laravel API を直接呼ばず、Next.js Route Handlers を BFF として利用します。

```text
Browser -> Next.js (App Router / BFF) -> Laravel REST API -> PostgreSQL
```

## 主な機能

- メールアドレス・パスワード認証と Laravel Sanctum token
- HttpOnly Cookie を利用する Next.js BFF
- USER / ADMIN の Policy ベース認可
- イベント CRUD、公開状態、検索、絞り込み、ソート、ページング
- 予約・チケット発行・キャンセル
- PostgreSQL row lock と transaction による定員超過防止
- Form Request、API Resource、共通エラー応答、request ID ログ
- OpenAPI 仕様と Laravel / Next.js の主要テスト

Terraform / AWS infrastructure は今回の実装対象外です。

## 起動

Docker Desktop を起動してから、リポジトリ直下で次を実行します。

```bash
docker compose up --build
```

- Web: http://localhost:3000
- Laravel API: http://localhost:8000/api
- PostgreSQL: localhost:5432

初回起動時に migration が自動実行されます。サンプルデータを投入する場合:

```bash
docker compose run --rm api php artisan db:seed
```

Seeder の開発用アカウント:

- Admin: `admin@example.com` / `password`
- User: `user@example.com` / `password`

これらはローカル学習用です。本番環境では Seeder の認証情報を使用しないでください。

## 開発コマンド

```bash
make test       # API と Web のテスト
make lint       # Pint と ESLint
make migrate    # migration
make seed       # Seeder
make down       # コンテナ停止
```

Web だけをホストで動かす場合は `apps/web/.env.local` を作成します。

```dotenv
LARAVEL_API_URL=http://localhost:8000
AUTH_COOKIE_SECURE=false
```

その後 `cd apps/web && pnpm dev` を実行します。PHP / Composer はホストに不要で、Laravel のコマンドは Docker コンテナ内で実行できます。

## API

OpenAPI 定義は [apps/api/docs/openapi.yaml](apps/api/docs/openapi.yaml) を参照してください。Laravel API の認証付き endpoint は `Authorization: Bearer <token>` を受け付けます。ブラウザ UI では token を直接扱わず、BFF が HttpOnly Cookie と Bearer token の変換を担当します。

## 設計資料

- [仕様](docs/spec.md)
- [実装判断](docs/decisions.md)

Laravel API は `Controller -> Service -> Repository interface -> Eloquent Repository` の依存方向です。Controller は HTTP の入出力と認可、Service はユースケースと業務判断、Repository は永続化を担当します。汎用 `BaseRepository` は置かず、ドメインごとの契約だけを `apps/api/app/Contracts` に定義しています。Service のDB非依存テストは `apps/api/tests/Isolated` にあります。

## セキュリティ上の注意

- 公開 Signup は USER role 固定です。
- password、token、cookie、secret はログへ出力しません。
- 本番では `APP_KEY`、DB password、internal API URL を Secrets Manager 等から注入し、`AUTH_COOKIE_SECURE=true` にしてください。
- BFF cookie は HttpOnly / SameSite=Lax / Path=/ です。
