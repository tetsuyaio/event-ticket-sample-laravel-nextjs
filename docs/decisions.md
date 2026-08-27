# 実装判断記録

## 2026-08-27 初期実装

- Terraform と AWS リソースは依頼により実装対象外とする。
- 公開 Signup では `USER` のみ作成し、`ADMIN` は Seeder で作る。クライアント指定の role は受け付けず、権限昇格を防止する。
- イベントの公開・取消は専用 API が仕様にないため、管理者の `PATCH /api/events/{event}` による status 更新として扱う。
- キャンセル済み予約も `(user_id, event_id)` の一意制約を維持し、同じイベントへの再予約は `ALREADY_RESERVED` とする。
- Event 一覧の既定ページサイズは 20、最大 100。sort は allowlist で制限する。
- API 日時は ISO 8601 形式、DB/サーバーは UTC を前提とする。
- OpenAPI は Laravel 13 対応ライブラリへの不要な依存を避け、Git 管理する `docs/openapi.yaml` を正とする。
- Sanctum token は `event_ticket_token` HttpOnly Cookie にのみ保持し、production のみ Secure、SameSite=Lax、Path=/ とする。
- Laravel API の origin（例: `http://api:8000`）はサーバー専用 `LARAVEL_API_URL` で渡し、ブラウザへ公開しない。各 client は `/api/...` path を付加する。
- Laravel 13 の最低 PHP 要件は 8.3 だが、仕様に合わせ Docker runtime は PHP 8.4 を用いる。
- ローカル Compose の `APP_KEY` は起動時に生成し、開発用であっても秘密値を Git に固定保存しない。

## 2026-08-27 Service / Repository 境界

- ユーザー要望による仕様変更として、Controller と Eloquent の間に `Services` を設け、Controller は入力検証・認可・Service 呼び出し・HTTP レスポンス変換だけを担当する。
- DB アクセスはドメイン別の Repository interface (`UserRepositoryInterface`、`EventRepositoryInterface`、`ReservationRepositoryInterface`) を通す。汎用 CRUD の `BaseRepository` は作らず、各ユースケースで必要な操作だけを契約に公開する。
- Service の DB 非依存 Unit testを可能にするため、トランザクション、Sanctum token、時刻、ticket番号生成も小さな interface として注入する。
- 予約の transaction 境界と業務判断は `ReservationService` に残し、Eloquent Repository は query、`lockForUpdate`、永続化のみを担当する。
- Repository の実装は当面 Eloquent のみとし、別 ORM 対応を目的とした抽象化や Eloquent Model から独自 Entity への全面変換は行わない。
