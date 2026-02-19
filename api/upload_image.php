<?php
// api/upload_image.php
header('Content-Type: application/json');

// 권한 확인 (admin.php와 동일한 세션 로직 필요)
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];
$uploadDir = '../uploads/';

// Uploads 디렉토리가 없으면 생성
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 파일 유효성 검사
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessage = 'File upload error.';
    switch ($file['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $errorMessage = 'File size exceeds the limit (10MB).';
            break;
        case UPLOAD_ERR_PARTIAL:
            $errorMessage = 'The file was only partially uploaded.';
            break;
        case UPLOAD_ERR_NO_FILE:
            $errorMessage = 'No file was uploaded.';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $errorMessage = 'Missing a temporary folder.';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $errorMessage = 'Failed to write file to disk.';
            break;
        case UPLOAD_ERR_EXTENSION:
            $errorMessage = 'A PHP extension stopped the file upload.';
            break;
    }
    http_response_code(400);
    echo json_encode(['error' => $errorMessage]);
    exit;
}

// 10MB 추가 크기 검사 (서버 설정과 이중 체크)
$maxSize = 10 * 1024 * 1024; // 10MB
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'File size exceeds 10MB limit.']);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, GIF, WEBP are allowed.']);
    exit;
}

// 파일명 생성 (충돌 방지)
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_') . '_' . time() . '.' . $extension;
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // 웹에서 접근 가능한 경로 반환
    $webPath = 'uploads/' . $filename;
    echo json_encode(['url' => $webPath]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to move uploaded file.']);
}
?>