# API 実装の重要判断

- 主キーは User / Event / Reservation / Ticket のすべてで UUID を使う。Sanctum の `tokenable_id` も UUID morph とする。
- 公開 Event API は匿名および USER に `PUBLISHED` のみ返す。Bearer Token が ADMIN の場合だけ全 status の一覧・詳細を許可する。
- 公開 signup は入力に `role` が含まれても採用せず、常に `USER` を設定する。ADMIN は Seeder からのみ作成する。
- Event の状態変更は専用 endpoint を増やさず `PATCH /api/events/{event}` の `status` で行う。
- Reservation の `(user_id, event_id)` 一意制約は取消後も維持し、同じ Event への再予約を禁止する。
- 予約と取消は Event 行を最初に `lockForUpdate()` し、同一 Event のロック順を統一する。取消済みの再取消では予約数を減算しない。
- 他ユーザーの Reservation への参照・取消は存在を秘匿するため `404 RESERVATION_NOT_FOUND` を返す。
- Event の sort 列は allow-list で限定し、任意の SQL 識別子を入力から組み立てない。
