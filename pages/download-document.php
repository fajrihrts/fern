<?php
// Download Document Handler
// Route: /dokumen/{registration_id}/{document_type}/{index?}

$user = auth();
$path = getCurrentPath();

// Parse URL: /dokumen/{registration_id}/{document_type}/{index?}
$parts = explode('/', trim($path, '/'));

if (count($parts) < 3 || $parts[0] !== 'dokumen') {
    http_response_code(404);
    die('Invalid document URL');
}

$registrationId = $parts[1] ?? '';
$documentType = $parts[2] ?? '';
$certificateIndex = $parts[3] ?? null;

// Validate inputs
if (empty($registrationId) || empty($documentType)) {
    http_response_code(400);
    die('Missing parameters');
}

// Get registration
$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->execute([$registrationId]);
$registration = $stmt->fetch();

if (!$registration) {
    http_response_code(404);
    die('Registration not found');
}

// Check access permissions
$hasAccess = false;

if ($user['role'] === 'super_admin' || $user['role'] === 'admin') {
    // Admin has access to all documents
    $hasAccess = true;
} elseif ($user['role'] === 'peserta') {
    // Peserta can only access their own documents
    if ($registration['user_id'] === $user['id']) {
        $hasAccess = true;
    }
}

if (!$hasAccess) {
    http_response_code(403);
    die('Access denied. You do not have permission to view this document.');
}

// Get file path based on document type
$filePath = null;
$fileName = null;

switch ($documentType) {
    case 'proposal':
        $filePath = $registration['proposal_file'];
        $fileName = 'Proposal_' . $registration['name'] . '.pdf';
        break;
    
    case 'transcript':
        $filePath = $registration['transcript_file'];
        $fileName = 'Transkrip_' . $registration['name'] . '.pdf';
        break;
    
    case 'recommendation_letter':
        $filePath = $registration['recommendation_letter_file'];
        $fileName = 'Surat_Rekomendasi_' . $registration['name'] . '.pdf';
        break;
    
    case 'certificate':
        if ($certificateIndex !== null && $registration['certificate_files']) {
            $certificates = json_decode($registration['certificate_files'], true);
            if (is_array($certificates) && isset($certificates[$certificateIndex])) {
                $filePath = $certificates[$certificateIndex];
                $fileName = 'Sertifikat_' . ($certificateIndex + 1) . '_' . $registration['name'] . '.pdf';
            }
        }
        break;
    
    default:
        http_response_code(400);
        die('Invalid document type');
}

if (!$filePath) {
    http_response_code(404);
    die('Document not found');
}

// Full file path
$fullPath = BASE_PATH . '/uploads/' . $filePath;

if (!file_exists($fullPath)) {
    http_response_code(404);
    die('File not found on server');
}

// Log activity
ActivityLog::log(
    ActivityLog::ACTION_VIEW,
    "User downloaded document: {$documentType}",
    null,
    [
        'registration_id' => $registrationId,
        'document_type' => $documentType,
        'file_path' => $filePath
    ]
);

// Serve file
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($fullPath);
exit;
