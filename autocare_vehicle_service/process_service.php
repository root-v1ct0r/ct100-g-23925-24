<?php
declare(strict_types=1);
session_start();

/*
 * AutoCare Garage booking processor.
 * The browser estimate is for convenience only. Always calculate the total again here.
 */

$serviceCatalog = [
    'engine_oil' => ['name' => 'Engine Oil Change', 'price' => 3000],
    'brake_inspection' => ['name' => 'Brake Inspection', 'price' => 2000],
    'wheel_alignment' => ['name' => 'Wheel Alignment', 'price' => 3500],
    'computer_diagnosis' => ['name' => 'Computer Diagnosis', 'price' => 2500],
    'full_service' => ['name' => 'Full Service', 'price' => 8000],
    // Individualization requirement for admission-number last digit 5.
    'pickup_delivery' => ['name' => 'Vehicle Pickup & Delivery', 'price' => 1500],
];

function clean(string $value): string {
    return trim($value);
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo '<!DOCTYPE html><html lang="en"><meta charset="UTF-8"><title>Method not allowed</title><body><h1>Submit a booking using the service form</h1><p>Open <a href="service.html">the booking form</a> to send a service request.</p></body></html>';
    exit;
}

$name = clean((string)($_POST['customer_name'] ?? ''));
$phone = clean((string)($_POST['phone'] ?? ''));
$email = clean((string)($_POST['email'] ?? ''));
$registration = strtoupper(clean((string)($_POST['registration'] ?? '')));
$vehicleType = clean((string)($_POST['vehicle_type'] ?? ''));
$model = clean((string)($_POST['model'] ?? ''));
$yearRaw = clean((string)($_POST['year'] ?? ''));
$date = clean((string)($_POST['service_date'] ?? ''));
$submittedServices = $_POST['services'] ?? [];

$errors = [];
$allowedVehicleTypes = ['Saloon Car', 'SUV', 'Pickup', 'Van'];
$currentYear = (int)date('Y');

if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter a customer name of 1 to 100 characters.';
if (!preg_match('/^[0-9]{10}$/', $phone)) $errors[] = 'Phone number must contain exactly 10 digits.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) $errors[] = 'Enter a valid email address.';
if ($registration === '' || strlen($registration) > 20) $errors[] = 'Enter a valid vehicle registration number.';
if (!in_array($vehicleType, $allowedVehicleTypes, true)) $errors[] = 'Select a valid vehicle type.';
if ($model === '' || mb_strlen($model) > 100) $errors[] = 'Enter the vehicle model.';
if (!preg_match('/^[0-9]{4}$/', $yearRaw) || (int)$yearRaw < 1950 || (int)$yearRaw > $currentYear) {
    $errors[] = 'Year of manufacture must be between 1950 and ' . $currentYear . '.';
}
$year = (int)$yearRaw;

$dateObject = DateTime::createFromFormat('Y-m-d', $date);
if (!$dateObject || $dateObject->format('Y-m-d') !== $date || $date < date('Y-m-d')) {
    $errors[] = 'Choose a valid service date today or later.';
}

if (!is_array($submittedServices)) {
    $submittedServices = [];
    $errors[] = 'Invalid service selection.';
}
$selectedServices = [];
foreach ($submittedServices as $key) {
    if (is_string($key) && isset($serviceCatalog[$key])) {
        $selectedServices[$key] = $serviceCatalog[$key];
    } else {
        $errors[] = 'One or more selected services are invalid.';
        break;
    }
}
if (count($selectedServices) === 0) $errors[] = 'Select at least one service.';

if ($errors) {
    http_response_code(422);
    ?>
    <!DOCTYPE html>
    <html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Booking errors | AutoCare Garage</title><link rel="stylesheet" href="assets/style.css"></head>
    <body><header class="site-header"><a class="brand" href="index.html"><span class="brand-mark">AC</span><span>AutoCare <small>GARAGE</small></span></a><nav><a href="index.html">Home</a><a href="service.html">Book a Service</a></nav></header>
    <main class="result-wrap"><section class="result-card error-card"><div class="result-symbol">!</div><p class="eyebrow">BOOKING NOT SUBMITTED</p><h1>Please correct these details</h1><p>The server checked your request and found the following issue(s).</p><ul class="server-errors">
    <?php foreach (array_unique($errors) as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
    </ul><a class="button button-primary" href="service.html">Return to booking form</a></section></main><footer><span>© <?= date('Y') ?> AutoCare Garage</span></footer></body></html>
    <?php
    exit;
}

$total = 0;
foreach ($selectedServices as $service) {
    $total += $service['price'];
}

$_SESSION['last_booking_reference'] = 'AC-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
$reference = $_SESSION['last_booking_reference'];
$serviceDateFormatted = $dateObject->format('d F Y');
$vehicleAge = $currentYear - $year;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Confirmation | AutoCare Garage</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <header class="site-header">
    <a class="brand" href="index.html"><span class="brand-mark">AC</span><span>AutoCare <small>GARAGE</small></span></a>
    <nav aria-label="Main navigation"><a href="index.html">Home</a><a href="service.html">Book a Service</a></nav>
  </header>
  <main class="result-wrap">
    <section class="result-card">
      <div class="result-symbol">✓</div>
      <p class="eyebrow">SERVICE REQUEST RECEIVED</p>
      <h1>Thanks, <?= e($name) ?>.</h1>
      <p class="result-lead">Your service request has been processed. Keep this reference for your records.</p>
      <div class="reference-box"><span>BOOKING REFERENCE</span><strong><?= e($reference) ?></strong></div>
      <div class="confirmation-grid">
        <div><span>Customer</span><strong><?= e($name) ?></strong></div>
        <div><span>Phone</span><strong><?= e($phone) ?></strong></div>
        <div><span>Email</span><strong><?= e($email) ?></strong></div>
        <div><span>Vehicle registration</span><strong><?= e($registration) ?></strong></div>
        <div><span>Vehicle type</span><strong><?= e($vehicleType) ?></strong></div>
        <div><span>Vehicle model</span><strong><?= e($model) ?></strong></div>
        <div><span>Year of manufacture</span><strong><?= e((string)$year) ?> (<?= $vehicleAge ?> years old)</strong></div>
        <div><span>Preferred service date</span><strong><?= e($serviceDateFormatted) ?></strong></div>
      </div>
      <h2 class="confirmed-services-title">Selected services</h2>
      <div class="confirmed-services">
        <?php foreach ($selectedServices as $service): ?>
          <div><span><?= e($service['name']) ?></span><strong>KSh <?= number_format($service['price']) ?></strong></div>
        <?php endforeach; ?>
      </div>
      <div class="confirmed-total"><span>ESTIMATED SERVICE COST</span><strong>KSh <?= number_format($total) ?></strong></div>
      <p class="confirmation-note">This estimate is based on the listed service charges, including the KSh 1,500 vehicle pickup and delivery option where selected. AutoCare Garage should confirm availability and the final cost before work begins.</p>
      <div class="result-actions"><a class="button button-primary" href="service.html">Book another service</a><a class="button button-outline" href="index.html">Return home</a></div>
    </section>
  </main>
  <footer><span>© <?= date('Y') ?> AutoCare Garage</span><span>Reliable care. Clear estimates.</span></footer>
</body>
</html>
