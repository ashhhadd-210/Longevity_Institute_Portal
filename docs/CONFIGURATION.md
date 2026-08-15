# Configuration

All runtime configuration comes from environment variables; every variable is
documented in [.env.example](../.env.example), which is the authoritative
list. No secret ever enters the repository. The secrets flow is:

```
Institute password manager (source of truth)
  ├─▶ GitHub environment secrets   (deploy-time: SSH keys, hosts)
  └─▶ /var/www/portal/.env         (runtime, per server)
```

## Portal-specific settings (`config/portal.php`)

| Variable | Default | Meaning |
|----------|---------|---------|
| `PORTAL_PRIVACY_NOTICE_VERSION` | `2026-08-draft` | Version consented to at registration; changing it requires re-consent |
| `PORTAL_INVITATION_EXPIRY_DAYS` | `14` | Validity of invitation links |

## Email

| Variable | Notes |
|----------|-------|
| `MAIL_MAILER` | `log` locally; `postmark` in staging/production |
| `POSTMARK_TOKEN` | Server token from the Institute's Postmark account |
| `POSTMARK_MESSAGE_STREAM_ID` | `outbound` (transactional stream only — ADR-006) |
| `POSTMARK_WEBHOOK_TOKEN` | Shared secret in the webhook URL: `https://…/webhooks/postmark?token=<value>` |

Configure the Postmark webhook (Delivery, Bounce, Spam complaint) to point at
that URL in the Postmark server settings.

## Environment differences

| Setting | Local | Staging | Production |
|---------|-------|---------|------------|
| `APP_ENV` | `local` | `staging` | `production` |
| `APP_DEBUG` | `true` | `false` | `false` |
| `MAIL_MAILER` | `log` | `postmark` (allowlist) | `postmark` |
| `SESSION_SECURE_COOKIE` | `false` | `true` | `true` |
| Demo fixtures seeded | yes | yes (synthetic only) | never |

After changing any value on a server: `php artisan config:cache` and
`php artisan queue:restart` (workers hold the old config in memory).
