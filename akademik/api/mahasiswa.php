```php
<?php

require_once "../config.php";
require_once "../helpers/response.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // =========================
    // GET
    // =========================
    case 'GET':

        // GET berdasarkan ID
        if (isset($_GET['id'])) {

            $id = (int) $_GET['id'];

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT
                    m.id,
                    m.nama,
                    m.nim,
                    j.nama_jurusan AS jurusan
                FROM mahasiswa m
                LEFT JOIN jurusan j ON m.jurusan_id = j.id
                WHERE m.id = ?"
            );

            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);

            $r = mysqli_stmt_get_result($stmt);

            if (!$r) {
                sendResponse(false, "query gagal: " . mysqli_error($koneksi), null, 500);
            }

            $data = mysqli_fetch_assoc($r);

            if (!$data) {
                sendResponse(false, "data mahasiswa tidak ditemukan", null, 404);
            }

            sendResponse(true, "berhasil", $data, 200);
        }

        // SEARCH
        if (isset($_GET['search'])) {

            $search = "%" . $_GET['search'] . "%";

            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT
                    m.id,
                    m.nama,
                    m.nim,
                    j.nama_jurusan AS jurusan
                FROM mahasiswa m
                LEFT JOIN jurusan j ON m.jurusan_id = j.id
                WHERE m.nama LIKE ? OR m.nim LIKE ?
                ORDER BY m.id DESC"
            );

            mysqli_stmt_bind_param($stmt, "ss", $search, $search);
            mysqli_stmt_execute($stmt);

            $r = mysqli_stmt_get_result($stmt);

            if (!$r) {
                sendResponse(false, "query gagal: " . mysqli_error($koneksi), null, 500);
            }

            $data = [];

            while ($row = mysqli_fetch_assoc($r)) {
                $data[] = $row;
            }

            sendResponse(true, "berhasil", $data, 200);
        }

        // PAGINATION
        if (isset($_GET['page']) || isset($_GET['limit'])) {

            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

            if ($page < 1) {
                $page = 1;
            }

            if ($limit < 1) {
                $limit = 10;
            }

            $offset = ($page - 1) * $limit;

            $q = "SELECT
                    m.id,
                    m.nama,
                    m.nim,
                    j.nama_jurusan AS jurusan
                FROM mahasiswa m
                LEFT JOIN jurusan j ON m.jurusan_id = j.id
                ORDER BY m.id DESC
                LIMIT ? OFFSET ?";

            $stmt = mysqli_prepare($koneksi, $q);

            mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
            mysqli_stmt_execute($stmt);

            $r = mysqli_stmt_get_result($stmt);

            if (!$r) {
                sendResponse(false, "query gagal: " . mysqli_error($koneksi), null, 500);
            }

            $data = [];

            while ($row = mysqli_fetch_assoc($r)) {
                $data[] = $row;
            }

            sendResponse(true, "berhasil", $data, 200);
        }

        // GET ALL
        $q = "SELECT
                m.id,
                m.nama,
                m.nim,
                j.nama_jurusan AS jurusan
            FROM mahasiswa m
            LEFT JOIN jurusan j ON m.jurusan_id = j.id
            ORDER BY m.id DESC";

        $r = mysqli_query($koneksi, $q);

        if (!$r) {
            sendResponse(false, "query gagal: " . mysqli_error($koneksi), null, 500);
        }

        $data = [];

        while ($row = mysqli_fetch_assoc($r)) {
            $data[] = $row;
        }

        sendResponse(true, "berhasil", $data, 200);

        break;


    // =========================
    // POST
    // =========================
    case 'POST':

        $input = json_decode(file_get_contents('php://input'), true);

        if (
            !$input ||
            !isset($input['nama']) ||
            !isset($input['nim']) ||
            !isset($input['jurusan_id'])
        ) {
            sendResponse(false, "field nama, nim, jurusan_id wajib", null, 400);
        }

        $nama = $input['nama'];
        $nim = $input['nim'];
        $jurusan_id = (int) $input['jurusan_id'];

        // Cek jurusan
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM jurusan WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $jurusan_id);
        mysqli_stmt_execute($stmt);

        $cek_jurusan = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($cek_jurusan) == 0) {
            sendResponse(false, "jurusan_id tidak valid", null, 400);
        }

        // Cek NIM
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM mahasiswa WHERE nim = ?");
        mysqli_stmt_bind_param($stmt, "s", $nim);
        mysqli_stmt_execute($stmt);

        $cek_nim = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($cek_nim) > 0) {
            sendResponse(false, "nim sudah terdaftar", null, 400);
        }

        // Insert mahasiswa
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO mahasiswa (nama, nim, jurusan_id, created_at)
            VALUES (?, ?, ?, NOW())"
        );

        mysqli_stmt_bind_param($stmt, "ssi", $nama, $nim, $jurusan_id);

        if (mysqli_stmt_execute($stmt)) {
            sendResponse(true, "data tersimpan", [
                "id" => mysqli_insert_id($koneksi)
            ], 201);
        } else {
            sendResponse(false, mysqli_error($koneksi), null, 500);
        }

        break;


    // =========================
    // PUT
    // =========================
    case 'PUT':

        if (!isset($_GET['id'])) {
            sendResponse(false, "id mahasiswa wajib", null, 400);
        }

        $id = (int) $_GET['id'];

        $input = json_decode(file_get_contents('php://input'), true);

        if (
            !$input ||
            !isset($input['nama']) ||
            !isset($input['nim']) ||
            !isset($input['jurusan_id'])
        ) {
            sendResponse(false, "field nama, nim, jurusan_id wajib", null, 400);
        }

        $nama = $input['nama'];
        $nim = $input['nim'];
        $jurusan_id = (int) $input['jurusan_id'];

        // Cek mahasiswa
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM mahasiswa WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $cek_mahasiswa = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($cek_mahasiswa) == 0) {
            sendResponse(false, "mahasiswa tidak ditemukan", null, 404);
        }

        // Cek jurusan
        $stmt = mysqli_prepare($koneksi, "SELECT id FROM jurusan WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $jurusan_id);
        mysqli_stmt_execute($stmt);

        $cek_jurusan = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($cek_jurusan) == 0) {
            sendResponse(false, "jurusan_id tidak valid", null, 400);
        }

        // Cek NIM
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM mahasiswa WHERE nim = ? AND id != ?"
        );

        mysqli_stmt_bind_param($stmt, "si", $nim, $id);
        mysqli_stmt_execute($stmt);

        $cek_nim = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($cek_nim) > 0) {
            sendResponse(false, "nim sudah terdaftar", null, 400);
        }

        // Update mahasiswa
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE mahasiswa
            SET nama = ?, nim = ?, jurusan_id = ?
            WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "ssii", $nama, $nim, $jurusan_id, $id);

        if (mysqli_stmt_execute($stmt)) {
            sendResponse(true, "data diperbarui", [
                "id" => $id
            ], 200);
        } else {
            sendResponse(false, mysqli_error($koneksi), null, 500);
        }

        break;


    // =========================
// DELETE
// =========================
case 'DELETE':

    if (!isset($_GET['id'])) {
        sendResponse(false, "id mahasiswa wajib", null, 400);
    }

    $id = (int) $_GET['id'];

    // Cek mahasiswa
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM mahasiswa WHERE id = ?"
    );

    if (!$stmt) {
        sendResponse(false, "Gagal menyiapkan query: " . mysqli_error($koneksi), null, 500);
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    // Cek hasil tanpa mysqli_stmt_get_result
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 0) {
        sendResponse(false, "mahasiswa tidak ditemukan", null, 404);
    }

    mysqli_stmt_close($stmt);

    // Hapus mahasiswa
    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM mahasiswa WHERE id = ?"
    );

    if (!$stmt) {
        sendResponse(false, "Gagal menyiapkan DELETE: " . mysqli_error($koneksi), null, 500);
    }

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {

        sendResponse(true, "data dihapus", [
            "id" => $id
        ], 200);

    } else {

        sendResponse(false, "Gagal menghapus: " . mysqli_stmt_error($stmt), null, 500);
    }

    mysqli_stmt_close($stmt);

    break;
}