<?php
/**
 * LIVETVMARKET - Notification & Delivery Engine
 * Manages SMTP Email Delivery (Customer & Admin) and Pushover Push Notifications (Admin Devices).
 */

require_once __DIR__ . '/db.php';

// ─── Pushover Push Notification Engine ─────────────────────────────────────────

/**
 * Send an immediate push notification to admin devices using Pushover API.
 */
function sendPushoverNotification(
    string $title,
    string $message,
    string $url = '',
    ?int $priority = null,
    ?string $sound = null,
    ?string $devices = null
): array {
    $enabled = getSetting('pushover_enabled', '1');
    if ($enabled !== '1' && $enabled !== 'true') {
        return ['success' => false, 'error' => 'Pushover notifications are disabled in settings.'];
    }

    $token   = getSetting('pushover_app_token', 'aec7p4gjfhdaaxewy6fo8knvogw84r');
    $userKey = getSetting('pushover_user_key', 'u6vnh57r1i4w5h84eoeh9jx9epnfh7');
    $devList = $devices ?? getSetting('pushover_devices', 'pixel8');
    $snd     = $sound ?? getSetting('pushover_sound', 'cashregister');
    $prio    = $priority ?? (int)getSetting('pushover_priority', '0');

    if (empty($token) || empty($userKey)) {
        return ['success' => false, 'error' => 'Pushover App Token or User Key is missing in settings.'];
    }

    $postData = [
        'token'    => $token,
        'user'     => $userKey,
        'title'    => $title,
        'message'  => $message,
        'priority' => $prio,
    ];

    if (!empty($devList)) {
        $postData['device'] = $devList;
    }

    if (!empty($snd)) {
        $postData['sound'] = $snd;
    }

    if (!empty($url)) {
        $postData['url']       = $url;
        $postData['url_title'] = 'View in Admin Panel';
    }

    if ($prio === 2) {
        $postData['retry']  = 60;
        $postData['expire'] = 3600;
    }

    $ch = curl_init('https://api.pushover.net/1/messages.json');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/x-www-form-urlencoded'
        ]
    ]);

    $response  = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'error' => 'CURL connection error: ' . $curlError];
    }

    $data = json_decode($response, true);
    if (isset($data['status']) && (int)$data['status'] === 1) {
        return ['success' => true, 'data' => $data];
    }

    $errors = isset($data['errors']) ? implode(', ', (array)$data['errors']) : 'Failed to deliver push notification.';
    return ['success' => false, 'error' => $errors, 'http_code' => $httpCode];
}

// ─── Native SMTP Client Engine ────────────────────────────────────────────────

/**
 * Send an HTML email via direct SMTP socket connection.
 * Does not require external packages; works reliably with Titan, Gmail, Hostinger, cPanel, etc.
 */
function sendSMTPEmail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $textBody = '',
    ?string &$diagnosticLog = null
): array {
    $enabled = getSetting('smtp_enabled', '0');
    if ($enabled !== '1' && $enabled !== 'true') {
        return ['success' => false, 'error' => 'SMTP email delivery is disabled in settings.'];
    }

    $host     = getSetting('smtp_host', 'smtp.titan.email');
    $port     = (int)getSetting('smtp_port', '465');
    $secure   = strtolower(getSetting('smtp_secure', 'ssl')); // 'ssl' or 'tls'
    $username = getSetting('smtp_username', '');
    $password = getSetting('smtp_password', '');
    $fromEmail= getSetting('smtp_from_email', 'support@livetvmarket.com');
    $fromName = getSetting('smtp_from_name', 'LIVETVMARKET');

    if (empty($host) || empty($username) || empty($password)) {
        return ['success' => false, 'error' => 'SMTP host, username, or password is not configured.'];
    }

    $log = [];
    $log[] = "Connecting to SMTP server {$host}:{$port} ({$secure})...";

    $timeout = 15;
    $errno = 0;
    $errstr = '';

    $protocol = ($secure === 'ssl') ? 'ssl://' : 'tcp://';
    $socket = @stream_socket_client($protocol . $host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);

    if (!$socket) {
        $log[] = "Connection failed: {$errstr} ({$errno})";
        $diagnosticLog = implode("\n", $log);
        return ['success' => false, 'error' => "Could not connect to {$host}: {$errstr} ({$errno})"];
    }

    stream_set_timeout($socket, $timeout);

    $readResponse = function() use ($socket, &$log) {
        $data = '';
        while ($line = fgets($socket, 512)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $log[] = "SERVER: " . trim($data);
        return $data;
    };

    $sendCommand = function(string $cmd, string $mask = '') use ($socket, &$log, $readResponse) {
        $log[] = "CLIENT: " . ($mask ?: $cmd);
        fwrite($socket, $cmd . "\r\n");
        return $readResponse();
    };

    // 1. Initial greeting
    $resp = $readResponse();
    if (!str_starts_with($resp, '220')) {
        fclose($socket);
        $diagnosticLog = implode("\n", $log);
        return ['success' => false, 'error' => 'Invalid greeting from SMTP server.'];
    }

    // 2. EHLO
    $clientHost = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $resp = $sendCommand("EHLO {$clientHost}");

    // 3. STARTTLS if configured
    if ($secure === 'tls') {
        $resp = $sendCommand("STARTTLS");
        if (!str_starts_with($resp, '220')) {
            fclose($socket);
            $diagnosticLog = implode("\n", $log);
            return ['success' => false, 'error' => 'STARTTLS failed.'];
        }
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
            fclose($socket);
            $diagnosticLog = implode("\n", $log);
            return ['success' => false, 'error' => 'TLS encryption handshake failed.'];
        }
        $resp = $sendCommand("EHLO {$clientHost}");
    }

    // 4. AUTH LOGIN
    $resp = $sendCommand("AUTH LOGIN");
    if (!str_starts_with($resp, '334')) {
        fclose($socket);
        $diagnosticLog = implode("\n", $log);
        return ['success' => false, 'error' => 'AUTH LOGIN command not accepted.'];
    }

    $resp = $sendCommand(base64_encode($username), '[Username: ' . $username . ']');
    if (!str_starts_with($resp, '334')) {
        fclose($socket);
        $diagnosticLog = implode("\n", $log);
        return ['success' => false, 'error' => 'SMTP Username rejected.'];
    }

    $resp = $sendCommand(base64_encode($password), '[Password: ********]');
    if (!str_starts_with($resp, '235')) {
        fclose($socket);
        $diagnosticLog = implode("\n", $log);
        return ['success' => false, 'error' => 'SMTP Authentication failed. Check your password.'];
    }

    // 5. MAIL FROM
    $resp = $sendCommand("MAIL FROM:<{$fromEmail}>");
    if (!str_starts_with($resp, '250')) {
        fclose($socket);
        $diagnosticLog = implode("\n", $log);
        return ['success' => false, 'error' => 'Sender email address rejected.'];
    }

    // 6. RCPT TO
    $resp = $sendCommand("RCPT TO:<{$toEmail}>");
    if (!str_starts_with($resp, '250') && !str_starts_with($resp, '251')) {
        fclose($socket);
        $diagnosticLog = implode("\n", $log);
        return ['success' => false, 'error' => 'Recipient email address rejected.'];
    }

    // 7. DATA
    $resp = $sendCommand("DATA");
    if (!str_starts_with($resp, '354')) {
        fclose($socket);
        $diagnosticLog = implode("\n", $log);
        return ['success' => false, 'error' => 'DATA command rejected.'];
    }

    // 8. Headers and Body
    $boundary = "bnd_" . md5(uniqid(microtime(), true));
    $encodedSubject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    $encodedFromName = "=?UTF-8?B?" . base64_encode($fromName) . "?=";

    $headers = [
        "Date: " . date('r'),
        "To: {$toName} <{$toEmail}>",
        "From: {$encodedFromName} <{$fromEmail}>",
        "Reply-To: {$fromEmail}",
        "Subject: {$encodedSubject}",
        "MIME-Version: 1.0",
        "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
        "X-Mailer: LIVETVMARKET Mailer 2.0"
    ];

    $emailContent = implode("\r\n", $headers) . "\r\n\r\n";

    if (!empty($textBody)) {
        $emailContent .= "--{$boundary}\r\n";
        $emailContent .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $emailContent .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $emailContent .= chunk_split(base64_encode($textBody)) . "\r\n";
    }

    $emailContent .= "--{$boundary}\r\n";
    $emailContent .= "Content-Type: text/html; charset=UTF-8\r\n";
    $emailContent .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $emailContent .= chunk_split(base64_encode($htmlBody)) . "\r\n";
    $emailContent .= "--{$boundary}--\r\n";

    // End of message is a single dot on its own line
    $emailContent .= ".";

    fwrite($socket, $emailContent . "\r\n");
    $resp = $readResponse();

    // 9. QUIT
    $sendCommand("QUIT");
    fclose($socket);

    $diagnosticLog = implode("\n", $log);

    if (str_starts_with($resp, '250')) {
        return ['success' => true, 'message' => 'Email sent successfully via SMTP!'];
    }

    return ['success' => false, 'error' => 'Message delivery failed after DATA transfer: ' . trim($resp)];
}

// ─── Branded Email Templates ──────────────────────────────────────────────────

/**
 * Generate high-tech, responsive HTML email for customer subscription delivery.
 */
function buildCustomerActivationEmail(array $order): string {
    $siteName     = getSetting('site_name', 'LIVETVMARKET');
    $siteUrl      = getSetting('site_url', 'https://livetvmarket.com');
    $orderId      = $order['id'] ?? '108';
    $customerName = htmlspecialchars($order['customer_name'] ?? 'Customer');
    $email        = htmlspecialchars($order['email'] ?? '');
    $whatsapp     = htmlspecialchars($order['whatsapp'] ?? '');
    $planName     = htmlspecialchars($order['plan_name'] ?? '12 Months VIP Access');
    $connections  = htmlspecialchars($order['connections'] ?? '1');
    $amount       = number_format((float)($order['amount'] ?? 74.99), 2);
    $currency     = htmlspecialchars($order['currency'] ?? 'USD');
    $device       = htmlspecialchars($order['device'] ?? 'Smart TV / Firestick');
    $cleanWa      = preg_replace('/[^0-9]/', '', getSetting('contact_whatsapp', '+1 555 019 2831'));

    // Generated credential placeholders for instant customer setup
    $username = 'ltv_' . strtolower(substr(md5($orderId . 'usr_salt'), 0, 7));
    $password = substr(md5($orderId . 'pwd_salt'), 0, 8);
    $serverHost = 'stream.' . getSetting('site_domain', 'livetvmarket.com') . ':8080';
    $m3uUrl = "http://{$serverHost}/get.php?username={$username}&password={$password}&type=m3u_plus";
    $epgUrl = "http://{$serverHost}/xmltv.php?username={$username}&password={$password}";

    $setupUrl = rtrim($siteUrl, '/') . '/setup';
    $waText   = urlencode("Hello LIVETVMARKET Support, I received my activation email for Order #LTV-{$orderId}. Can you assist with setup?");
    $waUrl    = "https://wa.me/{$cleanWa}?text={$waText}";

    $isStore = !empty($order['order_type']) && $order['order_type'] === 'store';
    if ($isStore) {
        $badgeText = "✓ Digital Subscription Order Approved";
        $introText = "Your payment of <strong style=\"color:#38bdf8;\">{$currency} {$amount}</strong> has been successfully approved. Your digital service credentials are being prepared.";
        $credentialsBox = <<<BOX
        <div style="background: rgba(168, 85, 247, 0.08); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 16px; padding: 20px; margin-bottom: 24px;">
          <div style="font-size: 14px; font-weight: 700; color: #c084fc; margin-bottom: 12px; text-transform: uppercase;">
            ⚡ Digital Subscription Delivery Details
          </div>
          <p style="font-size: 13px; color: #cbd5e1; line-height: 1.6; margin: 0 0 10px;">
            Service Plan: <strong style="color:#ffffff;">{$planName}</strong>
          </p>
          <p style="font-size: 13px; color: #cbd5e1; line-height: 1.6; margin: 0 0 10px;">
            Your dedicated credentials, login steps, and profile PIN are dispatched directly to your WhatsApp (<strong style="color:#34d399;">{$whatsapp}</strong>) and email within 5 to 15 minutes.
          </p>
          <p style="font-size: 11px; color: #94a3b8; margin: 0;">
            ✓ Backed by 100% Full-Term Replacement Warranty
          </p>
        </div>
BOX;
        $primaryBtnText = "💬 Connect on WhatsApp for Instant Setup & PIN";
        $primaryBtnUrl  = $waUrl;
    } else {
        $badgeText = "✓ Order Confirmed & Account Activated";
        $introText = "Your payment of <strong style=\"color:#38bdf8;\">{$currency} {$amount}</strong> has been successfully approved. Your Anti-Freeze™ 9.9 high-speed streaming subscription is ready.";
        $credentialsBox = <<<BOX
        <div style="background: rgba(0, 210, 255, 0.05); border: 1px solid rgba(0, 210, 255, 0.25); border-radius: 16px; padding: 20px; margin-bottom: 24px;">
          <div style="font-size: 14px; font-weight: 700; color: #00d2ff; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.05em;">
            ⚡ Your Xtream Codes & M3U Credentials
          </div>
          <div style="margin-bottom: 10px;">
            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600;">Xtream Server Portal:</span><br>
            <code style="display: block; margin-top: 4px; padding: 8px 12px; background: rgba(0,0,0,0.4); border-radius: 8px; font-family: monospace; font-size: 13px; color: #e2e8f0; word-break: break-all;">http://{$serverHost}</code>
          </div>
          <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 10px;">
            <tr>
              <td width="50%" style="padding-right: 6px;">
                <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600;">Username:</span>
                <code style="display: block; margin-top: 4px; padding: 8px 12px; background: rgba(0,0,0,0.4); border-radius: 8px; font-family: monospace; font-size: 13px; color: #00d2ff;">{$username}</code>
              </td>
              <td width="50%" style="padding-left: 6px;">
                <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600;">Password:</span>
                <code style="display: block; margin-top: 4px; padding: 8px 12px; background: rgba(0,0,0,0.4); border-radius: 8px; font-family: monospace; font-size: 13px; color: #00d2ff;">{$password}</code>
              </td>
            </tr>
          </table>
          <div style="margin-top: 10px;">
            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 600;">Direct M3U Plus URL:</span><br>
            <code style="display: block; margin-top: 4px; padding: 8px 12px; background: rgba(0,0,0,0.4); border-radius: 8px; font-family: monospace; font-size: 11px; color: #e2e8f0; word-break: break-all;">{$m3uUrl}</code>
          </div>
        </div>
BOX;
        $primaryBtnText = "📖 View Step-by-Step Setup Guides &rarr;";
        $primaryBtnUrl  = $setupUrl;
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Streaming Activation Credentials</title>
</head>
<body style="margin:0; padding:0; background-color:#070913; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#ffffff;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#070913; padding: 40px 15px;">
    <tr>
      <td align="center">
        <!-- Main Container -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; background-color:#0D1126; border:1px solid rgba(0, 210, 255, 0.25); border-radius:24px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.7);">
          
          <!-- Header -->
          <tr>
            <td style="padding: 32px 30px; background: linear-gradient(135deg, rgba(0, 210, 255, 0.15), rgba(59, 130, 246, 0.05)); border-bottom: 1px solid rgba(255, 255, 255, 0.08); text-align: center;">
              <div style="font-size: 20px; font-weight: 900; letter-spacing: 1px; color:#ffffff; text-transform: uppercase;">
                {$siteName} <span style="color:#00d2ff;">VIP STREAMING</span>
              </div>
              <div style="margin-top: 8px; display: inline-block; padding: 4px 12px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 9999px; font-size: 11px; font-weight: 700; color: #34d399; text-transform: uppercase; letter-spacing: 0.05em;">
                {$badgeText}
              </div>
            </td>
          </tr>

          <!-- Body Content -->
          <tr>
            <td style="padding: 32px 30px;">
              <h1 style="margin: 0 0 12px; font-size: 22px; font-weight: 800; color: #ffffff;">
                Welcome, {$customerName}!
              </h1>
              <p style="margin: 0 0 24px; font-size: 14px; line-height: 1.6; color: #94a3b8;">
                {$introText}
              </p>

              <!-- Order Summary Card -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; margin-bottom: 24px;">
                <tr>
                  <td style="padding: 14px 18px; border-bottom: 1px dashed rgba(255,255,255,0.08); font-size: 13px; color: #94a3b8;">Order Reference:</td>
                  <td style="padding: 14px 18px; border-bottom: 1px dashed rgba(255,255,255,0.08); font-size: 13px; font-weight: 700; color: #00d2ff; text-align: right; font-family: monospace;">#LTV-{$orderId}</td>
                </tr>
                <tr>
                  <td style="padding: 14px 18px; border-bottom: 1px dashed rgba(255,255,255,0.08); font-size: 13px; color: #94a3b8;">Subscription Plan:</td>
                  <td style="padding: 14px 18px; border-bottom: 1px dashed rgba(255,255,255,0.08); font-size: 13px; font-weight: 600; color: #ffffff; text-align: right;">{$planName}</td>
                </tr>
                <tr>
                  <td style="padding: 14px 18px; border-bottom: 1px dashed rgba(255,255,255,0.08); font-size: 13px; color: #94a3b8;">Status:</td>
                  <td style="padding: 14px 18px; border-bottom: 1px dashed rgba(255,255,255,0.08); font-size: 13px; font-weight: 700; color: #10b981; text-align: right;">● Paid & Approved</td>
                </tr>
              </table>

              <!-- Dynamic Credentials / Delivery Box -->
              {$credentialsBox}

              <!-- Action Buttons -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                <tr>
                  <td align="center">
                    <a href="{$primaryBtnUrl}" target="_blank" style="display: block; width: 88%; padding: 14px 20px; background: linear-gradient(135deg, #00d2ff, #0284c7); color: #070913; font-weight: 800; font-size: 14px; text-decoration: none; border-radius: 12px; text-align: center; box-shadow: 0 4px 20px rgba(0, 210, 255, 0.4);">
                      {$primaryBtnText}
                    </a>
                  </td>
                </tr>
                    </a>
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding-top: 10px;">
                    <a href="{$waUrl}" target="_blank" style="display: block; width: 88%; padding: 12px 20px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; font-weight: 700; font-size: 13px; text-decoration: none; border-radius: 12px; text-align: center;">
                      💬 Fast-Track VIP WhatsApp Support
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #64748b; text-align: center;">
                Need help installing on Firestick, IPTV Smarters, TiviMate, or Apple TV? Our 24/7 technical team is standing by on WhatsApp.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding: 24px 30px; background-color: #070913; border-top: 1px solid rgba(255, 255, 255, 0.05); text-align: center; font-size: 11px; color: #475569;">
              © {$siteName}. All broadcasts syndicated via certified partner CDN clusters.<br>
              Order #LTV-{$orderId} • Automated System Dispatch
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}

/**
 * Generate high-tech, responsive HTML email for customer free trial confirmation.
 */
function buildCustomerTrialEmail(array $lead): string {
    $siteName     = getSetting('site_name', 'LIVETVMARKET');
    $siteUrl      = getSetting('site_url', 'https://livetvmarket.com');
    $leadId       = $lead['id'] ?? '1';
    $customerName = htmlspecialchars($lead['full_name'] ?? 'Customer');
    $email        = htmlspecialchars($lead['email'] ?? '');
    $whatsapp     = htmlspecialchars($lead['whatsapp'] ?? '');
    $device       = htmlspecialchars($lead['device'] ?? 'Smart TV / Firestick');
    $app          = htmlspecialchars($lead['app'] ?? 'IPTV Smarters Pro');
    $cleanWa      = preg_replace('/[^0-9]/', '', getSetting('contact_whatsapp', '+1 555 019 2831'));

    $setupUrl = rtrim($siteUrl, '/') . '/setup';
    $waText   = urlencode("Hello LIVETVMARKET Support, I requested a 24-Hour Free Trial (#TR-{$leadId}) for my {$device}. Can you assist with setup?");
    $waUrl    = "https://wa.me/{$cleanWa}?text={$waText}";

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>24-Hour Free Trial Request Received</title>
</head>
<body style="margin:0; padding:0; background-color:#070913; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#ffffff;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#070913; padding: 40px 15px;">
    <tr>
      <td align="center">
        <!-- Main Container -->
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; background-color:#0D1126; border:1px solid rgba(0, 210, 255, 0.25); border-radius:24px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.7);">
          
          <!-- Header -->
          <tr>
            <td style="padding: 32px 30px; background: linear-gradient(135deg, rgba(0, 210, 255, 0.15), rgba(59, 130, 246, 0.05)); border-bottom: 1px solid rgba(255, 255, 255, 0.08); text-align: center;">
              <div style="font-size: 20px; font-weight: 900; letter-spacing: 1px; color:#ffffff; text-transform: uppercase;">
                {$siteName} <span style="color:#00d2ff;">VIP STREAMING</span>
              </div>
              <div style="margin-top: 8px; display: inline-block; padding: 4px 12px; background: rgba(0, 210, 255, 0.15); border: 1px solid rgba(0, 210, 255, 0.4); border-radius: 9999px; font-size: 11px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.05em;">
                🎁 24-Hour Free Trial Request Received
              </div>
            </td>
          </tr>

          <!-- Body Content -->
          <tr>
            <td style="padding: 32px 30px;">
              <h1 style="margin: 0 0 12px; font-size: 22px; font-weight: 800; color: #ffffff;">
                Hello, {$customerName}!
              </h1>
              <p style="margin: 0 0 24px; font-size: 14px; line-height: 1.6; color: #94a3b8;">
                We have received your 24-Hour Free Trial request. Our automated provisioning system is preparing your Anti-Freeze™ 9.9 high-speed streaming test line.
              </p>

              <!-- Trial Request Details Card -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; margin-bottom: 24px;">
                <tr>
                  <td style="padding: 14px 18px; border-bottom: 1px dashed rgba(255,255,255,0.08); font-size: 13px; color: #94a3b8;">Request Reference:</td>
                  <td style="padding: 14px 18px; border-bottom: 1px dashed rgba(255,255,255,0.08); font-size: 13px; font-weight: 700; color: #00d2ff; text-align: right; font-family: monospace;">#TR-{$leadId}</td>
                </tr>
                <tr>
                  <td style="padding: 14px 18px; border-bottom: 1px dashed rgba(255,255,255,0.08); font-size: 13px; color: #94a3b8;">Service:</td>
                  <td style="padding: 14px 18px; border-bottom: 1px dashed rgba(255,255,255,0.08); font-size: 13px; font-weight: 600; color: #ffffff; text-align: right;">24-Hour Free Test Line (All Devices Supported)</td>
                </tr>
                <tr>
                  <td style="padding: 14px 18px; font-size: 13px; color: #94a3b8;">Status:</td>
                  <td style="padding: 14px 18px; font-size: 13px; font-weight: 700; color: #38bdf8; text-align: right;">● Line Being Prepared</td>
                </tr>
              </table>

              <!-- Next Steps Info Box -->
              <div style="background: rgba(0, 210, 255, 0.05); border: 1px solid rgba(0, 210, 255, 0.25); border-radius: 16px; padding: 20px; margin-bottom: 24px;">
                <div style="font-size: 14px; font-weight: 700; color: #00d2ff; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.05em;">
                  ⚡ What Happens Next?
                </div>
                <p style="margin: 0 0 10px; font-size: 13px; line-height: 1.6; color: #cbd5e1;">
                  Your free 24-hour playlist (M3U &amp; Xtream Codes login) is being prepared. We will send the full credentials directly to this email (<span style="color:#38bdf8;">{$email}</span>) and to your WhatsApp (<span style="color:#34d399;">{$whatsapp}</span>). Our technical support agents will guide you step-by-step on WhatsApp to configure your line easily.
                </p>
                <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #94a3b8;">
                  Want your trial activated right now? Click the WhatsApp Fast-Track button below to chat directly with our technical support engineers.
                </p>
              </div>

              <!-- Action Buttons -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                <tr>
                  <td align="center">
                    <a href="{$waUrl}" target="_blank" style="display: block; width: 88%; padding: 14px 20px; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; font-weight: 800; font-size: 14px; text-decoration: none; border-radius: 12px; text-align: center; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);">
                      💬 Fast-Track Trial on WhatsApp &rarr;
                    </a>
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding-top: 10px;">
                    <a href="{$setupUrl}" target="_blank" style="display: block; width: 88%; padding: 12px 20px; background: rgba(0, 210, 255, 0.1); border: 1px solid rgba(0, 210, 255, 0.3); color: #00d2ff; font-weight: 700; font-size: 13px; text-decoration: none; border-radius: 12px; text-align: center;">
                      📖 View Step-by-Step Setup Guides
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin: 0; font-size: 12px; line-height: 1.5; color: #64748b; text-align: center;">
                Need help installing on Firestick, IPTV Smarters, TiviMate, or Apple TV? Our 24/7 technical team is standing by.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding: 24px 30px; background-color: #070913; border-top: 1px solid rgba(255, 255, 255, 0.05); text-align: center; font-size: 11px; color: #475569;">
              © {$siteName}. All broadcasts syndicated via certified partner CDN clusters.<br>
              Trial Request #TR-{$leadId} • Automated System Dispatch
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}

/**
 * Generate Admin notification email when a free trial lead is registered.
 */
function buildAdminTrialAlertEmail(array $lead): string {
    $siteName     = getSetting('site_name', 'LIVETVMARKET');
    $siteUrl      = getSetting('site_url', 'https://livetvmarket.com');
    $leadId       = $lead['id'] ?? '1';
    $customerName = htmlspecialchars($lead['full_name'] ?? 'Customer');
    $email        = htmlspecialchars($lead['email'] ?? 'customer@example.com');
    $whatsapp     = htmlspecialchars($lead['whatsapp'] ?? 'N/A');
    $device       = htmlspecialchars($lead['device'] ?? 'Unknown');
    $app          = htmlspecialchars($lead['app'] ?? 'Unknown');
    $createdAt    = htmlspecialchars($lead['created_at'] ?? date('Y-m-d H:i:s'));
    $adminUrl     = rtrim($siteUrl, '/') . '/admin/trials.php';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="margin:0; padding:20px; background:#070913; font-family:sans-serif; color:#ffffff;">
  <div style="max-width:550px; margin:0 auto; background:#0D1126; border:1px solid rgba(255,255,255,0.1); border-radius:18px; padding:24px;">
    <div style="font-size:12px; font-weight:700; color:#38bdf8; text-transform:uppercase; margin-bottom:8px;">● NEW FREE TRIAL LEAD</div>
    <h2 style="margin:0 0 16px; font-size:20px; color:#fff;">Trial Request #TR-{$leadId} — {$customerName}</h2>
    
    <table width="100%" style="font-size:13px; line-height:2; border-collapse:collapse; color:#cbd5e1;">
      <tr><td><strong>Lead Name:</strong></td><td align="right">{$customerName}</td></tr>
      <tr><td><strong>Email:</strong></td><td align="right">{$email}</td></tr>
      <tr><td><strong>WhatsApp:</strong></td><td align="right">{$whatsapp}</td></tr>
      <tr><td><strong>Submitted:</strong></td><td align="right">{$createdAt}</td></tr>
    </table>

    <div style="margin-top:20px; text-align:center;">
      <a href="{$adminUrl}" style="display:inline-block; padding:10px 20px; background:#00d2ff; color:#000; font-weight:700; border-radius:8px; text-decoration:none; font-size:13px;">Open Free Trial Leads in Admin &rarr;</a>
    </div>
  </div>
</body>
</html>
HTML;
}

/**
 * Generate Admin notification email when order is created or paid.
 */
function buildAdminOrderAlertEmail(array $order, bool $isPaid = false): string {
    $siteName     = getSetting('site_name', 'LIVETVMARKET');
    $siteUrl      = getSetting('site_url', 'https://livetvmarket.com');
    $orderId      = $order['id'] ?? '108';
    $customerName = htmlspecialchars($order['customer_name'] ?? 'Customer');
    $email        = htmlspecialchars($order['email'] ?? 'customer@example.com');
    $whatsapp     = htmlspecialchars($order['whatsapp'] ?? 'N/A');
    $planName     = htmlspecialchars($order['plan_name'] ?? '12 Months VIP Access');
    $amount       = number_format((float)($order['amount'] ?? 74.99), 2);
    $currency     = htmlspecialchars($order['currency'] ?? 'USD');
    $channel      = htmlspecialchars($order['payment_channel'] ?? 'Credit Card');
    $txid         = htmlspecialchars($order['transaction_id'] ?? 'N/A');
    $adminUrl     = rtrim($siteUrl, '/') . '/admin/orders.php';
    $statusText   = $isPaid ? 'PAID & APPROVED' : 'NEW ORDER (Pending)';
    $statusColor  = $isPaid ? '#10b981' : '#38bdf8';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="margin:0; padding:20px; background:#070913; font-family:sans-serif; color:#ffffff;">
  <div style="max-width:550px; margin:0 auto; background:#0D1126; border:1px solid rgba(255,255,255,0.1); border-radius:18px; padding:24px;">
    <div style="font-size:12px; font-weight:700; color:{$statusColor}; text-transform:uppercase; margin-bottom:8px;">● {$statusText}</div>
    <h2 style="margin:0 0 16px; font-size:20px; color:#fff;">Order #LTV-{$orderId} ({$currency} {$amount})</h2>
    
    <table width="100%" style="font-size:13px; line-height:2; border-collapse:collapse; color:#cbd5e1;">
      <tr><td><strong>Customer:</strong></td><td align="right">{$customerName}</td></tr>
      <tr><td><strong>Email:</strong></td><td align="right">{$email}</td></tr>
      <tr><td><strong>WhatsApp:</strong></td><td align="right">{$whatsapp}</td></tr>
      <tr><td><strong>Plan:</strong></td><td align="right">{$planName}</td></tr>
      <tr><td><strong>Channel:</strong></td><td align="right">{$channel}</td></tr>
      <tr><td><strong>TXID:</strong></td><td align="right">{$txid}</td></tr>
    </table>

    <div style="margin-top:20px; text-align:center;">
      <a href="{$adminUrl}" style="display:inline-block; padding:10px 20px; background:#00d2ff; color:#000; font-weight:700; border-radius:8px; text-decoration:none; font-size:13px;">Open Admin Orders &rarr;</a>
    </div>
  </div>
</body>
</html>
HTML;
}

// ─── High-Level Event Notification Triggers ───────────────────────────────────

/**
 * Triggered when a new order is initially placed.
 */
function notifyNewOrder(int $orderId): void {
    global $pdo;
    if (!$pdo || $orderId <= 0) return;

    try {
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) return;

        $siteUrl = getSetting('site_url', 'https://livetvmarket.com');
        $adminOrdersUrl = rtrim($siteUrl, '/') . '/admin/orders.php';

        // 1. Send Pushover Push Notification to Admin devices
        $title = "LIVETVMARKET: New Order #LTV-{$order['id']}";
        $msg = "Customer: {$order['customer_name']}\nPlan: {$order['plan_name']}\nAmount: {$order['currency']} {$order['amount']}\nEmail: {$order['email']}\nWhatsApp: {$order['whatsapp']}";
        sendPushoverNotification($title, $msg, $adminOrdersUrl, 0, 'pushover');

        // 2. Send Admin Notification Email if enabled
        $adminEmail = getSetting('smtp_admin_email', getSetting('contact_email', 'admin@livetvmarket.com'));
        if (!empty($adminEmail)) {
            $subject = "[NEW ORDER] #LTV-{$order['id']} - {$order['customer_name']} ({$order['currency']} {$order['amount']})";
            $html = buildAdminOrderAlertEmail($order, false);
            sendSMTPEmail($adminEmail, 'Administrator', $subject, $html);
        }
    } catch (Exception $e) {
        error_log("[NOTIFY NEW ORDER ERROR] " . $e->getMessage());
    }
}

/**
 * Triggered when an order is paid and confirmed.
 */
function notifyOrderPaid(int $orderId, string $txid = ''): void {
    global $pdo;
    if (!$pdo || $orderId <= 0) return;

    try {
        $stmt = $pdo->prepare("SELECT * FROM `orders` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) return;

        if (!empty($txid)) {
            $order['transaction_id'] = $txid;
        }

        $siteUrl = getSetting('site_url', 'https://livetvmarket.com');
        $adminOrdersUrl = rtrim($siteUrl, '/') . '/admin/orders.php';

        // 1. Send High-Priority Pushover Push Notification with 'cashregister' sound
        $title = "💰 Order Paid: #LTV-{$order['id']} ({$order['currency']} {$order['amount']})";
        $msg = "Customer: {$order['customer_name']}\nPlan: {$order['plan_name']}\nEmail: {$order['email']}\nWhatsApp: {$order['whatsapp']}\nMethod: {$order['payment_channel']}" . (!empty($txid) ? "\nTX: {$txid}" : "");
        sendPushoverNotification($title, $msg, $adminOrdersUrl, 1, 'cashregister');

        // 2. Deliver Activation Credentials Email to Customer via SMTP
        if (!empty($order['email'])) {
            $custSubject = "Activation Credentials & Setup Details — Order #LTV-{$order['id']}";
            $custHtml = buildCustomerActivationEmail($order);
            $sendRes = sendSMTPEmail($order['email'], $order['customer_name'] ?: 'Customer', $custSubject, $custHtml);
            error_log("[CUSTOMER EMAIL DELIVERY] Order #{$orderId}: " . ($sendRes['success'] ? 'SUCCESS' : ('FAILED - ' . ($sendRes['error'] ?? ''))));
        }

        // 3. Send Admin Paid Notification Email
        $adminEmail = getSetting('smtp_admin_email', getSetting('contact_email', 'admin@livetvmarket.com'));
        if (!empty($adminEmail)) {
            $adminSubject = "💰 [PAYMENT CONFIRMED] #LTV-{$order['id']} - {$order['customer_name']} ({$order['currency']} {$order['amount']})";
            $adminHtml = buildAdminOrderAlertEmail($order, true);
            sendSMTPEmail($adminEmail, 'Administrator', $adminSubject, $adminHtml);
        }
    } catch (Exception $e) {
        error_log("[NOTIFY ORDER PAID ERROR] " . $e->getMessage());
    }
}

/**
 * Triggered when a visitor submits a free trial request.
 * Delivers customer confirmation email, admin notification email, and Pushover push alert.
 */
function notifyTrialLead(int $leadId): void {
    global $pdo;
    if (!$pdo || $leadId <= 0) return;

    try {
        $stmt = $pdo->prepare("SELECT * FROM `trial_leads` WHERE `id` = ? LIMIT 1");
        $stmt->execute([$leadId]);
        $lead = $stmt->fetch();
        if (!$lead) return;

        $siteUrl = getSetting('site_url', 'https://livetvmarket.com');
        $adminTrialsUrl = rtrim($siteUrl, '/') . '/admin/trials.php';

        // 1. Send Pushover Push Notification to Admin devices (e.g. pixel8)
        $title = "🎁 Free Trial: {$lead['full_name']}";
        $msg = "Customer: {$lead['full_name']}\nEmail: {$lead['email']}\nWhatsApp: {$lead['whatsapp']}";
        sendPushoverNotification($title, $msg, $adminTrialsUrl, 0, 'pushover');

        // 2. Deliver Confirmation Email to Customer via SMTP
        if (!empty($lead['email'])) {
            $custSubject = "Your Free 24h Trial Request Has Been Received — LIVETVMARKET";
            $custHtml = buildCustomerTrialEmail($lead);
            $sendRes = sendSMTPEmail($lead['email'], $lead['full_name'] ?: 'Customer', $custSubject, $custHtml);
            error_log("[CUSTOMER TRIAL EMAIL] Lead #{$leadId}: " . ($sendRes['success'] ? 'SUCCESS' : ('FAILED - ' . ($sendRes['error'] ?? ''))));
        }

        // 3. Send Admin Notification Email via SMTP
        $adminEmail = getSetting('smtp_admin_email', getSetting('contact_email', 'admin@livetvmarket.com'));
        if (!empty($adminEmail)) {
            $adminSubject = "🎁 [NEW TRIAL REQUEST] #TR-{$lead['id']} - {$lead['full_name']}";
            $adminHtml = buildAdminTrialAlertEmail($lead);
            sendSMTPEmail($adminEmail, 'Administrator', $adminSubject, $adminHtml);
        }
    } catch (Exception $e) {
        error_log("[NOTIFY TRIAL LEAD ERROR] " . $e->getMessage());
    }
}


