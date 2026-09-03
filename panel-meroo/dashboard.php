<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$tab = $_GET['tab'] ?? 'gallery';
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editItem = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id = ?');
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
}

$galleryItems = $pdo->query('SELECT * FROM gallery ORDER BY sort_order ASC, id DESC')->fetchAll();
$settings = get_settings($pdo);

$msgMap = [
    'saved'    => ['ok', 'Berhasil disimpan.'],
    'deleted'  => ['ok', 'Item berhasil dihapus.'],
    'settings' => ['ok', 'Pengaturan situs berhasil diperbarui.'],
    'error'    => ['err', $_GET['detail'] ?? 'Terjadi kesalahan.'],
];
$flash = isset($_GET['msg']) && isset($msgMap[$_GET['msg']]) ? $msgMap[$_GET['msg']] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Admin</title>
<meta name="robots" content="noindex, nofollow">
<?php require __DIR__ . '/includes/admin-style.php'; ?>
</head>
<body>

<div class="topbar">
  <div class="brand">mer<span>oo__</span> · admin</div>
  <nav>
    <a href="dashboard.php?tab=gallery" class="<?= $tab==='gallery'?'active':'' ?>">Galeri</a>
    <a href="dashboard.php?tab=settings" class="<?= $tab==='settings'?'active':'' ?>">Pengaturan Situs</a>
    <a href="change-password.php">Ganti Password</a>
    <a href="../index.php" target="_blank">Lihat Situs ↗</a>
    <a href="logout.php">Keluar</a>
  </nav>
</div>

<div class="wrap">
  <h1>Halo, <?= e($_SESSION['admin_username']) ?> 👋</h1>
  <p style="color:var(--ink-dim); margin-bottom:24px;">Kelola galeri gambar dan konten situs dari sini.</p>

  <?php if ($flash): ?>
    <div class="msg <?= $flash[0] ?>"><?= e($flash[1]) ?></div>
  <?php endif; ?>

  <?php if ($tab === 'gallery'): ?>

    <div class="card">
      <h2><?= $editItem ? 'Edit Gambar' : 'Tambah Gambar Baru' ?></h2>
      <form method="post" action="gallery-save.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if ($editItem): ?><input type="hidden" name="id" value="<?= (int)$editItem['id'] ?>"><?php endif; ?>

        <div class="grid2">
          <div>
            <label>Nama Karakter / Judul</label>
            <input type="text" name="name" required value="<?= e($editItem['name'] ?? '') ?>" placeholder="Misal: Castorice">
          </div>
          <div>
            <label>Tag / Kategori (huruf kecil, tanpa spasi)</label>
            <input type="text" name="tag" required value="<?= e($editItem['tag'] ?? '') ?>" placeholder="Misal: castorice">
          </div>
        </div>

        <label>Gambar <?= $editItem ? '(kosongkan jika tidak ingin ganti gambar)' : '' ?></label>
        <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
        <div class="hint">Format JPG/PNG/GIF/WEBP, maksimal 5MB.</div>
        <?php if ($editItem): ?>
          <div style="margin-top:10px;"><img src="../<?= e($editItem['image_path']) ?>" style="width:90px;border-radius:8px;"></div>
        <?php endif; ?>

        <label>Quote singkat (dipakai di gacha &amp; galeri)</label>
        <input type="text" name="quote" value="<?= e($editItem['quote'] ?? '') ?>" placeholder="Kalimat pendek tentang gambar ini">

        <div class="checkrow">
          <input type="checkbox" id="is_featured" name="is_featured" value="1" <?= !empty($editItem['is_featured']) ? 'checked' : '' ?>>
          <label for="is_featured" style="margin:0;">Tampilkan di section "Tercinta" (karakter favorit)</label>
        </div>

        <div class="grid2">
          <div>
            <label>Label favorit (untuk section Tercinta)</label>
            <input type="text" name="featured_subtitle" value="<?= e($editItem['featured_subtitle'] ?? '') ?>" placeholder="Misal: yang paling tercinta">
          </div>
          <div>
            <label>Deskripsi panjang (opsional, untuk kartu utama)</label>
            <textarea name="featured_desc" placeholder="Cerita singkat tentang karakter ini..."><?= e($editItem['featured_desc'] ?? '') ?></textarea>
          </div>
        </div>

        <label>Urutan tampil (angka kecil tampil duluan)</label>
        <input type="text" name="sort_order" value="<?= e((string)($editItem['sort_order'] ?? 0)) ?>" style="max-width:120px;">

        <div style="margin-top:22px; display:flex; gap:10px;">
          <button class="btn" type="submit"><?= $editItem ? 'Simpan Perubahan' : 'Tambah ke Galeri' ?></button>
          <?php if ($editItem): ?><a class="btn secondary" href="dashboard.php?tab=gallery">Batal</a><?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card">
      <h2>Semua Gambar (<?= count($galleryItems) ?>)</h2>
      <div class="table-wrap">
      <table>
        <thead><tr><th>Gambar</th><th>Nama</th><th>Tag</th><th>Favorit</th><th>Urutan</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($galleryItems as $item): ?>
          <tr>
            <td data-label="Gambar"><img src="../<?= e($item['image_path']) ?>" alt=""></td>
            <td data-label="Nama"><?= e($item['name']) ?></td>
            <td data-label="Tag"><span class="tag-pill">#<?= e($item['tag']) ?></span></td>
            <td data-label="Favorit"><?= $item['is_featured'] ? '<span class="star">★ ya</span>' : '—' ?></td>
            <td data-label="Urutan"><?= (int)$item['sort_order'] ?></td>
            <td data-label="Aksi" class="actions">
              <a class="btn small secondary" href="dashboard.php?tab=gallery&edit=<?= (int)$item['id'] ?>">Edit</a>
              <form method="post" action="gallery-delete.php" onsubmit="return confirm('Yakin hapus gambar ini?');" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                <button class="btn small danger" type="submit">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$galleryItems): ?>
          <tr><td colspan="6" style="color:var(--ink-dim);">Belum ada gambar. Tambahkan lewat form di atas.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>

  <?php else: ?>

    <form method="post" action="settings-save.php" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <div class="card">
        <h2>Hero (bagian paling atas)</h2>
        <label>Teks kecil di atas judul</label>
        <input type="text" name="hero_eyebrow" value="<?= e($settings['hero_eyebrow']) ?>">
        <label>Tagline (kutipan miring)</label>
        <input type="text" name="hero_tagline" value="<?= e($settings['hero_tagline']) ?>">
        <label>Paragraf deskripsi singkat</label>
        <textarea name="hero_sub"><?= e($settings['hero_sub']) ?></textarea>
        <label>Gambar latar hero</label>
        <input type="file" name="hero_bg_image" accept="image/*">
        <div class="hint">Saat ini: <?= e($settings['hero_bg_image']) ?> — kosongkan jika tidak ingin ganti.</div>
      </div>

      <div class="card">
        <h2>Tentang Saya</h2>
        <label>Kalimat sapaan</label>
        <input type="text" name="about_greeting" value="<?= e($settings['about_greeting']) ?>">
        <label>Isi paragraf (pisahkan antar paragraf dengan baris kosong)</label>
        <textarea name="about_text" style="min-height:140px;"><?= e($settings['about_text']) ?></textarea>
        <label>Foto profil / portrait</label>
        <input type="file" name="about_image" accept="image/*">
        <div class="hint">Saat ini: <?= e($settings['about_image']) ?> — kosongkan jika tidak ingin ganti.</div>
      </div>

      <div class="card">
        <h2>Link Sosial Media</h2>
        <div class="grid2">
          <div><label>Instagram</label><input type="text" name="social_instagram" value="<?= e($settings['social_instagram']) ?>"></div>
          <div><label>GitHub</label><input type="text" name="social_github" value="<?= e($settings['social_github']) ?>"></div>
          <div><label>MyAnimeList</label><input type="text" name="social_mal" value="<?= e($settings['social_mal']) ?>"></div>
          <div><label>Spotify (kosongkan jika belum ada)</label><input type="text" name="social_spotify" value="<?= e($settings['social_spotify']) ?>"></div>
          <div><label>Steam (kosongkan jika belum ada)</label><input type="text" name="social_steam" value="<?= e($settings['social_steam']) ?>"></div>
          <div><label>X / Twitter (kosongkan jika belum ada)</label><input type="text" name="social_x" value="<?= e($settings['social_x']) ?>"></div>
        </div>
      </div>

      <div class="card">
        <h2>Footer</h2>
        <label>Kutipan footer</label>
        <input type="text" name="footer_quote" value="<?= e($settings['footer_quote']) ?>">
        <label>Teks kredit</label>
        <input type="text" name="footer_credit" value="<?= e($settings['footer_credit']) ?>">
      </div>

      <button class="btn" type="submit">Simpan Pengaturan</button>
    </form>

  <?php endif; ?>
</div>

</body>
</html>