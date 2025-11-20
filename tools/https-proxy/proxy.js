const httpProxy = require('http-proxy');
const https = require('https');
const selfsigned = require('selfsigned');
const os = require('os');


// Configuration
const TARGET_URL = process.env.TARGET_URL || 'http://127.0.0.1:8000'; // Laravel dev server
const PROXY_PORT = process.env.PROXY_PORT || 8080;

// Generate self-signed certificate
console.log('Generating self-signed certificate...');
const attrs = [{ name: 'commonName', value: 'localhost' }];
const pems = selfsigned.generate(attrs, { days: 365 });

const options = {
    ssl: {
        key: pems.private,
        cert: pems.cert
    },
    target: TARGET_URL,
    secure: false, // Accept self-signed certs from target if any (not needed for http target but good practice)
    ws: true, // Proxy websockets
    xfwd: true // Add X-Forwarded-* headers
};

// Create proxy server
const proxy = httpProxy.createProxyServer({});

// Error handling
proxy.on('error', function (err, req, res) {
    console.error('Proxy error:', err);
    if (res && !res.headersSent) {
        res.writeHead(500, { 'Content-Type': 'text/plain' });
        res.end('Proxy error: ' + err.message);
    }
});

// Create HTTPS server
const httpsServer = https.createServer(options.ssl, (req, res) => {
    proxy.web(req, res, { target: TARGET_URL });
});

// Upgrade for websockets
httpsServer.on('upgrade', (req, socket, head) => {
    proxy.ws(req, socket, head, { target: TARGET_URL });
});

// Get LAN IP
function getLanIp() {
    const interfaces = os.networkInterfaces();
    for (const name of Object.keys(interfaces)) {
        for (const iface of interfaces[name]) {
            if (iface.family === 'IPv4' && !iface.internal) {
                return iface.address;
            }
        }
    }
    return 'localhost';
}

// Start server
httpsServer.listen(PROXY_PORT, () => {
    const ip = getLanIp();
    console.log('-'.repeat(50));
    console.log(`HTTPS Proxy running!`);
    console.log(`Target: ${TARGET_URL}`);
    console.log(`Local Access: https://localhost:${PROXY_PORT}`);
    console.log(`LAN Access:   https://${ip}:${PROXY_PORT}`);
    console.log('-'.repeat(50));
    console.log('NOTE: You will see a security warning in the browser because');
    console.log('      we are using a self-signed certificate. This is normal.');
    console.log('      Please accept the warning to proceed.');
    console.log('-'.repeat(50));
});
