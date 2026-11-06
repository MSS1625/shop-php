# 🚀 Deploying Digi-Shop to a VPS — a step-by-step guide (free tier included)

> This is the English guide; Persian version: [`docs/DEPLOY.fa.md`](DEPLOY.fa.md)
>
> As of v2.17.0 the whole store boots on any Linux server with a production stack
> (app + web + db + caddy) in two commands — with automatic HTTPS. You need:
> **a VPS**, **a domain** (any cheap one), and **Docker** (installed on the server below).

---

## 1) Where to get a free server — the real Free-Tier picture (autumn 2026)

| Service | What you get | Key limitation | Card |
|---|---|---|---|
| **Oracle Cloud Always Free** ⭐ | The equivalent of 2 ARM (A1) cores + 12 GB RAM always-on, 2 tiny AMD VMs, 200 GB disk | Signup requires an international card for identity verification (no charge) and is not available in every country | Required |
| **Google Cloud** | One e2-micro (1 GB RAM), always free in US regions | 1 GB is tight for MySQL + Laravel; also wants a card | Required |
| **GitHub Codespaces** | 60 hours/month of a cloud machine with Docker preinstalled | Not a 24/7 host — great for practice and demos | Not required |
| **Render** | Containers on a free plan with HTTPS | Spins down after 15 idle minutes and has an ephemeral filesystem → not a fit for this project | Not required |

**Our pick: Oracle Always Free** — nowhere else gives you that much RAM and CPU
*forever*. If signing up isn't possible for you, practice this exact guide on
Codespaces (section 5) — the skill is identical.

Oracle signup notes that save you confusion:

- **Choose the home region wisely** — it can't be changed later, and Always Free
  resources only exist in that region. Popular regions (e.g. Ashburn) frequently
  throw "Out of Capacity" errors for ARM shapes.
- Beside Always Free you also get **$300 / 30-day trial credits** — nice for
  trying other OCI services, not needed for this guide.
- If the ARM shape shows no capacity: wait and retry (it frees up), or pick a
  quieter region at signup.

---

## 2) Preparing the server (Oracle, or any Ubuntu VPS)

### 2-1) Creating the machine

On Oracle: **Create Instance** with:

- Image: **Canonical Ubuntu 24.04 LTS** (ARM/aarch64 by default)
- Shape: **VM.Standard.A1.Flex** — 2 OCPUs and 12 GB RAM (the free ceiling)
- SSH key: your public key (without it you can't log in)

Don't worry about ARM — every image this project uses (`php:8.3-fpm-alpine`,
`mysql:8.4`, `caddy:2-alpine`) ships arm64 builds.

### 2-2) Opening ports — the classic Oracle trap

The cloud firewall only opens port 22 by default. For the web you must open
80 and 443: **Instance → Subnet → Security Lists → Add Ingress Rules** for
`0.0.0.0/0` on TCP 80 and 443. (Half of all "my site is down" moments are exactly here.)

### 2-3) DNS

At your domain registrar, create an **A record** pointing at the server's public
IP (e.g. `shop.example.com → 203.0.113.10`). Until this record resolves, no TLS
certificate can be issued — DNS first, deploy second.

### 2-4) Installing Docker (on the server)

```bash
ssh ubuntu@<SERVER-IP>
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER && exit   # then ssh again
```

---

## 3) Deploy — two commands

On the server:

```bash
git clone https://github.com/MSS1625/shop-php.git && cd shop-php

cp .env.production.example .env.production
nano .env.production        # DIGISHOP_DOMAIN + strong DB passwords
```

Then:

```bash
docker compose --env-file .env.production -f docker-compose.prod.yml up -d --build --wait
```

That's it. The first boot takes a few minutes (image build + MySQL first-time
init + migrate/seed behind healthchecks). Afterwards:

- **`https://your-domain`** is live — Caddy obtained the Let's Encrypt certificate
  itself and renews it automatically; HTTP redirects to HTTPS.
- The admin password is printed once in the logs:
  `docker compose --env-file .env.production -f docker-compose.prod.yml logs app`
  (or set `ADMIN_PASSWORD` in `.env.production` beforehand)
- Only caddy is reachable from outside (80/443); the app, nginx and MySQL stay
  on the internal compose network.

> Shortcut for the long command: `alias dc='docker compose --env-file .env.production -f docker-compose.prod.yml'`
> — then it's just `dc ps`, `dc logs -f app`, `dc up -d --build --wait` …

---

## 4) After deploying

**Updating to a new version:**

```bash
git pull
dc up -d --build --wait    # the entrypoint re-runs the idempotent shop:install
```

**Database backup (do this regularly):**

```bash
dc exec db sh -c 'exec mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' \
  > digishop-$(date +%F).sql
```

**Uploaded-images backup:**

```bash
docker run --rm -v digishop-prod_storage-data:/data -v "$PWD":/backup \
  alpine tar czf /backup/uploads-$(date +%F).tar.gz -C /data .
```

**Logs:** `dc logs -f caddy` (TLS/proxy) · `dc logs -f app` (Laravel).

---

## 5) Practice without any server — GitHub Codespaces

No VPS or international card yet? Practice this path for free:

1. On the repo: green **Code → Codespaces → Create codespace**
2. In the browser terminal:
   ```bash
   docker compose up -d --wait    # the exact same stack (app + web + db)
   ```
3. Forward port 8080 from the Ports panel and make it public → you have a live URL.

60 free hours per month, Docker preinstalled — enough to learn these commands
and healthchecks hands-on; it's just not a 24/7 host.

---

## 6) Quick troubleshooting

| Symptom | First place to look |
|---|---|
| TLS certificate not issued | `dc logs caddy` — almost always DNS (2-3) or port 443 closed (2-2) |
| Site down but Caddy is up | `dc ps` — are app, web and db all `healthy`? then `dc logs app` |
| First boot feels slow | Normal; `--wait` blocks behind healthchecks (MySQL init + migrations) |
| Links render as http | Fixed automatically since v2.17.0 (`trustProxies`) — update if you're older |
| Oracle "Out of Capacity" | Quieter region, or retry later (section 1) |

---

## Recap

The full path: **free VPS → Docker → `.env.production` → one command → your
store on HTTPS with your own domain.** Walking through it teaches you, in
practice: how real Free Tiers work, how cloud firewalls and DNS connect, how
Caddy issues TLS with zero config, and how a Docker stack boots on a real
server — skills that transfer to any project you'll ever ship.
