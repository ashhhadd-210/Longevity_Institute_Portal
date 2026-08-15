# 03 — Entity-Relationship Diagram & Annotated Schema

**Status:** Draft for internal review · **Milestone:** M1 migrations unless marked *(deferred)*

## 1. ERD

```mermaid
erDiagram
    users ||--o| collaborators : "registers as"
    users ||--o{ role_assignments : "holds"
    roles ||--o{ role_assignments : "granted via"
    roles }o--o{ permissions : "includes"

    collaborators ||--|| profiles : "presents"
    profiles ||--o{ profile_entries : "CV sections"
    profiles ||--o{ profile_links : "≤5 links"
    profiles }o--o| organisations : "primary affiliation"
    collaborators }o--o{ expertise_areas : "declares"

    collaborators ||--o{ referrals : "submits"
    applications ||--o| admission_decisions : "receives"
    referrals ||--o| admission_decisions : "receives"
    admission_decisions ||--o| invitations : "may issue"
    invitations ||--o| collaborators : "admits"
    users ||--o{ invitations : "issued_by"
    users ||--o{ admission_decisions : "decided_by"

    users ||--o{ consents : "grants"
    users ||--o{ data_requests : "files"
    users ||--o{ audit_events : "acts (nullable)"
    invitations ||--o{ email_messages : "delivery-logged"

    users {
        bigint id PK
        citext email UK "verified; enumeration-safe flows"
        text password_hash
        text first_name "full Unicode"
        text middle_name "nullable"
        text last_name
        text mfa_secret "encrypted; required for admin roles"
        jsonb mfa_recovery_codes "hashed"
        text account_state "active|suspended|deactivated"
        timestamptz email_verified_at
    }
    collaborators {
        bigint id PK
        bigint user_id FK "unique"
        bigint invitation_id FK "provenance"
        text status "active|inactive|departed"
        text cohort "wave_0|wave_1|..."
        boolean directory_visible "admin-controllable"
        text visibility_override "null|force_hidden|force_visible"
        timestamptz admitted_at
    }
    profiles {
        bigint id PK
        bigint collaborator_id FK "unique"
        text professional_title
        text one_line_summary "char-limited"
        text about "1-2 paragraphs"
        text photo_path "object storage; rectangular"
        jsonb credentials "≤3, displayed after name"
        char country_code "public"
        text city "ADMIN-ONLY"
        text state_region "ADMIN-ONLY"
        jsonb languages
        text collaboration_availability
        bigint organisation_id FK "nullable"
        timestamptz completed_at "null until required elements done"
    }
    invitations {
        bigint id PK
        citext email
        text first_name
        text last_name
        text token_hash "SHA-256; raw token never stored"
        timestamptz expires_at
        text status "issued|sent|accepted|expired|revoked"
        bigint admission_decision_id FK "nullable (direct)"
        bigint issued_by FK
        int send_count
        timestamptz accepted_at
    }
    admission_decisions {
        bigint id PK
        text subject_type "application|referral|direct"
        bigint subject_id "nullable when direct"
        text decision "approved|declined"
        text rationale
        bigint decided_by FK
        timestamptz decided_at
    }
    applications {
        bigint id PK
        citext email
        text first_name
        text last_name
        text organisation_text
        text professional_title
        char country_code
        jsonb expertise_claimed
        text motivation
        text status "submitted|under_review|approved|declined|withdrawn"
        timestamptz submitted_at
    }
    referrals {
        bigint id PK
        bigint referrer_collaborator_id FK "no per-collaborator limit"
        citext candidate_email
        text candidate_first_name
        text candidate_last_name
        text candidate_organisation
        text candidate_title
        char candidate_country_code
        jsonb candidate_expertise
        text reason
        text relationship_to_referrer
        text comments
        text status "submitted|under_review|approved|declined"
    }
    audit_events {
        bigint id PK
        bigint actor_user_id FK "nullable (system)"
        text event_type "taxonomy: admission.*, identity.*, profile.*, governance.*"
        text subject_type
        bigint subject_id
        jsonb context
        timestamptz occurred_at "append-only; no UPDATE/DELETE"
    }
    email_messages {
        bigint id PK
        text message_type "invitation|password_reset|notification|..."
        citext to_email
        text subject
        text postmark_message_id UK
        text status "queued|sent|delivered|bounced|spam_complaint|failed"
        text related_type "nullable polymorphic"
        bigint related_id
        timestamptz delivered_at
    }
```

*(Deferred entities — designed, not yet migrated: `collaboration_interests`, `acronyms`,
`acronym_meanings`, `conduct_reports`, `admin_broadcasts`. Shapes recorded in doc 02 §2.)*

## 2. Schema conventions

| Convention | Rule |
|------------|------|
| Encoding / collation | UTF-8, ICU collation — full extended-Latin + diacritics (client standard) |
| Emails | `citext` (case-insensitive) via PostgreSQL extension |
| Time | `timestamptz` everywhere; UTC in the database, localized at display |
| Keys | `bigint` identity PKs; FKs with explicit `ON DELETE` behavior per relationship (mostly `RESTRICT` — institutional history must not cascade away) |
| Soft deletion | Only where the domain justifies it (profiles); pipeline records are never deleted, they change status |
| Status columns | `text` + `CHECK` constraint (readable in psql, enforced in DB, extendable by additive migration) |
| JSONB | For genuinely variable shapes only (CV entry payloads, context, claimed expertise) — never for data we filter/join on institutionally |
| Search | PostgreSQL native: `pg_trgm` on names, tsvector on profile text — no external search service |
| Audit table | Append-only, enforced by revoking `UPDATE`/`DELETE` from the application's DB role |

## 3. Indexing plan (initial)

- `users.email` unique; `invitations.token_hash` unique; `invitations (status, expires_at)` for expiry sweeps
- `applications (status, submitted_at)`, `referrals (status)` — review-queue ordering
- `audit_events (subject_type, subject_id, occurred_at)` — "history of this person" queries
- `email_messages (related_type, related_id)` — per-invitation delivery status
- `collaborators (directory_visible, status)` partial index — directory listing
- Trigram indexes on profile name/title fields — arrives with M3 search surface

## 4. Seed data (M1)

Roles + permissions catalog (doc 06), initial expertise vocabulary (placeholder until Linas
provides his), Founder + developer admin accounts (MFA enforced at first login), synthetic
collaborators/pipeline fixtures for staging demos — clearly fake names, never real people.
