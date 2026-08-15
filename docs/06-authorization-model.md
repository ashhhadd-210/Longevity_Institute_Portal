# 06 — Authorization Model

**Status:** Draft for internal review · **Milestone:** M1

## 1. Principle

**Roles and permissions are data, never booleans.** There is no `is_admin` column anywhere.
The system answers: *who can do what, to which resources, under which circumstances* — an
institutional authorization model, not login protection.

```
User ──▶ RoleAssignment ──▶ Role ──▶ Permissions
              │
              └─ assigned_by, assigned_at   (governance is itself history)
```

Future "additional authorized Institute officers", regional reviewers, or read-only staff
become **configuration (a new role row + permission grants), not migration**.

## 2. Initial permission catalog

Named `domain.action[.qualifier]`, seeded in M1 (catalog grows additively):

| Domain | Permissions |
|--------|-------------|
| admission | `admission.application.review`, `admission.referral.review`, `admission.decision.record`, `admission.invitation.issue`, `admission.invitation.revoke`, `admission.invitation.resend`, `admission.history.view` |
| collaborator | `collaborator.view_admin` (incl. admin-only city/state), `collaborator.manage`, `collaborator.status.change`, `collaborator.visibility.override` |
| expertise | `expertise.manage` (Institute-controlled vocabulary) |
| governance | `governance.role.assign`, `governance.audit.view`, `governance.user.suspend` |
| privacy | `privacy.export.fulfil`, `privacy.erasure.fulfil` |
| platform | `platform.email_log.view`, `platform.settings.manage` |

## 3. Seeded roles

| Role | Grants | `requires_mfa` |
|------|--------|:---:|
| `founder` | Everything, including `governance.role.assign` | ✔ |
| `administrator` | All admission + collaborator + platform; **not** role assignment (Founder-reserved initially) | ✔ |
| `collaborator` | Own profile + directory viewing (implicit collaborator capabilities) | — |

Roadmap roles (`admission_reviewer`, `regional_reviewer`, `readonly_staff`) are documented
shapes — added later as rows, proving the model.

## 4. Enforcement points (defense in depth)

1. **Route middleware** — admin route group requires an MFA-verified session and any admin-tier role.
2. **Policies** — every controller/Livewire action authorizes through a Policy backed by
   `$user->hasPermission(...)` (assembled from role grants, request-cached). Policies are the
   single choke point: no inline role-name checks (`hasRole('administrator')`) in application code —
   role names are configuration, permission checks are contract.
3. **Query scoping** — admin-only fields (city/state) exposed only through admin resources;
   directory queries are scoped to visible collaborators at the query layer, so a template
   mistake cannot leak a hidden profile.
4. **Audit** — permission-bearing mutations emit audit events with actor + subject.

## 5. Tests as the contract

The M1 test suite includes an authorization matrix test: for each (role × representative
endpoint), assert allow/deny. This is the regression net that keeps future surfaces honest,
and doubles as documentation of who-can-do-what.
