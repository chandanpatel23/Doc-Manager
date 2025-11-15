<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RemoteInfo extends Command
{
    /**
     * The name and signature of the console command.
     * --apply will write suggested keys to .env (backing up first)
     *
     * @var string
     */
    protected $signature = 'remote:info {--apply : Apply suggested .env changes (creates .env.remote.bak)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show suggested .env changes to allow LAN remote access and optionally apply them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ip = $this->getLanIp();

        if (! $ip) {
            $this->error('Could not detect a LAN IPv4 address. Please set APP_URL manually.');
            return 1;
        }

        $port = 8000;
        $appUrl = "http://{$ip}:{$port}";

        $this->info('Detected LAN IP: ' . $ip);
        $this->line('Suggested .env entries:');
        $this->line("APP_URL={$appUrl}");
    $this->line('SESSION_SECURE_COOKIE=false');
    $this->line('SESSION_SAME_SITE=null');

        if ($this->option('apply')) {
            $envPath = base_path('.env');
            if (! file_exists($envPath)) {
                $this->error('.env not found');
                return 1;
            }

            $backup = base_path('.env.remote.bak');
            copy($envPath, $backup);
            $this->info("Backed up .env to .env.remote.bak");

            $contents = file_get_contents($envPath);

            $contents = $this->setEnvValue($contents, 'APP_URL', $appUrl);
            $contents = $this->setEnvValue($contents, 'SESSION_SECURE_COOKIE', 'false');
            $contents = $this->setEnvValue($contents, 'SESSION_SAME_SITE', 'null');

            file_put_contents($envPath, $contents);
            $this->info('Applied suggested .env changes. Run: php artisan config:clear; php artisan cache:clear');
        }

        return 0;
    }

    protected function getLanIp()
    {
        // Try to read network interfaces via shell in a cross-platform way.
        // We'll attempt Windows PowerShell first (since user is on Windows).
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Run PowerShell command to get first non-APIPA IPv4
            $cmd = 'powershell -NoProfile -Command "Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.IPAddress -notlike \"169.*\" -and $_.IPAddress -notlike \"127.*\"} | Select-Object -First 1 -ExpandProperty IPAddress"';
            $output = trim(shell_exec($cmd));
            if ($output) {
                return $output;
            }
        }

        // Fallback: attempt to open UDP socket to known external host
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock) {
            @socket_connect($sock, '8.8.8.8', 53);
            @socket_getsockname($sock, $name, $port);
            @socket_close($sock);
            if (! empty($name)) {
                return $name;
            }
        }

        return null;
    }

    protected function setEnvValue($contents, $key, $value)
    {
        $pattern = '/^' . preg_quote($key, '/') . '=.*/m';
        if (preg_match($pattern, $contents)) {
            return preg_replace($pattern, $key . '=' . $value, $contents);
        }

        // append
        return rtrim($contents, "\n") . "\n{$key}={$value}\n";
    }
}
