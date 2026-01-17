<?php
// إعدادات سيرفر مريم الخاصة
$server_ip = "emerald.magmanode.com";
$port = 29510;
$public_key = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxqSI7CMcmTxYtYIfZMd6p3QIb0XcVtSjXj2WAzDjavBM2hDK9vuaw6ZbsDVpUqz9kYJfqRbrx/mC+ar6aSlZ8o29F40IILNQxiEwBhQV/0AV4T8YCwpQiv0js60adJYQOF2BhQQgh3SC/zvm3oJDIG/MEhsjX4icxSPITFfenuzFYAj6IXdQxIoYZUkeq9UoafuGZbmVyXHVFGAZgoDHcM2L8uXidBYTuci4ith+5wgWVDQCIyrboH9z9THWHY6fVClzdCZ1a1cdNJ+xMcCFXqPmvNev5WKwOefL4i9d/j1YPM1/ak1twGmnISkJT8OrxGf79FJHh1c8slN8B5bAeQIDAQAB";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';

    if (empty($username)) {
        echo json_encode(["status" => "error", "message" => "اسم اللاعب مفقود"]);
        exit;
    }

    // تجهيز حزمة التصويت (تنسيق Votifier v1)
    $timestamp = time();
    $address = $_SERVER['REMOTE_ADDR'];
    $voteData = "VOTE\nRenexaMC_Web\n$username\n$address\n$timestamp\n";

    // تشفير البيانات بالمفتاح العام
    $key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($public_key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
    $publicKeyResource = openssl_get_publickey($key);
    
    if (!$publicKeyResource) {
        echo json_encode(["status" => "error", "message" => "فشل في قراءة المفتاح العام"]);
        exit;
    }

    openssl_public_encrypt($voteData, $encryptedData, $publicKeyResource);

    // إرسال البيانات للسيرفر عبر Socket
    $fp = @fsockopen($server_ip, $port, $errno, $errstr, 3);
    if ($fp) {
        fwrite($fp, $encryptedData);
        fclose($fp);
        echo json_encode(["status" => "success", "message" => "تم إرسال الإشارة بنجاح"]);
    } else {
        echo json_encode(["status" => "error", "message" => "السيرفر لا يستجيب: $errstr"]);
    }
}
?>
