export type Role = "USER" | "ADMIN";
export type EventStatus = "DRAFT" | "PUBLISHED" | "CLOSED" | "CANCELLED";
export type ReservationStatus = "RESERVED" | "CANCELLED";
export type TicketStatus = "VALID" | "CANCELLED";

export interface User {
  id: string;
  email: string;
  name: string;
  role: Role;
  created_at: string;
  updated_at: string;
}

export interface Event {
  id: string;
  title: string;
  description: string;
  venue: string;
  starts_at: string;
  ends_at: string;
  capacity: number;
  reserved_count: number;
  status: EventStatus;
  created_by: string;
  created_at: string;
  updated_at: string;
}

export interface Ticket {
  id: string;
  reservation_id: string;
  ticket_number: string;
  status: TicketStatus;
  issued_at: string;
  created_at: string;
}

export interface Reservation {
  id: string;
  user_id: string;
  event_id: string;
  status: ReservationStatus;
  reserved_at: string;
  cancelled_at: string | null;
  created_at: string;
  updated_at: string;
  event?: Event;
  ticket?: Ticket;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface ApiErrorBody {
  message: string;
  code: string;
  errors: Record<string, string[]> | null;
}

export interface Paginated<T> {
  data: T[];
  meta?: PaginationMeta;
}

export interface EventInput {
  title: string;
  description: string;
  venue: string;
  starts_at: string;
  ends_at: string;
  capacity: number;
  status: EventStatus;
}
