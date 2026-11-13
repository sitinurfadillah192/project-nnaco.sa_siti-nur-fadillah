<?php
/**
 * NNACOSA Local SMTP Sandbox Server (RFC 5321)
 * Captures outgoing emails on port 1025 for instant testing without real passwords.
 *
 * Usage:
 *   php scripts/local-smtp-server.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access denied: CLI only.\n");
}

$port = 1025;
$host = '127.0.0.1';
$inboxDir = dirname(__DIR__) . '/public/mail_inbox';

if (!is_dir($inboxDir)) {
    mkdir($inboxDir, 0777, true);
}

$server = @stream_socket_server("tcp://$host:$port", $errno, $errstr);
if (!$server) {
    die("[ERROR] Could not start SMTP server on $host:$port - $errstr ($errno)\n");
}

echo "============================================================\n";
echo "NNACOSA Local SMTP Sandbox Server\n";
echo "============================================================\n";
echo "Listening on : tcp://$host:$port\n";
echo "Mail preview : $inboxDir\n";
echo "Web URL      : http://localhost/nnco_sa/public/mail_inbox/\n";
echo "Status       : READY (Waiting for outgoing emails...)\n";
echo "Press Ctrl+C to stop.\n";
echo "============================================================\n\n";

while ($client = @stream_socket_accept($server, -1)) {
    fwrite($client, "220 NNACOSA Local Mail Sandbox ESMTP Ready\r\n");
    $dataMode = false;
    $rawMessage = '';
    $from = '';
    $to = [];

    while (!feof($client)) {
        $line = fgets($client);
        if ($line === false) break;

        if ($dataMode) {
            if (rtrim($line, "\r\n") === '.') {
                $dataMode = false;
                fwrite($client, "250 2.0.0 OK: message queued " . uniqid() . "\r\n");

                // Process and save received email
                $timestamp = date('Y-m-d_H-i-s');
                $id = uniqid();
                
                // Parse subject
                preg_match('/^Subject:\s*(.*?)$/mi', $rawMessage, $subMatch);
                $subject = !empty($subMatch[1]) ? trim($subMatch[1]) : 'No Subject';

                // Decode MIME encoded subject if present
                if (function_exists('iconv_mime_decode')) {
                    $subject = iconv_mime_decode($subject, 0, 'UTF-8');
                }

                // Parse HTML body if multipart
                $htmlBody = '';
                if (preg_match('/Content-Type: multipart\/alternative; boundary="([^"]+)"/i', $rawMessage, $bMatch)) {
                    $boundary = $bMatch[1];
                    $parts = explode('--' . $boundary, $rawMessage);
                    foreach ($parts as $part) {
                        if (stripos($part, 'Content-Type: text/html') !== false) {
                            $sections = explode("\r\n\r\n", $part, 2);
                            $bodyPart = $sections[1] ?? '';
                            if (stripos($sections[0], 'Content-Transfer-Encoding: base64') !== false) {
                                $htmlBody = base64_decode($bodyPart);
                            } elseif (stripos($sections[0], 'Content-Transfer-Encoding: quoted-printable') !== false) {
                                $htmlBody = quoted_printable_decode($bodyPart);
                            } else {
                                $htmlBody = $bodyPart;
                            }
                            break;
                        }
                    }
                }

                if (empty($htmlBody)) {
                    $htmlBody = nl2br(htmlspecialchars($rawMessage));
                }

                $filename = "email_{$timestamp}_{$id}.html";
                $filePath = $inboxDir . '/' . $filename;
                $latestPath = $inboxDir . '/latest.html';

                file_put_contents($filePath, $htmlBody);
                file_put_contents($latestPath, $htmlBody);
                file_put_contents($inboxDir . "/email_{$timestamp}_{$id}.eml", $rawMessage);

                // Update index list
                updateInboxIndex($inboxDir);

                $recipients = implode(', ', $to);
                echo "[" . date('H:i:s') . "] EMAIL RECEIVED!\n";
                echo "  To     : $recipients\n";
                echo "  Subject: $subject\n";
                echo "  Saved  : public/mail_inbox/$filename\n";
                echo "  Preview: http://localhost/nnco_sa/public/mail_inbox/latest.html\n\n";

                continue;
            }
            $rawMessage .= $line;
            continue;
        }

        $cmd = trim($line);
        $upper = strtoupper($cmd);

        if (strpos($upper, 'EHLO') === 0 || strpos($upper, 'HELO') === 0) {
            fwrite($client, "250-localhost\r\n250-8BITMIME\r\n250-SIZE 10485760\r\n250 OK\r\n");
        } elseif (strpos($upper, 'MAIL FROM:') === 0) {
            $from = trim(substr($cmd, 10));
            fwrite($client, "250 2.1.0 OK\r\n");
        } elseif (strpos($upper, 'RCPT TO:') === 0) {
            $to[] = trim(substr($cmd, 8));
            fwrite($client, "250 2.1.5 OK\r\n");
        } elseif ($upper === 'DATA') {
            $dataMode = true;
            $rawMessage = '';
            fwrite($client, "354 Start mail input; end with <CRLF>.<CRLF>\r\n");
        } elseif ($upper === 'QUIT') {
            fwrite($client, "221 2.0.0 Bye\r\n");
            break;
        } elseif ($upper === 'RSET') {
            $from = '';
            $to = [];
            $rawMessage = '';
            fwrite($client, "250 2.0.0 OK Reset\r\n");
        } elseif ($upper === 'NOOP') {
            fwrite($client, "250 2.0.0 OK\r\n");
        } else {
            fwrite($client, "250 2.0.0 OK\r\n");
        }
    }

    fclose($client);
}

fclose($server);

function updateInboxIndex($dir) {
    $files = glob($dir . '/email_*.html');
    rsort($files);

    $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>NNACOSA Local Mail Sandbox</title>";
    $html .= "<style>body{font-family:sans-serif;padding:30px;background:#f8fafc;color:#1e293b;}";
    $html .= "h1{color:#4f46e5;margin-bottom:8px;}";
    $html .= ".card{background:white;padding:20px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);max-width:800px;margin:20px auto;}";
    $html .= "table{width:100%;border-collapse:collapse;margin-top:15px;}th,td{padding:12px;border-bottom:1px solid #e2e8f0;text-align:left;}";
    $html .= "a{color:#3b82f6;text-decoration:none;font-weight:600;}a:hover{text-decoration:underline;}";
    $html .= ".badge{background:#10b981;color:white;padding:3px 8px;border-radius:12px;font-size:12px;}</style></head><body>";
    $html .= "<div class='card'><h1>📬 NNACOSA Local Mail Sandbox Inbox</h1>";
    $html .= "<p style='color:#64748b;'>Seluruh email notifikasi pesanan yang terkirim dari localhost otomatis ditangkap di sini.</p>";
    $html .= "<table><thead><tr><th>Waktu</th><th>File Email</th><th>Aksi</th></tr></thead><tbody>";

    foreach ($files as $file) {
        $basename = basename($file);
        $time = date('d M Y H:i:s', filemtime($file));
        $html .= "<tr><td>$time</td><td><code>$basename</code></td><td><a href='$basename' target='_blank'>Buka Email &rarr;</a></td></tr>";
    }

    if (empty($files)) {
        $html .= "<tr><td colspan='3' style='text-align:center;color:#94a3b8;'>Belum ada email yang masuk.</td></tr>";
    }

    $html .= "</tbody></table></div></body></html>";
    file_put_contents($dir . '/index.html', $html);
}
