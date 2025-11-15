Remote access (LAN) instructions for local development

Goal

Allow other machines on your local network (LAN) to access this Laravel app running on your development machine.

Summary

- Bind the dev server to 0.0.0.0 so it listens on all interfaces.
- Set APP_URL to the machine's LAN IP or hostname.
- Ensure session cookie settings allow cross-host requests for browser-based flows (camera upload needs cookies for CSRF).
- Open a firewall rule for the chosen port (e.g., 8000) or temporarily disable the firewall for testing.

Steps

1) Find your machine LAN IP (PowerShell):

```powershell
# shows IPv4 address on Windows
Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias (Get-NetAdapter | Where-Object Status -eq 'Up').Name | Where-Object {$_.IPAddress -notlike '169.*'} | Select-Object IPAddress
```

2) Update .env

- Set APP_URL to your machine IP, for example:

```
APP_URL=http://192.168.1.42:8000
```

- Make session cookies permissive for local testing (use with care; only for LAN/dev):

```
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=null
```

Save .env and (optionally) restart any running artisan serve process.

3) Start the Laravel dev server bound to all interfaces

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

You should see the server listening on 0.0.0.0:8000; other machines can open http://<your-ip>:8000

4) Windows firewall note

If other machines can't reach the port, create a temporary inbound rule for TCP port 8000:

```powershell
# run as Administrator
New-NetFirewallRule -DisplayName "Allow Laravel 8000" -Direction Inbound -Protocol TCP -LocalPort 8000 -Action Allow
```

Remove it when you're done testing:

```powershell
# run as Administrator
Remove-NetFirewallRule -DisplayName "Allow Laravel 8000"
```

5) Camera + HTTPS note

- Browsers require a secure context (HTTPS) for getUserMedia access on some platforms and cross-origin contexts. When accessing via LAN IP you may still be able to use camera over HTTP (desktop browsers often allow it for localhost); if your browser blocks camera access over plain HTTP, consider one of:
  - Use a reverse proxy with a self-signed cert (e.g., Caddy, mkcert + nginx)
  - Use ngrok or localtunnel to expose an HTTPS endpoint to the internet (good for quick testing but avoid exposing sensitive data)

6) CSRF and cookies

- The app uses session-based CSRF tokens. For cross-host testing on a LAN, ensure the browser receives and sends session cookies back to the server. Setting SESSION_SAME_SITE=null and SESSION_SECURE_COOKIE=false in .env (and clearing config/cache) will help for local tests.

7) Apply config changes

After editing `.env`, run:

```powershell
# optional but recommended
php artisan config:clear; php artisan cache:clear; php artisan view:clear
```


Security reminder

- These changes are for local development only. Do NOT use SESSION_SAME_SITE=null or disable secure cookies in production. Restore secure defaults before exposing an app to the public internet.

Troubleshooting

- If the browser doesn't send cookies, inspect the Network tab and check Set-Cookie and Cookie headers.
- If the camera is blocked, try accessing from the host machine first (localhost), or use HTTPS via ngrok or a local cert.

Contact

If you want, I can:
- Add an artisan command or a tiny helper script to print your LAN IP and recommended .env values.
- Add a small notice on the app's login/create page that shows the current APP_URL to help debugging.
