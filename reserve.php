<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function jsonResponse(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function clean(?string $value): string
{
    return trim((string)$value);
}

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatMoney(float $amount): string
{
    return '$' . number_format($amount, 0, ',', '.');
}

function buildClientEmail(array $reservation, array $config): string
{
    $siteName = esc($config['site']['name']);
    $paymentLabel = $reservation['payment_type'] === 'deposit_50' ? 'Seña del 50%' : 'Pago total';

    $paymentMethods = [
        'mercadopago' => 'MercadoPago',
        'transferencia' => 'Transferencia',
        'efectivo' => 'Efectivo',
    ];

    $paymentMethodLabel = $paymentMethods[$reservation['payment_method']] ?? $reservation['payment_method'];

    $pricingHtml = '';
    if ($reservation['pricing_mode'] === 'per_person') {
        $pricingHtml = '
            <tr>
                <td style="padding:14px;border:1px solid #eaecf0;background:#fcfcfd;"><strong>Adultos</strong></td>
                <td style="padding:14px;border:1px solid #eaecf0;">' . (int)$reservation['adults'] . ' × ' . formatMoney((float)$reservation['adult_price']) . '</td>
            </tr>
            <tr>
                <td style="padding:14px;border:1px solid #eaecf0;background:#fcfcfd;"><strong>Niños / jóvenes</strong></td>
                <td style="padding:14px;border:1px solid #eaecf0;">' . (int)$reservation['youth'] . ' × ' . formatMoney((float)$reservation['youth_price']) . '</td>
            </tr>
        ';
    }

    $paymentInstructions = '';
    if ($reservation['payment_method'] === 'transferencia') {
        $paymentInstructions = '
            <div style="margin-top:20px;padding:18px;border:1px solid #e4e7ec;border-radius:14px;background:#fafafa;">
                <h3 style="margin:0 0 12px;font-size:18px;color:#101828;">Datos para transferencia</h3>
                <ul style="margin:0;padding-left:18px;color:#475467;line-height:1.8;">
                    <li><strong>Alias:</strong> ' . esc($config['payment']['transferencia_alias']) . '</li>
                    <li><strong>CBU:</strong> ' . esc($config['payment']['transferencia_cbu']) . '</li>
                    <li><strong>Titular:</strong> ' . esc($config['payment']['transferencia_titular']) . '</li>
                </ul>
            </div>
        ';
    } elseif ($reservation['payment_method'] === 'mercadopago') {
        $paymentInstructions = '
            <div style="margin-top:20px;padding:18px;border:1px solid #e4e7ec;border-radius:14px;background:#fafafa;">
                <p style="margin:0;color:#475467;line-height:1.7;">
                    Podrás completar el pago desde el enlace de MercadoPago luego de la confirmación.
                </p>
            </div>
        ';
    } else {
        $paymentInstructions = '
            <div style="margin-top:20px;padding:18px;border:1px solid #e4e7ec;border-radius:14px;background:#fafafa;">
                <p style="margin:0;color:#475467;line-height:1.7;">
                    El pago en efectivo queda sujeto a coordinación con la administración.
                </p>
            </div>
        ';
    }

    return '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Reserva recibida</title>
    </head>
    <body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1d2939;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:24px 0;">
            <tr>
                <td align="center">
                    <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="width:640px;max-width:100%;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e4e7ec;">
                        <tr>
                            <td style="background:linear-gradient(135deg,#1f6b4f,#114535);padding:28px 32px;color:#ffffff;">
                                <div style="font-size:12px;letter-spacing:.12em;text-transform:uppercase;opacity:.85;">Reserva recibida</div>
                                <h1 style="margin:10px 0 0;font-size:28px;line-height:1.1;">Gracias por reservar en ' . $siteName . '</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:28px 32px;">
                                <p style="margin:0 0 18px;color:#475467;font-size:15px;line-height:1.7;">
                                    Hola <strong>' . esc($reservation['full_name']) . '</strong>, recibimos tu solicitud de reserva.
                                </p>

                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="padding:14px;border:1px solid #eaecf0;background:#fcfcfd;"><strong>Paquete</strong></td>
                                        <td style="padding:14px;border:1px solid #eaecf0;">' . esc($reservation['package_title']) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:14px;border:1px solid #eaecf0;background:#fcfcfd;"><strong>Duración</strong></td>
                                        <td style="padding:14px;border:1px solid #eaecf0;">' . (int)$reservation['package_days'] . ' día(s) / ' . (int)$reservation['package_nights'] . ' noche(s)</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:14px;border:1px solid #eaecf0;background:#fcfcfd;"><strong>Check-in</strong></td>
                                        <td style="padding:14px;border:1px solid #eaecf0;">' . esc($reservation['checkin']) . ' - 11:00 hs</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:14px;border:1px solid #eaecf0;background:#fcfcfd;"><strong>Check-out</strong></td>
                                        <td style="padding:14px;border:1px solid #eaecf0;">' . esc($reservation['checkout']) . ' - 10:00 hs</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:14px;border:1px solid #eaecf0;background:#fcfcfd;"><strong>Total de personas</strong></td>
                                        <td style="padding:14px;border:1px solid #eaecf0;">' . (int)$reservation['guests'] . '</td>
                                    </tr>
                                    ' . $pricingHtml . '
                                    <tr>
                                        <td style="padding:14px;border:1px solid #eaecf0;background:#fcfcfd;"><strong>Modalidad de pago</strong></td>
                                        <td style="padding:14px;border:1px solid #eaecf0;">' . esc($paymentLabel) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:14px;border:1px solid #eaecf0;background:#fcfcfd;"><strong>Método de pago</strong></td>
                                        <td style="padding:14px;border:1px solid #eaecf0;">' . esc($paymentMethodLabel) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:14px;border:1px solid #eaecf0;background:#fcfcfd;"><strong>Total</strong></td>
                                        <td style="padding:14px;border:1px solid #eaecf0;">' . formatMoney((float)$reservation['calculated_total']) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:14px;border:1px solid #eaecf0;background:#fcfcfd;"><strong>A pagar ahora</strong></td>
                                        <td style="padding:14px;border:1px solid #eaecf0;color:#1f6b4f;font-weight:bold;">' . formatMoney((float)$reservation['due_now']) . '</td>
                                    </tr>
                                </table>

                                ' . $paymentInstructions . '

                                <div style="margin-top:20px;padding:18px;border-radius:14px;background:rgba(220,178,74,.12);border:1px solid rgba(220,178,74,.28);">
                                    <p style="margin:0;color:#5d470f;line-height:1.7;">
                                        <strong>Importante:</strong> la reserva queda sujeta a validación de disponibilidad y acreditación del pago o seña correspondiente.
                                    </p>
                                </div>

                                <p style="margin:24px 0 0;color:#475467;line-height:1.7;">
                                    Ante cualquier duda, escribinos a <strong>' . esc($config['mail']['reply_to']) . '</strong>.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:20px 32px;background:#f9fafb;border-top:1px solid #eaecf0;color:#667085;font-size:13px;">
                                ' . $siteName . ' · ' . esc($config['site']['year']) . ' © Todos los derechos reservados
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}

function buildAdminEmail(array $reservation): string
{
    $paymentLabel = $reservation['payment_type'] === 'deposit_50' ? 'Seña del 50%' : 'Pago total';

    $paymentMethods = [
        'mercadopago' => 'MercadoPago',
        'transferencia' => 'Transferencia',
        'efectivo' => 'Efectivo',
    ];

    $paymentMethodLabel = $paymentMethods[$reservation['payment_method']] ?? $reservation['payment_method'];

    $pricingRows = '';
    if ($reservation['pricing_mode'] === 'per_person') {
        $pricingRows = '
            <tr><td style="border:1px solid #eee;padding:10px;"><strong>Adultos</strong></td><td style="border:1px solid #eee;padding:10px;">' . (int)$reservation['adults'] . ' × ' . formatMoney((float)$reservation['adult_price']) . '</td></tr>
            <tr><td style="border:1px solid #eee;padding:10px;"><strong>Niños / jóvenes</strong></td><td style="border:1px solid #eee;padding:10px;">' . (int)$reservation['youth'] . ' × ' . formatMoney((float)$reservation['youth_price']) . '</td></tr>
        ';
    }

    return '
    <html lang="es">
    <head><meta charset="UTF-8"><title>Nueva reserva</title></head>
    <body style="font-family:Arial,Helvetica,sans-serif;background:#f7f7f7;padding:24px;">
        <div style="max-width:760px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;">
            <div style="background:#1f6b4f;color:#fff;padding:22px 26px;">
                <h1 style="margin:0;font-size:26px;">Nueva reserva recibida</h1>
            </div>
            <div style="padding:24px 26px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Paquete</strong></td><td style="border:1px solid #eee;padding:10px;">' . esc($reservation['package_title']) . '</td></tr>
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Cliente</strong></td><td style="border:1px solid #eee;padding:10px;">' . esc($reservation['full_name']) . '</td></tr>
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Email</strong></td><td style="border:1px solid #eee;padding:10px;">' . esc($reservation['email']) . '</td></tr>
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Teléfono</strong></td><td style="border:1px solid #eee;padding:10px;">' . esc($reservation['phone']) . '</td></tr>
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Total personas</strong></td><td style="border:1px solid #eee;padding:10px;">' . (int)$reservation['guests'] . '</td></tr>
                    ' . $pricingRows . '
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Check-in</strong></td><td style="border:1px solid #eee;padding:10px;">' . esc($reservation['checkin']) . ' 11:00 hs</td></tr>
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Check-out</strong></td><td style="border:1px solid #eee;padding:10px;">' . esc($reservation['checkout']) . ' 10:00 hs</td></tr>
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Duración</strong></td><td style="border:1px solid #eee;padding:10px;">' . (int)$reservation['package_days'] . ' día(s) / ' . (int)$reservation['package_nights'] . ' noche(s)</td></tr>
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Modalidad</strong></td><td style="border:1px solid #eee;padding:10px;">' . esc($paymentLabel) . '</td></tr>
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Método</strong></td><td style="border:1px solid #eee;padding:10px;">' . esc($paymentMethodLabel) . '</td></tr>
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Total</strong></td><td style="border:1px solid #eee;padding:10px;">' . formatMoney((float)$reservation['calculated_total']) . '</td></tr>
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>A pagar ahora</strong></td><td style="border:1px solid #eee;padding:10px;">' . formatMoney((float)$reservation['due_now']) . '</td></tr>
                    <tr><td style="border:1px solid #eee;padding:10px;"><strong>Observaciones</strong></td><td style="border:1px solid #eee;padding:10px;">' . nl2br(esc($reservation['notes'])) . '</td></tr>
                </table>
            </div>
        </div>
    </body>
    </html>';
}

function buildWhatsappUrl(array $reservation, string $adminNumber): string
{
    $paymentLabel = $reservation['payment_type'] === 'deposit_50' ? 'Seña 50%' : 'Pago total';

    $body = "Nueva reserva\n";
    $body .= "Paquete: {$reservation['package_title']}\n";
    $body .= "Cliente: {$reservation['full_name']}\n";
    $body .= "Email: {$reservation['email']}\n";
    $body .= "Tel: {$reservation['phone']}\n";
    $body .= "Personas: {$reservation['guests']}\n";

    if ($reservation['pricing_mode'] === 'per_person') {
        $body .= "Adultos: {$reservation['adults']}\n";
        $body .= "Niños/Jóvenes: {$reservation['youth']}\n";
    }

    $body .= "Check-in: {$reservation['checkin']} 11:00 hs\n";
    $body .= "Check-out: {$reservation['checkout']} 10:00 hs\n";
    $body .= "Pago: {$paymentLabel}\n";
    $body .= "Método: {$reservation['payment_method']}\n";
    $body .= "Total: " . formatMoney((float)$reservation['calculated_total']) . "\n";
    $body .= "A pagar ahora: " . formatMoney((float)$reservation['due_now']);

    return 'https://wa.me/' . $adminNumber . '?text=' . rawurlencode($body);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, [
        'success' => false,
        'message' => 'Método no permitido.'
    ]);
}

$packageId = clean($_POST['package_id'] ?? '');
$fullName = clean($_POST['full_name'] ?? '');
$email = clean($_POST['email'] ?? '');
$phone = clean($_POST['phone'] ?? '');
$guests = (int)($_POST['guests'] ?? 0);
$adults = (int)($_POST['adults'] ?? 0);
$youth = (int)($_POST['youth'] ?? 0);
$checkin = clean($_POST['checkin'] ?? '');
$checkout = clean($_POST['checkout'] ?? '');
$paymentType = clean($_POST['payment_type'] ?? '');
$paymentMethod = clean($_POST['payment_method'] ?? '');
$notes = clean($_POST['notes'] ?? '');
$frontendCalculatedTotal = (float)($_POST['calculated_total'] ?? 0);

if ($packageId === '' || !isset($config['packages'][$packageId])) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'El paquete seleccionado no es válido.'
    ]);
}

$package = $config['packages'][$packageId];

if (
    $fullName === '' ||
    $email === '' ||
    $phone === '' ||
    $guests < 1 ||
    $checkin === '' ||
    $checkout === '' ||
    $paymentType === '' ||
    $paymentMethod === ''
) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Faltan datos obligatorios en la reserva.'
    ]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'El email ingresado no es válido.'
    ]);
}

$validPaymentTypes = ['total', 'deposit_50'];
$validPaymentMethods = ['mercadopago', 'transferencia', 'efectivo'];

if (!in_array($paymentType, $validPaymentTypes, true)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Modalidad de pago inválida.'
    ]);
}

if (!in_array($paymentMethod, $validPaymentMethods, true)) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Método de pago inválido.'
    ]);
}

// Validación simple de fechas
$checkinTs = strtotime($checkin);
$checkoutTs = strtotime($checkout);

if (!$checkinTs || !$checkoutTs) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'Las fechas indicadas no son válidas.'
    ]);
}

if ($checkoutTs <= $checkinTs) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'La fecha de check-out debe ser posterior al check-in.'
    ]);
}

// Cálculo total backend
$pricingMode = $package['pricing_mode'];
$calculatedTotal = 0.0;
$adultPrice = 0.0;
$youthPrice = 0.0;

if ($pricingMode === 'per_person') {
    $adultPrice = (float)$package['adult_price'];
    $youthPrice = (float)$package['youth_price'];

    if ($adults < 0 || $youth < 0) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'La cantidad de adultos y niños/jóvenes no es válida.'
        ]);
    }

    $realGuests = $adults + $youth;
    if ($realGuests <= 0) {
        jsonResponse(422, [
            'success' => false,
            'message' => 'Debe ingresar al menos una persona.'
        ]);
    }

    if ($realGuests !== $guests) {
        $guests = $realGuests;
    }

    $calculatedTotal = ($adults * $adultPrice) + ($youth * $youthPrice);
} else {
    $calculatedTotal = (float)$package['package_price'];
}

if ($calculatedTotal <= 0) {
    jsonResponse(422, [
        'success' => false,
        'message' => 'No se pudo calcular el total de la reserva.'
    ]);
}

$dueNow = $paymentType === 'deposit_50'
    ? round($calculatedTotal * 0.5, 2)
    : $calculatedTotal;

// Podés dejar esta validación flexible por diferencias menores
if ($frontendCalculatedTotal > 0 && abs($frontendCalculatedTotal - $calculatedTotal) > 1) {
    // No frenamos la reserva: confiamos en el backend
}

$reservation = [
    'package_id' => $package['id'],
    'package_title' => $package['title'],
    'package_days' => (int)$package['days'],
    'package_nights' => (int)$package['nights'],
    'pricing_mode' => $pricingMode,
    'package_price' => $package['package_price'] ?? 0,
    'adult_price' => $adultPrice,
    'youth_price' => $youthPrice,
    'full_name' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'guests' => $guests,
    'adults' => $adults,
    'youth' => $youth,
    'checkin' => $checkin,
    'checkout' => $checkout,
    'payment_type' => $paymentType,
    'payment_method' => $paymentMethod,
    'notes' => $notes,
    'calculated_total' => $calculatedTotal,
    'due_now' => $dueNow,
];

$clientEmailHtml = buildClientEmail($reservation, $config);
$adminEmailHtml = buildAdminEmail($reservation);

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $config['mail']['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['mail']['username'];
    $mail->Password   = $config['mail']['password'];
    $mail->SMTPSecure = $config['mail']['encryption'];
    $mail->Port       = (int)$config['mail']['port'];
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($config['mail']['from_email'], $config['mail']['from_name']);
    $mail->addReplyTo($config['mail']['reply_to'], $config['mail']['from_name']);

    foreach ($config['mail']['admin_emails'] as $adminEmail) {
        $mail->addAddress($adminEmail);
    }

    $mail->isHTML(true);
    $mail->Subject = 'Nueva reserva - ' . $reservation['package_title'] . ' - ' . $reservation['full_name'];
    $mail->Body    = $adminEmailHtml;
    $mail->AltBody = 'Nueva reserva recibida';
    $mail->send();

    $mail->clearAddresses();
    $mail->clearReplyTos();

    $mail->addReplyTo($config['mail']['reply_to'], $config['mail']['from_name']);
    $mail->addAddress($reservation['email'], $reservation['full_name']);
    $mail->Subject = 'Recibimos tu reserva - ' . $reservation['package_title'];
    $mail->Body    = $clientEmailHtml;
    $mail->AltBody = 'Hola ' . $reservation['full_name'] . ', recibimos tu reserva.';
    $mail->send();

    $whatsappUrl = buildWhatsappUrl($reservation, $config['whatsapp']['admin_number']);

    $paymentRedirect = null;
    $message = 'Te enviamos la confirmación por email y se generó el aviso de WhatsApp.';

    if ($paymentMethod === 'mercadopago') {
        $paymentRedirect = $config['payment']['mercadopago_checkout_url'];
        $message .= ' Continuá con el pago en MercadoPago.';
    } elseif ($paymentMethod === 'transferencia') {
        $message .= ' Revisá en tu email los datos para realizar la transferencia.';
    } else {
        $message .= ' El pago en efectivo queda sujeto a coordinación con la administración.';
    }

    jsonResponse(200, [
        'success' => true,
        'message' => $message,
        'payment_redirect' => $paymentRedirect,
        'whatsapp_url' => $whatsappUrl,
        'calculated_total' => $calculatedTotal,
        'due_now' => $dueNow
    ]);
} catch (Exception $e) {
    jsonResponse(500, [
        'success' => false,
        'message' => 'La reserva fue validada, pero ocurrió un error al enviar emails: ' . $e->getMessage()
    ]);
}