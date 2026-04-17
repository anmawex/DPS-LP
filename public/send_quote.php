<?php
/**
 * send_quote.php
 * Auto Refinance Calculator — Quote Request Handler
 * Sends the amortization results + contact info to support@drivepointsolutions.org
 *
 * Place this file in public_html (same root as send_mail.php).
 * Requires PHPMailer: composer require phpmailer/phpmailer
 */

// ── CORS headers ─────────────────────────────────────────────────────────────
$allowed_origin = 'https://drivepointsolutions.org';

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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ── PHPMailer autoload ───────────────────────────────────────────────────────
$autoload = __DIR__ . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error: PHPMailer not found.'
    ]);
    exit;
}

require $autoload;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── Read & sanitize input ────────────────────────────────────────────────────
$body_raw = file_get_contents('php://input');
$data     = json_decode($body_raw, true);

if (!$data) {
    $data = $_POST;
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function fmt(float $val): string {
    return '$' . number_format($val, 2);
}

// Contact info
$fullName = sanitize($data['fullName'] ?? '');
$email    = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone    = sanitize($data['phone']    ?? '');

// Calculator inputs
$currentPayment = (float)($data['currentPayment'] ?? 0);
$balance        = (float)($data['balance']        ?? 0);
$currentRate    = (float)($data['currentRate']    ?? 0);
$newRate        = (float)($data['newRate']        ?? 0);
$currentTerm    = (float)($data['currentTerm']    ?? 0);
$newTerm        = (float)($data['newTerm']        ?? 0);

// Calculator results (pre-computed on the frontend)
$newMonthlyPayment  = (float)($data['newMonthlyPayment']  ?? 0);
$monthlySavings     = (float)($data['monthlySavings']     ?? 0);
$totalInterestSaved = (float)($data['totalInterestSaved'] ?? 0);

// ── Validate ─────────────────────────────────────────────────────────────────
if (!$fullName || !$email) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Name and email are required.']);
    exit;
}

if ($balance <= 0 || $newRate <= 0 || $newTerm <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Calculator results are incomplete.']);
    exit;
}

// ── Pre-format currency strings (PHP heredoc can't call functions inline) ────
$fmtCurrentPayment  = fmt($currentPayment);
$fmtBalance         = fmt($balance);
$fmtNewMonthly      = fmt($newMonthlyPayment);
$fmtMonthlySavings  = fmt($monthlySavings);
$fmtInterestSaved   = fmt($totalInterestSaved);
$year               = date('Y');

// ── SMTP credentials ─────────────────────────────────────────────────────────
define('SMTP_HOST',     'smtp.hostinger.com');
define('SMTP_PORT',     465);
define('SMTP_USER',     'support@drivepointsolutions.org');
define('SMTP_PASS',     'nF?QM&(]W2.LbgD'); // ← Hostinger email password
define('SMTP_FROM',     'support@drivepointsolutions.org');
define('SMTP_FROM_NAME','Drive Point Solutions');
define('MAIL_TO',       'support@drivepointsolutions.org');

// ── Send email ────────────────────────────────────────────────────────────────
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress(MAIL_TO, 'Drive Point Solutions Support');
    $mail->addReplyTo($email, $fullName);

    $mail->isHTML(true);
    $mail->Subject = "Auto Refi Quote Request from {$fullName}";

    // ── HTML email body ───────────────────────────────────────────────────────
    $mail->Body = <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Auto Refi Quote Request</title>
    </head>
    <body style="margin:0;padding:0;background:#F7F8FA;font-family:'Segoe UI',Arial,sans-serif;">
      <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F8FA;padding:40px 0;">
        <tr>
          <td align="center">
            <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

              <!-- Header -->
              <tr>
                <td style="background:#0A1628;padding:32px 40px;">
                  <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">
                    DRIVE POINT SOLUTIONS
                  </h1>
                  <p style="margin:8px 0 0;color:rgba(255,255,255,0.5);font-size:13px;">
                    🚗 Auto Refinance Quote Request
                  </p>
                </td>
              </tr>

              <!-- Contact Info -->
              <tr>
                <td style="padding:32px 40px 0;">
                  <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.08em;">Client Information</p>
                  <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F8FA;border-radius:12px;overflow:hidden;">
                    <tr>
                      <td style="padding:14px 18px;border-bottom:1px solid #E5E7EB;">
                        <span style="font-size:11px;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.06em;">Name</span><br>
                        <strong style="font-size:16px;color:#0A1628;">{$fullName}</strong>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:14px 18px;border-bottom:1px solid #E5E7EB;">
                        <span style="font-size:11px;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.06em;">Email</span><br>
                        <strong style="font-size:15px;color:#1D4ED8;">{$email}</strong>
                      </td>
                    </tr>
                    <tr>
                      <td style="padding:14px 18px;">
                        <span style="font-size:11px;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.06em;">Phone</span><br>
                        <strong style="font-size:15px;color:#0A1628;">{$phone}</strong>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

              <!-- Calculator Inputs -->
              <tr>
                <td style="padding:24px 40px 0;">
                  <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.08em;">Calculator Inputs</p>
                  <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F8FA;border-radius:12px;overflow:hidden;">
                    <tr>
                      <td width="50%" style="padding:12px 18px;border-bottom:1px solid #E5E7EB;border-right:1px solid #E5E7EB;">
                        <span style="font-size:11px;color:#9CA3AF;display:block;">Current Monthly Payment</span>
                        <strong style="color:#0A1628;">{$fmtCurrentPayment}</strong>
                      </td>
                      <td width="50%" style="padding:12px 18px;border-bottom:1px solid #E5E7EB;">
                        <span style="font-size:11px;color:#9CA3AF;display:block;">Remaining Balance</span>
                        <strong style="color:#0A1628;">{$fmtBalance}</strong>
                      </td>
                    </tr>
                    <tr>
                      <td width="50%" style="padding:12px 18px;border-bottom:1px solid #E5E7EB;border-right:1px solid #E5E7EB;">
                        <span style="font-size:11px;color:#9CA3AF;display:block;">Current APR</span>
                        <strong style="color:#0A1628;">{$currentRate}%</strong>
                      </td>
                      <td width="50%" style="padding:12px 18px;border-bottom:1px solid #E5E7EB;">
                        <span style="font-size:11px;color:#9CA3AF;display:block;">New APR</span>
                        <strong style="color:#0A1628;">{$newRate}%</strong>
                      </td>
                    </tr>
                    <tr>
                      <td width="50%" style="padding:12px 18px;border-right:1px solid #E5E7EB;">
                        <span style="font-size:11px;color:#9CA3AF;display:block;">Remaining Term</span>
                        <strong style="color:#0A1628;">{$currentTerm} yrs</strong>
                      </td>
                      <td width="50%" style="padding:12px 18px;">
                        <span style="font-size:11px;color:#9CA3AF;display:block;">New Term</span>
                        <strong style="color:#0A1628;">{$newTerm} yrs</strong>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

              <!-- Results -->
              <tr>
                <td style="padding:24px 40px 0;">
                  <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.08em;">Amortization Results</p>
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td width="33%" style="padding:4px;">
                        <div style="background:#EFF6FF;border-radius:12px;padding:16px;text-align:center;">
                          <span style="font-size:10px;color:#3B82F6;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;display:block;">New Monthly Payment</span>
                          <strong style="font-size:22px;color:#1D40BD;display:block;margin-top:6px;">{$fmtNewMonthly}</strong>
                        </div>
                      </td>
                      <td width="33%" style="padding:4px;">
                        <div style="background:#F0FDF4;border-radius:12px;padding:16px;text-align:center;">
                          <span style="font-size:10px;color:#22C55E;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;display:block;">Monthly Savings</span>
                          <strong style="font-size:22px;color:#16A34A;display:block;margin-top:6px;">{$fmtMonthlySavings}</strong>
                        </div>
                      </td>
                      <td width="33%" style="padding:4px;">
                        <div style="background:#EFF6FF;border-radius:12px;padding:16px;text-align:center;">
                          <span style="font-size:10px;color:#3B82F6;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;display:block;">Total Interest Saved</span>
                          <strong style="font-size:22px;color:#1D40BD;display:block;margin-top:6px;">{$fmtInterestSaved}</strong>
                        </div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>

              <!-- CTA -->
              <tr>
                <td style="padding:28px 40px;">
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td align="center">
                        <a href="mailto:{$email}" style="display:inline-block;background:#1D4ED8;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:14px 32px;border-radius:999px;">
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
                    Sent from the Auto Refinance Calculator on drivepointsolutions.org<br>
                    © {$year} Drive Point Solutions. All rights reserved.
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

    // ── Plain text fallback ───────────────────────────────────────────────────
    $mail->AltBody = <<<TEXT
    Auto Refinance Quote Request — Drive Point Solutions

    CLIENT
    Name:  {$fullName}
    Email: {$email}
    Phone: {$phone}

    CALCULATOR INPUTS
    Current Monthly Payment: {$currentPayment}
    Remaining Balance:       {$balance}
    Current APR:             {$currentRate}%
    New APR:                 {$newRate}%
    Remaining Term:          {$currentTerm} yrs
    New Term:                {$newTerm} yrs

    RESULTS
    New Monthly Payment:   {$fmtNewMonthly}
    Monthly Savings:       {$fmtMonthlySavings}
    Total Interest Saved:  {$fmtInterestSaved}

    ---
    Sent from the Auto Refinance Calculator on drivepointsolutions.org
    TEXT;

    $mail->send();

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Quote request sent successfully!']);

} catch (Exception $e) {
    error_log('[DPS Quote] Mailer Error: ' . $mail->ErrorInfo);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Could not send your request. Please email us directly at support@drivepointsolutions.org'
    ]);
}
