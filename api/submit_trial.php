<?php
/**
 * LIVETVMARKET - API: Submit Free 24h Trial Lead
 * Inserts visitor trial request directly into MySQL database 'iptv-website'.
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Read raw JSON or form-encoded POST
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$fullName = trim($input['full_name'] ?? $input['name'] ?? '');
$email = trim($input['email'] ?? '');
$whatsapp = trim($input['whatsapp'] ?? '');
$device = trim($input['device'] ?? 'Firestick');
$app = trim($input['app'] ?? 'IPTV Smarters Pro');

if (empty($fullName) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($whatsapp)) {
    echo json_encode(['success' => false, 'message' => 'Full Name, a valid Email address, and WhatsApp Number are all required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO `trial_leads` (`full_name`, `email`, `whatsapp`, `device`, `app`, `status`) VALUES (?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$fullName, $email, $whatsapp, $device, $app]);
    $leadId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Your 24h free trial request has been registered successfully!',
        'lead_id' => $leadId
    ]);
} catch (Exception $e) {
    error_log("Error saving trial lead: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again or contact WhatsApp support.']);
}
