<?php
require_once  __DIR__ . '/helpers/response.php';

mysqli_report(MYSQLI_REPORT_OFF);

$host = "localhost";
$user = "root";
$password = "";
$db = "kampus";

$koneksi = mysqli_connect($host, $user, $password, $db);

if (!$koneksi) {
    sendResponse(
        false,
        "koneksi databse gagal",
        null,
        500
    );
}