# Cloudflare Tunnel fronting OpenStack origin on port 8080

These steps publish the site running on the OpenStack VM (listening on port 8080) at `herbaria.buspark.io` via Cloudflare Tunnel on AlmaLinux 9.6.

## Prereqs
- AlmaLinux 9.6 VM with outbound HTTPS (443) allowed.
- Site reachable on the VM at `http://localhost:8080` (or the floating IP internally).
- Access to the Cloudflare zone `herbaria.buspark.io` with permission to create DNS records.

## Install cloudflared from the Cloudflare RPM repo
1) Add the repo (Cloudflare-managed .repo file and correct GPG key):
```bash
sudo dnf config-manager --add-repo https://pkg.cloudflare.com/cloudflared.repo
```
2) Install:
```bash
sudo dnf install -y cloudflared
cloudflared --version
```

## Create and configure the tunnel
1) Authenticate to Cloudflare (opens browser or gives a URL to paste a token):
```bash
cloudflared tunnel login
```
2) Create the tunnel (record the UUID printed):
```bash
cloudflared tunnel create herbaria-alpha
```
3) Prepare `/etc/cloudflared` and credentials file (created in `~/.cloudflared/<UUID>.json` by the previous step):
```bash
sudo mkdir -p /etc/cloudflared
sudo cp ~/.cloudflared/<TUNNEL-UUID>.json /etc/cloudflared/
```

4) Write `/etc/cloudflared/config.yml`:
```bash
sudo tee /etc/cloudflared/config.yml >/dev/null <<'EOF'
tunnel: <TUNNEL-UUID>
credentials-file: /etc/cloudflared/<TUNNEL-UUID>.json
ingress:
  - hostname: herbaria.buspark.io
    service: http://localhost:8080
  - service: http_status:404
EOF
```
Replace `<TUNNEL-UUID>` with the value from step 2.

5) Bind the hostname to the tunnel (creates a proxied CNAME in Cloudflare):
```bash
cloudflared tunnel route dns herbaria-alpha herbaria.buspark.io
```

## Run the tunnel as a service
```bash
sudo cloudflared service install
sudo systemctl enable --now cloudflared
sudo systemctl status cloudflared
```
(For ad-hoc testing, you can run `cloudflared tunnel run herbaria-alpha` in the foreground instead.)

## Test
- Visit `http://herbaria.buspark.io` or `https://herbaria.buspark.io`. Cloudflare terminates TLS; origin stays on 8080.
- If it fails, check logs: `sudo journalctl -u cloudflared -f`.

## Optional: remove Cloudflare Access prompt (make public)
- In Cloudflare Dashboard: Zero Trust → Access → Applications → select the app for `herbaria.buspark.io`.
- Either delete/disable the application, or set an Access policy with Action = Bypass and Include = Everyone.
- Save; changes apply quickly. No tunnel/DNS change needed.

## Notes
- No inbound firewall changes are needed; the tunnel is outbound-only on 443.
- If you later add TLS on the origin with a self-signed cert, add under `ingress`:
  ```
  originRequest:
    noTLSVerify: true
  ```
- To update cloudflared: `sudo dnf upgrade cloudflared`.
