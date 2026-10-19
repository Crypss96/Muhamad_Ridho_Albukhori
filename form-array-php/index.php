<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['students']) || !is_array($_SESSION['students'])) {
    $_SESSION['students'] = [];
}

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/** Escape text before rendering it in HTML. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Read a scalar POST value safely. */
function postString(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

/** Use Post/Redirect/Get to avoid duplicate submissions on page refresh. */
function redirectToForm(): never
{
    header('Location: ./index.php#form', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = postString('csrf_token');

    if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        $_SESSION['flash_errors'] = ['Sesi formulir tidak valid atau sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.'];
        redirectToForm();
    }

    $action = postString('action');

    if ($action === 'add') {
        $input = [
            'nama' => postString('nama'),
            'nim' => postString('nim'),
            'prodi' => postString('prodi'),
            'email' => postString('email'),
        ];

        $errors = [];

        if ($input['nama'] === '') {
            $errors[] = 'Nama lengkap wajib diisi.';
        }
        if ($input['nim'] === '') {
            $errors[] = 'NIM wajib diisi.';
        }
        if ($input['prodi'] === '') {
            $errors[] = 'Program studi wajib diisi.';
        }
        if ($input['email'] === '' || filter_var($input['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Masukkan alamat email yang valid.';
        }

        if ($errors !== []) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['old_input'] = $input;
            redirectToForm();
        }

        $_SESSION['students'][] = [
            'id' => bin2hex(random_bytes(8)),
            'nama' => $input['nama'],
            'nim' => $input['nim'],
            'prodi' => $input['prodi'],
            'email' => $input['email'],
            'created_at' => date('d-m-Y H:i'),
        ];

        $_SESSION['flash_success'] = 'Data mahasiswa berhasil ditambahkan ke tabel.';
        unset($_SESSION['old_input']);
        redirectToForm();
    }

    if ($action === 'clear') {
        $_SESSION['students'] = [];
        $_SESSION['flash_success'] = 'Semua data pada sesi ini berhasil dihapus.';
        unset($_SESSION['old_input']);
        redirectToForm();
    }

    $_SESSION['flash_errors'] = ['Aksi formulir tidak dikenali.'];
    redirectToForm();
}

$errors = $_SESSION['flash_errors'] ?? [];
$success = $_SESSION['flash_success'] ?? '';
$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['flash_errors'], $_SESSION['flash_success'], $_SESSION['old_input']);

if (!is_array($errors)) {
    $errors = [];
}
if (!is_array($oldInput)) {
    $oldInput = [];
}
$students = $_SESSION['students'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Contoh form PHP untuk menyimpan data mahasiswa ke dalam array sesi dan menampilkannya di tabel.">
    <title>Form Data Mahasiswa — PHP Array</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7fb;
            --surface: #ffffff;
            --text: #172033;
            --muted: #687386;
            --line: #e3e8f0;
            --primary: #3157d5;
            --primary-dark: #2443ae;
            --success-bg: #eaf8ef;
            --success-text: #17683a;
            --error-bg: #fff0f0;
            --error-text: #a32323;
            --shadow: 0 18px 50px rgba(26, 43, 79, .08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
        }

        .container {
            width: min(1080px, calc(100% - 32px));
            margin: 42px auto;
        }

        .page-header { margin-bottom: 24px; }
        .eyebrow {
            margin: 0 0 8px;
            color: var(--primary);
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        h1 { margin: 0; font-size: clamp(1.8rem, 4vw, 2.5rem); letter-spacing: -.04em; }
        .subtitle { margin: 10px 0 0; max-width: 680px; color: var(--muted); }

        .card {
            margin-bottom: 22px;
            padding: clamp(18px, 3vw, 28px);
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        .card-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }
        h2 { margin: 0; font-size: 1.2rem; letter-spacing: -.02em; }
        .card-description { margin: 5px 0 0; color: var(--muted); font-size: .92rem; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
        .field { display: flex; flex-direction: column; gap: 7px; }
        .field label { font-size: .9rem; font-weight: 700; }
        .field input {
            width: 100%;
            min-height: 46px;
            padding: 11px 13px;
            border: 1px solid #cfd7e4;
            border-radius: 10px;
            background: #fff;
            color: var(--text);
            font: inherit;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .field input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(49, 87, 213, .13);
        }
        .form-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 16px;
            border: 1px solid transparent;
            border-radius: 10px;
            font: inherit;
            font-weight: 750;
            text-decoration: none;
            cursor: pointer;
        }
        .button-primary { background: var(--primary); color: white; }
        .button-primary:hover { background: var(--primary-dark); }
        .button-secondary { background: white; color: var(--muted); border-color: var(--line); }
        .button-secondary:hover { background: #f8faff; color: var(--text); }
        .button:focus-visible { outline: 3px solid rgba(49, 87, 213, .35); outline-offset: 2px; }

        .notice { margin: 0 0 18px; padding: 12px 15px; border-radius: 10px; font-size: .92rem; }
        .notice-success { background: var(--success-bg); color: var(--success-text); }
        .notice-error { background: var(--error-bg); color: var(--error-text); }
        .notice ul { margin: 0; padding-left: 20px; }

        .table-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            font-size: .86rem;
        }
        .count {
            display: inline-flex;
            min-width: 30px;
            min-height: 30px;
            align-items: center;
            justify-content: center;
            padding: 0 8px;
            border-radius: 9px;
            background: #edf1ff;
            color: var(--primary);
            font-weight: 800;
        }
        .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 12px; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        thead { background: #f7f9fd; }
        th, td { padding: 14px 16px; border-bottom: 1px solid var(--line); vertical-align: top; }
        th { color: #536075; font-size: .78rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; white-space: nowrap; }
        td { font-size: .91rem; }
        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:hover { background: #fafbff; }
        .empty-state { padding: 34px 18px; text-align: center; color: var(--muted); }
        .empty-state strong { display: block; margin-bottom: 5px; color: var(--text); }
        .footnote { margin: 15px 0 0; color: var(--muted); font-size: .84rem; }
        .clear-form { margin: 0; }

        @media (max-width: 640px) {
            .container { width: min(100% - 20px, 1080px); margin: 24px auto; }
            .form-grid { grid-template-columns: 1fr; gap: 14px; }
            .card-heading { align-items: flex-start; flex-direction: column; }
            th, td { padding: 12px; }
            .form-actions .button { width: 100%; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
<main class="container">
    <header class="page-header">
        <p class="eyebrow">PHP Dasar · Array · Form · Tabel</p>
        <h1>Form Data Mahasiswa</h1>
        <p class="subtitle">Masukkan data melalui formulir, lalu data akan ditambahkan ke array PHP dan ditampilkan dalam tabel di bawah.</p>
    </header>

    <section class="card" id="form" aria-labelledby="form-title">
        <div class="card-heading">
            <div>
                <h2 id="form-title">Tambah data mahasiswa</h2>
                <p class="card-description">Semua kolom wajib diisi.</p>
            </div>
        </div>

        <?php if ($success !== ''): ?>
            <div class="notice notice-success" role="status"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <div class="notice notice-error" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="./index.php#form">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="add">

            <div class="form-grid">
                <div class="field">
                    <label for="nama">Nama lengkap</label>
                    <input id="nama" name="nama" type="text" maxlength="120" autocomplete="name" value="<?= e($oldInput['nama'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="nim">NIM</label>
                    <input id="nim" name="nim" type="text" maxlength="40" value="<?= e($oldInput['nim'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="prodi">Program studi</label>
                    <input id="prodi" name="prodi" type="text" maxlength="120" value="<?= e($oldInput['prodi'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" maxlength="190" autocomplete="email" value="<?= e($oldInput['email'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-actions">
                <button class="button button-primary" type="submit">Tambah ke tabel</button>
            </div>
        </form>
    </section>

    <section class="card" aria-labelledby="table-title">
        <div class="card-heading">
            <div>
                <h2 id="table-title">Daftar mahasiswa</h2>
                <p class="card-description">Data pada tabel diambil dari array sesi PHP.</p>
            </div>
            <div class="table-meta">
                <span>Total data</span>
                <span class="count"><?= count($students) ?></span>
            </div>
        </div>

        <?php if ($students === []): ?>
            <div class="empty-state">
                <strong>Belum ada data mahasiswa</strong>
                Isi formulir di atas, lalu tekan “Tambah ke tabel”.
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th scope="col">No.</th>
                        <th scope="col">Nama</th>
                        <th scope="col">NIM</th>
                        <th scope="col">Program studi</th>
                        <th scope="col">Email</th>
                        <th scope="col">Waktu input</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $index => $student): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= e($student['nama'] ?? '') ?></td>
                            <td><?= e($student['nim'] ?? '') ?></td>
                            <td><?= e($student['prodi'] ?? '') ?></td>
                            <td><?= e($student['email'] ?? '') ?></td>
                            <td><?= e($student['created_at'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="footnote">Data hanya disimpan di sesi PHP selama sesi browser masih tersedia. Data tidak disimpan permanen ke database.</p>
            <form class="form-actions clear-form" method="post" action="./index.php#form">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="clear">
                <button class="button button-secondary" type="submit">Kosongkan tabel</button>
            </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
