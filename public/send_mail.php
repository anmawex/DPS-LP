<?php
/**
 * send_mail.php
 * Contact form handler — Drive Point Solutions
 * Sends form data to support@drivepointsolutions.org via Hostinger SMTP.
 *
 * IMPORTANT: Place this file in your hosting public_html (or equivalent) root.
 * Requires PHPMailer. Install via Composer:
 *   composer require phpmailer/phpmailer
 * Then set PHPMAILER_AUTOLOAD to the correct path below.
 */

// ── CORS headers (only allow your own domain in production) ─────────────────
$allowed_origin = 'https://drivepointsolutions.org'; // Change to your domain

// Allow both www and non-www for convenience
if (isset($_SERVER['HTTP_ORIGIN'])) {
    if (
        $_SERVER['HTTP_ORIGIN'] === $allowed_origin ||
        $_SERVER['HTTP_ORIGIN'] === 'https://www.drivepointsolutions.org'
    ) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    }
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Only allow POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ── PHPMailer autoload ───────────────────────────────────────────────────────
// Option A: using Composer (recommended)
$autoload = __DIR__ . '/vendor/autoload.php';

// Option B: using PHPMailer directly (uncomment and adjust path if not using Composer)
// $autoload = __DIR__ . '/PHPMailer/src/PHPMailer.php';

if (!file_exists($autoload)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error: PHPMailer not found. Please install it via Composer.'
    ]);
    exit;
}

require $autoload;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ── Read & sanitize input ────────────────────────────────────────────────────
$body_raw = file_get_contents('php://input');
$data     = json_decode($body_raw, true);

// Fall back to $_POST if Content-Type is application/x-www-form-urlencoded
if (!$data) {
    $data = $_POST;
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

$fullName = sanitize($data['fullName'] ?? '');
$email    = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone    = sanitize($data['phone']   ?? '');
$service  = sanitize($data['service'] ?? '');
$message  = sanitize($data['message'] ?? '');

// ── Validate required fields ─────────────────────────────────────────────────
if (!$fullName || !$email || !$message) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!$email) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// ── Map service value to a readable label ────────────────────────────────────
$serviceLabels = [
    'auto-loan' => 'Auto Loan',
    'coverage'  => 'Auto Coverage',
    'home-refi' => 'Home Refinancing',
    'credit'    => 'Credit Repair',
];
$serviceLabel = $serviceLabels[$service] ?? ($service ?: 'Not specified');

// ── SMTP credentials (Hostinger) ─────────────────────────────────────────────
// Store these in environment variables or a config file outside public_html in production.
define('SMTP_HOST',     'smtp.hostinger.com');
define('SMTP_PORT',     465);               // 465 = SSL | 587 = TLS/STARTTLS
define('SMTP_USER',     'support@drivepointsolutions.org');
define('SMTP_PASS',     'nF?QM&(]W2.LbgD'); // ← REPLACE with your actual password
define('SMTP_FROM',     'support@drivepointsolutions.org');
define('SMTP_FROM_NAME','Drive Point Solutions');
define('MAIL_TO',       'support@drivepointsolutions.org');

// ── Build & send email ───────────────────────────────────────────────────────
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL on port 465
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    // From / To
    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress(MAIL_TO, 'Drive Point Solutions Support');
    $mail->addReplyTo($email, $fullName); // Reply goes directly to the sender

    // Content
    $mail->isHTML(true);
    $mail->Subject = "New Contact Form Message from {$fullName}";

    // ── HTML email body ──────────────────────────────────────────────────────
    $mail->Body = <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>New Contact Message</title>
    </head>
    <body style="margin:0;padding:0;background:#F7F8FA;font-family:'Segoe UI',Arial,sans-serif;">
      <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F8FA;padding:40px 0;">
        <tr>
          <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

              <!-- Header -->
              <tr>
                <td style="background:#0A1628;padding:32px 40px;text-align:center;">
                  <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">
                    DRIVE POINT SOLUTIONS
                  </h1>
                  <p style="margin:8px 0 0;color:rgba(255,255,255,0.5);font-size:13px;">
                    New Contact Form Submission
                  </p>
                </td>
              </tr>

              <!-- Body -->
              <tr>
                <td style="padding:40px;">
                  <p style="margin:0 0 24px;color:#374151;font-size:15px;line-height:1.6;">
                    You've received a new message through the website contact form. Here are the details:
                  </p>

                  <!-- Fields -->
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="padding:12px 16px;background:#F7F8FA;border-radius:10px;margin-bottom:8px;">
                        <p style="margin:0;font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.08em;">Full Name</p>
                        <p style="margin:4px 0 0;font-size:16px;font-weight:600;color:#0A1628;">{$fullName}</p>
                      </td>
                    </tr>
                    <tr><td style="height:8px;"></td></tr>
                    <tr>
                      <td style="padding:12px 16px;background:#F7F8FA;border-radius:10px;">
                        <p style="margin:0;font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.08em;">Email</p>
                        <p style="margin:4px 0 0;font-size:16px;font-weight:600;color:#1D4ED8;">{$email}</p>
                      </td>
                    </tr>
                    <tr><td style="height:8px;"></td></tr>
                    <tr>
                      <td style="padding:12px 16px;background:#F7F8FA;border-radius:10px;">
                        <p style="margin:0;font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.08em;">Phone</p>
                        <p style="margin:4px 0 0;font-size:16px;font-weight:600;color:#0A1628;">{$phone}</p>
                      </td>
                    </tr>
                    <tr><td style="height:8px;"></td></tr>
                    <tr>
                      <td style="padding:12px 16px;background:#F7F8FA;border-radius:10px;">
                        <p style="margin:0;font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.08em;">Service of Interest</p>
                        <p style="margin:4px 0 0;font-size:16px;font-weight:600;color:#0A1628;">{$serviceLabel}</p>
                      </td>
                    </tr>
                    <tr><td style="height:8px;"></td></tr>
                    <tr>
                      <td style="padding:12px 16px;background:#F7F8FA;border-radius:10px;">
                        <p style="margin:0;font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.08em;">Message</p>
                        <p style="margin:4px 0 0;font-size:15px;color:#374151;line-height:1.7;white-space:pre-wrap;">{$message}</p>
                      </td>
                    </tr>
                  </table>

                  <!-- CTA -->
                  <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:32px;">
                    <tr>
                      <td align="center">
                        <a href="mailto:{$email}" style="display:inline-block;background:#1D4ED8;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:14px 32px;border-radius:999px;letter-spacing:0.03em;">
                          Reply to {$fullName}
                        </a>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

              <!-- Footer -->
              <tr>
                <td style="background:#F7F8FA;padding:20px 40px;text-align:center;border-top:1px solid #E5E7EB;">
                  <p style="margin:0;font-size:12px;color:#9CA3AF;">
                    This message was sent from the Drive Point Solutions contact form.<br>
                    © <?= date('Y') ?> Drive Point Solutions. All rights reserved.
                  </p>
                </td>
              </tr>

            </table>
          </td>
        </tr>
      </table>
    </body>
    </html>
    HTML;

    // Plain text fallback
    $mail->AltBody = <<<TEXT
    New Contact Form Submission — Drive Point Solutions

    Full Name:          {$fullName}
    Email:              {$email}
    Phone:              {$phone}
    Service of Interest:{$serviceLabel}

    Message:
    {$message}

    ---
    Sent from drivepointsolutions.org
    TEXT;

    $mail->send();

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Your message has been sent successfully!']);

} catch (Exception $e) {
    // Log the detailed error server-side, return a generic message to the client
    error_log('[DPS Contact Form] Mailer Error: ' . $mail->ErrorInfo);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'We could not send your message. Please try again later or email us directly at support@drivepointsolutions.org'
    ]);
}
