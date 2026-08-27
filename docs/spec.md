# Event Ticket Reservation App - Laravel + Next.js Specification

## 1. Overview

新しいバックエンド言語・フレームワークを学習するときのリファレンス実装として利用できる、イベント・チケット予約サービスを作成する。

本バージョンでは Backend に Laravel、Frontend に Next.js を利用する。

Frontend は Next.js App Router を利用し、ブラウザから Laravel API を直接呼び出さず、Next.js Route Handlers を BFF（Backend for Frontend）として利用する。

```text
Browser
  ↓
Next.js App Router
  ↓
Next.js Route Handlers / BFF
  ↓
Laravel REST API
  ↓
PostgreSQL
```

主目的は Laravel による一般的な API 開発を一通り経験することであり、ORM や Frontend 自体を深く学ぶことは主目的としない。

---

## 2. Goals

- Laravel の基本的な API 開発構造を理解する
- Routing / Controller / Service / Eloquent の責務を理解する
- Form Request による Validation を実装する
- メールアドレス + パスワードによる認証を実装する
- Laravel Sanctum を利用した Token Authentication を実装する
- Policy / Middleware による認可を実装する
- Eloquent ORM + PostgreSQL を利用する
- DB Transaction を実装する
- 同時予約時の競合を考慮する
- API Resource を利用した Response を実装する
- Exception Handling を実装する
- Pagination / Filtering / Sorting を実装する
- OpenAPI Documentation を用意する
- Feature / Unit Test を実装する
- Docker を利用したローカル環境を構築する
- Terraform で AWS Infrastructure を管理する
- Next.js App Router を利用する
- Next.js Route Handlers を BFF として利用する

---

## 3. Non Goals

初期実装では以下は対象外とする。

- 実際の決済
- OAuth / Social Login
- 複雑な座席指定
- QR コードによる入場管理
- メール送信
- Push 通知
- Microservices
- Event Sourcing
- CQRS
- Redis
- Kafka
- SQS
- Kubernetes
- Laravel Octane の性能最適化
- Next.js の高度な UI / Animation
- SEO 最適化
- ORM の内部実装の深掘り

---

## 4. Technology Stack

### Backend

- PHP 8.4
- Laravel 13.x
- Laravel Sanctum
- Eloquent ORM
- PostgreSQL
- Composer
- Pest
- REST API

Laravel は 2026 年 8 月時点の最新安定 Major Version である Laravel 13.x を利用する。

Minor / Patch Version はプロジェクト作成時点の最新安定版を利用する。

### Frontend

- TypeScript
- Next.js
- React
- App Router
- Server Components
- Client Components
- Route Handlers
- pnpm

Pages Router は使用しない。

### Infrastructure

- AWS
- Terraform
- Docker
- ECR
- ECS Fargate
- ALB
- RDS PostgreSQL
- CloudFront
- CloudWatch Logs
- Secrets Manager または SSM Parameter Store

---

## 5. Architecture

```text
Browser
  ↓ HTTPS
Next.js
  ├─ App Router
  ├─ Server Components
  ├─ Client Components
  └─ Route Handlers
       ↓ Internal HTTP
Laravel API
  ├─ Controller
  ├─ Service
  └─ Eloquent
       ↓
PostgreSQL
```

Browser から Laravel API は直接呼び出さない。

Client Component からデータ変更する場合:

```text
Browser
  ↓
/api/*
  ↓
Next.js Route Handler
  ↓
Laravel API
```

Server Component から取得する場合は Laravel API 呼び出し用の共通 Server-side Client を利用する。

---

## 6. Domain Model

```text
User
 ├─ Reservation
 │    └─ Ticket
 │
Event
 ├─ Reservation
 └─ Ticket
```

---

## 7. Entity Definitions

### User

| Field | Type | Description |
|---|---|---|
| id | UUID | Primary Key |
| email | string | ログインメール |
| password | string | ハッシュ化済み Password |
| name | string | 表示名 |
| role | enum | USER / ADMIN |
| created_at | datetime | 作成日時 |
| updated_at | datetime | 更新日時 |

Constraints:

- email UNIQUE
- Password 平文保存禁止

### Event

| Field | Type | Description |
|---|---|---|
| id | UUID | Primary Key |
| title | string | イベント名 |
| description | text | 説明 |
| venue | string | 開催場所 |
| starts_at | datetime | 開始日時 |
| ends_at | datetime | 終了日時 |
| capacity | integer | 最大人数 |
| reserved_count | integer | 現在予約数 |
| status | enum | DRAFT / PUBLISHED / CLOSED / CANCELLED |
| created_by | UUID | Admin User |
| created_at | datetime | 作成日時 |
| updated_at | datetime | 更新日時 |

### Reservation

| Field | Type | Description |
|---|---|---|
| id | UUID | Primary Key |
| user_id | UUID | User |
| event_id | UUID | Event |
| status | enum | RESERVED / CANCELLED |
| reserved_at | datetime | 予約日時 |
| cancelled_at | datetime nullable | キャンセル日時 |
| created_at | datetime | 作成日時 |
| updated_at | datetime | 更新日時 |

Unique Constraint:

```text
(user_id, event_id)
```

### Ticket

| Field | Type | Description |
|---|---|---|
| id | UUID | Primary Key |
| reservation_id | UUID | Reservation |
| ticket_number | string | チケット番号 |
| status | enum | VALID / CANCELLED |
| issued_at | datetime | 発行日時 |
| created_at | datetime | 作成日時 |

Constraints:

```text
reservation_id UNIQUE
ticket_number UNIQUE
```

---

## 8. Authentication

Laravel Sanctum を利用する。

### Signup

Laravel API:

```http
POST /api/auth/signup
```

処理:

```text
Form Request Validation
  ↓
Email Duplicate Check
  ↓
Hash::make()
  ↓
User Create
  ↓
Sanctum Token Issue
```

### Login

Next.js 側公開 Endpoint:

```http
POST /api/auth/login
```

Flow:

```text
Browser
  ↓
Next.js Route Handler
  ↓
Laravel /api/auth/login
  ↓
Sanctum Token
  ↓
Next.js HttpOnly Cookie
```

Sanctum Token は LocalStorage に保存しない。

Cookie:

```text
HttpOnly = true
Secure = true in production
SameSite = Lax or Strict
```

### Logout

```text
Browser
 ↓
Next Route Handler
 ↓
Laravel Token Revoke
 ↓
Next HttpOnly Cookie Delete
```

### Current User

Laravel:

```http
GET /api/auth/me
```

Next:

```http
GET /api/auth/me
```

Next Route Handler が Cookie から Token を取得し Laravel API に Bearer Token として送信する。

---

## 9. Authorization

Role:

```text
USER
ADMIN
```

ADMIN:

- Event 作成
- Event 更新
- Event 削除
- Event 公開
- Event キャンセル

USER:

- Event 閲覧
- Event 予約
- 自分の予約確認
- 自分の予約キャンセル

Laravel の Policy / Gate / Middleware を利用する。

---

## 10. Laravel API Specification

### Authentication

```text
POST /api/auth/signup
POST /api/auth/login
POST /api/auth/logout
GET  /api/auth/me
```

### Events

```text
GET    /api/events
GET    /api/events/{event}
POST   /api/events
PATCH  /api/events/{event}
DELETE /api/events/{event}
```

### Search / Pagination

```http
GET /api/events?keyword=laravel&status=PUBLISHED&page=1&per_page=20
```

Query:

```text
keyword
status
starts_from
starts_to
sort
page
per_page
```

### Reservations

```text
POST   /api/events/{event}/reservations
GET    /api/me/reservations
GET    /api/me/reservations/{reservation}
DELETE /api/me/reservations/{reservation}
```

---

## 11. Next.js Route Handlers

Next.js の Route Handler を Laravel API の BFF として利用する。

```text
apps/web/src/app/api/
├── auth/
│   ├── signup/route.ts
│   ├── login/route.ts
│   ├── logout/route.ts
│   └── me/route.ts
├── events/
│   ├── route.ts
│   └── [eventId]/
│       ├── route.ts
│       └── reservations/
│           └── route.ts
└── me/
    └── reservations/
        ├── route.ts
        └── [reservationId]/
            └── route.ts
```

Route Handler の責務:

- Browser Request を受け取る
- HttpOnly Cookie から Authentication Token を読む
- Laravel API に Request を転送する
- Laravel API Response を Browser 向けに返す
- Domain Logic は実装しない

---

## 12. Server Component Data Fetching

読み取り系は Server Component から Laravel API を Server-side Fetch してよい。

共通 Client:

```text
apps/web/src/lib/api/
├── client.ts
├── auth.ts
├── events.ts
└── reservations.ts
```

Browser から Laravel API へ直接通信しない。

---

## 13. Reservation Transaction

Laravel の `DB::transaction()` を利用する。

```text
BEGIN

1. Event 取得
2. status == PUBLISHED を確認
3. 残席確認
4. 重複予約確認
5. Reservation INSERT
6. Ticket INSERT
7. Event reserved_count UPDATE

COMMIT
```

途中で失敗した場合は ROLLBACK。

---

## 14. Concurrency Control

同時予約で capacity を超過しないようにする。

候補:

```text
Atomic Update
Row Lock
lockForUpdate()
```

例:

```php
Event::query()
    ->whereKey($eventId)
    ->lockForUpdate()
    ->firstOrFail();
```

学習項目:

- Race Condition
- Lost Update
- Atomic Update
- Row Lock
- Transaction
- Isolation Level

---

## 15. Reservation Cancel Transaction

```text
BEGIN

Reservation 取得
  ↓
所有者確認
  ↓
Reservation CANCELLED
  ↓
Ticket CANCELLED
  ↓
Event reserved_count -= 1

COMMIT
```

二重キャンセルでカウントが複数回減らないようにする。

---

## 16. Validation

Laravel Form Request を利用する。

```text
RegisterRequest
LoginRequest
StoreEventRequest
UpdateEventRequest
```

Controller 内に Validation Rule を大量に直接記述しない。

---

## 17. Response

Laravel API Resource を利用する。

```text
UserResource
EventResource
ReservationResource
TicketResource
```

Eloquent Model をそのまま Response として返さない。

---

## 18. Error Handling

共通 Error Response:

```json
{
  "message": "Event is sold out",
  "code": "EVENT_SOLD_OUT",
  "errors": null
}
```

代表 Error:

```text
INVALID_CREDENTIALS
EMAIL_ALREADY_EXISTS
EVENT_NOT_FOUND
EVENT_NOT_PUBLISHED
EVENT_SOLD_OUT
ALREADY_RESERVED
RESERVATION_NOT_FOUND
RESERVATION_ALREADY_CANCELLED
UNAUTHORIZED
FORBIDDEN
VALIDATION_ERROR
```

Laravel Exception Handler で形式を統一する。

---

## 19. Laravel Application Structure

```text
apps/api/
├── app/
│   ├── Enums/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/
│   ├── Policies/
│   └── Services/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── tests/
│   ├── Feature/
│   └── Unit/
├── composer.json
└── Dockerfile
```

Policy:

- Laravel 標準構造を優先
- Repository Pattern は初期実装では導入しない
- Generic BaseRepository / BaseService は作らない
- 複数 Model をまたぐ Business Logic や Transaction は Service に置く

---

## 20. Laravel Features To Learn

最低限以下を利用する。

- Routing
- Route Model Binding
- Controller
- Form Request
- Middleware
- Eloquent Model
- Relation
- Query Builder
- Migration
- Seeder
- Factory
- API Resource
- Policy
- Gate
- Sanctum
- DB Transaction
- Pagination
- Validation
- Exception Handling
- Config / Environment
- Logging
- Pest Test

---

## 21. Eloquent

Relations:

```text
User
  hasMany Reservations

Event
  hasMany Reservations
  belongsTo User(created_by)

Reservation
  belongsTo User
  belongsTo Event
  hasOne Ticket

Ticket
  belongsTo Reservation
```

ORM 自体の深掘りは主目的としない。

理解対象:

- Model
- Relation
- Query
- Scope
- CRUD
- Transaction
- Lock
- Pagination
- Eager Loading
- N+1 Problem

---

## 22. Database Migration

Development:

```text
Migration Create
  ↓
php artisan migrate
  ↓
Local PostgreSQL
```

Production:

```text
CI/CD
  ↓
php artisan migrate --force
  ↓
RDS PostgreSQL
  ↓
ECS Deploy
```

Migration File は Git 管理する。

---

## 23. Next.js Pages

```text
/
├── login
├── signup
├── events
│   └── [eventId]
├── my
│   └── reservations
└── admin
    └── events
        ├── new
        └── [eventId]
            └── edit
```

---

## 24. Next.js Rendering Policy

Server Component を Default とする。

Client Component は以下の場合のみ利用する。

- Form Interaction
- Browser Event
- Client-side State
- Browser API
- Interactive UI

不要な `"use client"` を付けない。

---

## 25. OpenAPI

Laravel API の OpenAPI Specification を用意する。

Laravel 13 対応が安定しているライブラリを実装時点で選ぶ。

---

## 26. Logging

Laravel Logging を利用する。

最低限:

```text
request_id
method
path
status_code
duration
user_id
```

禁止:

```text
password
Sanctum token
cookie
secret
```

---

## 27. Testing

### Laravel Feature Test

Pest を利用する。

重点:

```text
Authentication
Authorization
Event CRUD
Reservation
Cancel
Sold Out
```

### Unit Test

重点:

```text
ReservationService
Business Rule
```

### Next.js Test

最低限:

- Login Form
- Reservation Interaction
- Route Handler の重要処理

---

## 28. Local Development

Docker Compose:

```text
Browser
   ↓
Next.js
   ↓
Laravel
   ↓
PostgreSQL
```

Services:

```text
web
api
postgres
```

Ports:

```text
Next.js     3000
Laravel     8000
PostgreSQL  5432
```

---

## 29. Monorepo

単一 Git Repository の Monorepo とする。

```text
event-ticket-app/
├── apps/
│   ├── web/
│   │   ├── src/
│   │   ├── package.json
│   │   ├── pnpm-lock.yaml
│   │   ├── next.config.ts
│   │   └── Dockerfile
│   └── api/
│       ├── app/
│       ├── database/
│       ├── routes/
│       ├── tests/
│       ├── composer.json
│       ├── composer.lock
│       └── Dockerfile
├── infra/
│   └── terraform/
├── docs/
│   └── specification-laravel-next.md
├── docker-compose.yml
├── Makefile
└── README.md
```

Next.js は pnpm、Laravel は Composer を利用する。

Node と PHP の Package Manager を無理に統一しない。

---

## 30. AWS Architecture

Next.js は Route Handlers / Server Components を利用するため、S3 Static Hosting のみでは実行しない。

```text
Internet
   ↓
CloudFront
   ↓
Public ALB
   ↓
ECS Fargate: Next.js
   ↓ Internal
ECS Fargate: Laravel API
   ↓
RDS PostgreSQL
```

Browser から公開する Endpoint は Next.js を基本とする。

Laravel API は Private Network 内で Next.js からのみアクセスできる構成を目標とする。

---

## 31. ECS Services

### Web

```text
ECR
 ↓
ECS Fargate
 ↓
Next.js
```

Production Build:

```text
next build
standalone output
```

### API

```text
ECR
 ↓
ECS Fargate
 ↓
Laravel
```

Laravel は Production Container として実行する。

---

## 32. Service-to-Service Communication

Next.js → Laravel は Private Network 内通信とする。

候補:

```text
ECS Service Connect
Cloud Map
Internal ALB
```

第一候補は ECS Service Connect。

---

## 33. RDS

Amazon RDS for PostgreSQL を利用する。

- Private Subnet
- Public Access disabled
- Laravel ECS Service からのみ DB Port を許可
- Next.js から RDS へ直接接続しない

---

## 34. Network

```text
VPC

├── Public Subnets
│   └── Public ALB
└── Private Subnets
    ├── Next.js ECS
    ├── Laravel ECS
    └── RDS PostgreSQL
```

NAT Gateway は学習環境ではコストに注意する。

---

## 35. Secrets

Git に保存しないもの:

```text
APP_KEY
DB credentials
Sanctum token
Internal API URL
```

Secrets Manager または SSM Parameter Store から ECS Task に注入する。

---

## 36. CloudWatch

Next.js / Laravel とも CloudWatch Logs へ出力する。

---

## 37. Terraform

管理対象:

```text
VPC
Subnet
Security Group
ALB
CloudFront
ECR
ECS Cluster
ECS Services
ECS Task Definitions
Service Connect / Service Discovery
RDS
Secrets
CloudWatch
IAM
```

最初から過剰に Module 分割しない。

---

## 38. Implementation Phases

### Phase 1

```text
Monorepo
Next.js
Laravel
PostgreSQL
Docker Compose
```

### Phase 2

```text
Migration
Eloquent Model
Factory
Seeder
API Routing
```

### Phase 3

```text
Signup
Login
Logout
Sanctum
Auth Middleware
Next Route Handler
HttpOnly Cookie
```

### Phase 4

```text
Role
Policy
Event CRUD
Form Request
API Resource
Pagination
Search
```

### Phase 5

```text
Reservation
Ticket
DB Transaction
```

### Phase 6

```text
Concurrency Control
Sold Out
Cancel Transaction
```

### Phase 7

```text
Exception Handling
Logging
Request ID
OpenAPI
```

### Phase 8

```text
Pest Feature Test
Unit Test
Critical Next.js Test
```

### Phase 9

```text
Next.js UI
```

### Phase 10

```text
Terraform
ECR
ECS Fargate
RDS
CloudFront
ALB
Service-to-Service Network
```

---

## 39. Definition of Done

- Monorepo
- Laravel 13 API
- Next.js App Router
- Next.js Route Handler BFF
- Signup
- Login
- Logout
- Sanctum Authentication
- HttpOnly Cookie
- Admin Authorization
- Event CRUD
- Event Search
- Pagination
- Reservation
- Ticket Issue
- Transaction
- Concurrent Reservation Protection
- Reservation Cancel
- API Resource
- Validation
- Common Error Response
- Logging
- OpenAPI
- Pest Test
- Next.js UI
- Docker Compose
- Terraform
- AWS Deployment

---

## 40. Codex Implementation Policy

- 仕様を勝手に変更しない
- Laravel 13 の標準的な設計を優先する
- Next.js は App Router を利用する
- Pages Router は利用しない
- Browser から Laravel API を直接呼ばない
- Route Handler は BFF として利用する
- Route Handler に Domain Logic を書かない
- Controller に複雑な Business Logic を書かない
- Validation は Form Request を利用する
- Response は API Resource を優先する
- Authentication は Laravel Sanctum を利用する
- Resource Authorization は Policy を優先する
- Eloquent を過剰に Repository Pattern でラップしない
- Generic BaseRepository / BaseService を作らない
- Transaction が必要な処理は `DB::transaction()` を利用する
- Concurrent Reservation を考慮する
- N+1 Query を避ける
- Migration は Git 管理する
- Production Migration は `php artisan migrate --force` を利用する
- Password / Token / Cookie / Secret をログ出力しない
- Next.js の Server Component を Default とする
- Client Component は必要な場合のみ利用する
- TypeScript の `any` を安易に使用しない
- PHP の型宣言を可能な範囲で利用する
- 過剰な抽象化を避ける
- 実装と同時に重要な Test を追加する

---

## 41. Future Extensions

```text
Password Reset
Email Verification
Rate Limiting
Redis
Laravel Queue
SQS
SES
EventBridge
Payment
Seat Reservation
QR Ticket
Audit Log
Soft Delete
OpenTelemetry
CI/CD
GitHub Actions
Blue / Green Deployment
Laravel Octane
```

この Event Ticket Reservation App は、他 Framework 版と Domain Model / API Requirement を可能な限り揃える。

比較対象:

```text
NestJS
FastAPI
Laravel
Rails
Spring Boot
Go
Rust
```
