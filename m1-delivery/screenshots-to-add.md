# Extra screenshots to take yourself (logged-in dashboards)

The report PDF already includes: staging login, production login, signed-in
home, the MFA wall, and the password-confirmation step. These five add proof
from the Institute's own dashboards — take them in your browser and attach
alongside the PDF:

1. **GitHub Actions — green runs**
   https://github.com/ActiveLongevityInstitute/active-longevity-portal/actions
   Shows CI + "Deploy staging" + "Deploy production" all green.

2. **GitHub — repository root**
   https://github.com/ActiveLongevityInstitute/active-longevity-portal
   Shows the code + docs/ tree under the Institute's organisation, tag m1.0.

3. **DigitalOcean — project resources**
   https://cloud.digitalocean.com/projects/6c29d1ff-3166-4f6e-b4f0-05d6d500e8cd/resources
   Shows portal-production, portal-staging, portal-postgres in the Institute's team.

4. **DigitalOcean — database cluster overview**
   Databases → portal-postgres → Overview (shows PostgreSQL 16, online, NYC3).
   Optionally the "Trusted sources" panel — only the two droplets listed.

5. **DigitalOcean — firewall**
   Networking → Firewalls → portal-web (inbound 22/80/443 only).

Tip: crop out your avatar/email where you prefer; nothing else on these pages
is sensitive — no secrets appear in any of them.
