<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$tab = $_GET['tab'] ?? 'gallery';

// --- gallery edit target ---
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editItem = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id = ?');
    $stmt->execute([$editId]);
    $editItem = $stmt->fetch();
}

// --- poke message edit target ---
$editPokeId = isset($_GET['edit_poke']) ? (int)$_GET['edit_poke'] : null;
$editPoke = null;
if ($editPokeId) {
    $stmt = $pdo->prepare('SELECT * FROM poke_messages WHERE id = ?');
    $stmt->execute([$editPokeId]);
    $editPoke = $stmt->fetch();
}

// --- music track edit target ---
$editMusicId = isset($_GET['edit_music']) ? (int)$_GET['edit_music'] : null;
$editMusic = null;
if ($editMusicId) {
    $stmt = $pdo->prepare('SELECT * FROM music_tracks WHERE id = ?');
    $stmt->execute([$editMusicId]);
    $editMusic = $stmt->fetch();
}

$galleryItems = $pdo->query('SELECT * FROM gallery ORDER BY sort_order ASC, id DESC')->fetchAll();
$featuredCount = $pdo->query('SELECT COUNT(*) c FROM gallery WHERE is_featured = 1')->fetch()['c'];
$pokeItems = $pdo->query('SELECT * FROM poke_messages ORDER BY tag ASC, sort_order ASC, id ASC')->fetchAll();
$musicItems = $pdo->query('SELECT * FROM music_tracks ORDER BY sort_order ASC, id ASC')->fetchAll();
$settings = get_settings($pdo);

$msgMap = [
    'saved'        => ['ok', 'Saved successfully.'],
    'deleted'      => ['ok', 'Item deleted successfully.'],
    'settings'     => ['ok', 'Site settings updated successfully.'],
    'poke_saved'   => ['ok', 'Poke message saved successfully.'],
    'poke_deleted' => ['ok', 'Poke message deleted successfully.'],
    'music_saved'  => ['ok', 'Track saved successfully.'],
    'music_deleted' => ['ok', 'Track deleted successfully.'],
    'error'        => ['err', $_GET['detail'] ?? 'Something went wrong.'],
];
$flash = isset($_GET['msg']) && isset($msgMap[$_GET['msg']]) ? $msgMap[$_GET['msg']] : null;
?>
<!DOCTYPE html>
<html lang="en">
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
    <a href="dashboard.php?tab=gallery" class="<?= $tab==='gallery'?'active':'' ?>">Gallery</a>
    <a href="dashboard.php?tab=poke" class="<?= $tab==='poke'?'active':'' ?>">Poke Messages</a>
    <a href="dashboard.php?tab=music" class="<?= $tab==='music'?'active':'' ?>">Music</a>
    <a href="dashboard.php?tab=settings" class="<?= $tab==='settings'?'active':'' ?>">Site Settings</a>
    <a href="change-password.php">Change Password</a>
    <a href="../index.php" target="_blank">View Site ↗</a>
    <a href="logout.php">Log Out</a>
  </nav>
</div>

<div class="wrap">
  <h1>Hi, <?= e($_SESSION['admin_username']) ?> 👋</h1>
  <p style="color:var(--ink-dim); margin-bottom:24px;">Manage your image gallery and site content from here.</p>

  <?php if ($flash): ?>
    <div class="msg <?= $flash[0] ?>"><?= e($flash[1]) ?></div>
  <?php endif; ?>

  <?php if ($tab === 'gallery'): ?>

    <div class="card">
      <h2><?= $editItem ? 'Edit Image' : 'Add New Image' ?></h2>
      <form method="post" action="gallery-save.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if ($editItem): ?><input type="hidden" name="id" value="<?= (int)$editItem['id'] ?>"><?php endif; ?>

        <div class="grid2">
          <div>
            <label>Character Name / Title</label>
            <input type="text" name="name" required value="<?= e($editItem['name'] ?? '') ?>" placeholder="e.g. Castorice">
          </div>
          <div>
            <label>Tag / Category (lowercase, no spaces)</label>
            <input type="text" name="tag" required value="<?= e($editItem['tag'] ?? '') ?>" placeholder="e.g. castorice">
          </div>
        </div>

        <label>Image <?= $editItem ? '(leave empty to keep the current image)' : '' ?></label>
        <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
        <div class="hint">JPG/PNG/GIF/WEBP format, 5MB max.</div>
        <?php if ($editItem): ?>
          <div style="margin-top:10px;"><img src="../<?= e($editItem['image_path']) ?>" style="width:90px;border-radius:8px;"></div>
        <?php endif; ?>

        <label>Short quote (used in the gacha &amp; gallery)</label>
        <input type="text" name="quote" value="<?= e($editItem['quote'] ?? '') ?>" placeholder="A short line about this image">

        <div class="checkrow">
          <input type="checkbox" id="is_featured" name="is_featured" value="1" <?= !empty($editItem['is_featured']) ? 'checked' : '' ?>>
          <label for="is_featured" style="margin:0;">Show in the "Favorites" section (featured character)</label>
        </div>
        <div class="hint">Currently featured: <?= (int)$featuredCount ?>/10. The first one becomes the big main card, the rest appear as smaller cards next to it.</div>

        <div class="grid2">
          <div>
            <label>Favorite label (for the Favorites section)</label>
            <input type="text" name="featured_subtitle" value="<?= e($editItem['featured_subtitle'] ?? '') ?>" placeholder="e.g. the most beloved">
          </div>
          <div>
            <label>Long description (optional, shown on the main card)</label>
            <textarea name="featured_desc" placeholder="A short story about this character..."><?= e($editItem['featured_desc'] ?? '') ?></textarea>
          </div>
        </div>

        <label>Display order (lower numbers show first)</label>
        <input type="text" name="sort_order" value="<?= e((string)($editItem['sort_order'] ?? 0)) ?>" style="max-width:120px;">

        <div style="margin-top:22px; display:flex; gap:10px;">
          <button class="btn" type="submit"><?= $editItem ? 'Save Changes' : 'Add to Gallery' ?></button>
          <?php if ($editItem): ?><a class="btn secondary" href="dashboard.php?tab=gallery">Cancel</a><?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card">
      <h2>All Images (<?= count($galleryItems) ?>)</h2>
      <div class="table-wrap">
      <table>
        <thead><tr><th>Image</th><th>Name</th><th>Tag</th><th>Featured</th><th>Order</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($galleryItems as $item): ?>
          <tr>
            <td data-label="Image"><img src="../<?= e($item['image_path']) ?>" alt=""></td>
            <td data-label="Name"><?= e($item['name']) ?></td>
            <td data-label="Tag"><span class="tag-pill">#<?= e($item['tag']) ?></span></td>
            <td data-label="Featured"><?= $item['is_featured'] ? '<span class="star">★ yes</span>' : '—' ?></td>
            <td data-label="Order"><?= (int)$item['sort_order'] ?></td>
            <td data-label="Actions" class="actions">
              <a class="btn small secondary" href="dashboard.php?tab=gallery&edit=<?= (int)$item['id'] ?>">Edit</a>
              <form method="post" action="gallery-delete.php" onsubmit="return confirm('Delete this image?');" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                <button class="btn small danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$galleryItems): ?>
          <tr><td colspan="6" style="color:var(--ink-dim);">No images yet. Add one using the form above.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>

  <?php elseif ($tab === 'poke'): ?>

    <div class="card">
      <h2><?= $editPoke ? 'Edit Poke Message' : 'Add New Poke Message' ?></h2>
      <p style="color:var(--ink-dim); font-size:0.85rem; margin-top:-8px;">
        These are the little reaction lines that pop up when a visitor clicks/pokes a character image.
        Use a <b>tag</b> that matches a gallery tag (e.g. <code>castorice</code>) for character-specific lines,
        or <code>default</code> for a generic fallback used by any tag without its own messages.
      </p>
      <form method="post" action="poke-save.php">
        <?= csrf_field() ?>
        <?php if ($editPoke): ?><input type="hidden" name="id" value="<?= (int)$editPoke['id'] ?>"><?php endif; ?>

        <div class="grid2">
          <div>
            <label>Tag</label>
            <input type="text" name="tag" required value="<?= e($editPoke['tag'] ?? '') ?>" placeholder="e.g. castorice, odette, or default">
          </div>
          <div>
            <label>Order</label>
            <input type="text" name="sort_order" value="<?= e((string)($editPoke['sort_order'] ?? 0)) ?>">
          </div>
        </div>

        <label>Message</label>
        <input type="text" name="message" required value="<?= e($editPoke['message'] ?? '') ?>" placeholder="e.g. hm? ...what is it?" maxlength="255">

        <div style="margin-top:22px; display:flex; gap:10px;">
          <button class="btn" type="submit"><?= $editPoke ? 'Save Changes' : 'Add Message' ?></button>
          <?php if ($editPoke): ?><a class="btn secondary" href="dashboard.php?tab=poke">Cancel</a><?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card">
      <h2>All Poke Messages (<?= count($pokeItems) ?>)</h2>
      <div class="table-wrap">
      <table>
        <thead><tr><th>Tag</th><th>Message</th><th>Order</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($pokeItems as $p): ?>
          <tr>
            <td data-label="Tag"><span class="tag-pill">#<?= e($p['tag']) ?></span></td>
            <td data-label="Message">"<?= e($p['message']) ?>"</td>
            <td data-label="Order"><?= (int)$p['sort_order'] ?></td>
            <td data-label="Actions" class="actions">
              <a class="btn small secondary" href="dashboard.php?tab=poke&edit_poke=<?= (int)$p['id'] ?>">Edit</a>
              <form method="post" action="poke-delete.php" onsubmit="return confirm('Delete this message?');" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button class="btn small danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$pokeItems): ?>
          <tr><td colspan="4" style="color:var(--ink-dim);">No poke messages yet. Add one using the form above.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>

  <?php elseif ($tab === 'music'): ?>

    <div class="card">
      <h2><?= $editMusic ? 'Edit Track' : 'Add New Track' ?></h2>
      <p style="color:var(--ink-dim); font-size:0.85rem; margin-top:-8px;">
        Manage the background music playlist that plays on the main site. Tracks play in order
        and loop back to the first one when the playlist ends.
      </p>
      <form method="post" action="music-save.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if ($editMusic): ?><input type="hidden" name="id" value="<?= (int)$editMusic['id'] ?>"><?php endif; ?>

        <div class="grid2">
          <div>
            <label>Track Title</label>
            <input type="text" name="title" required value="<?= e($editMusic['title'] ?? '') ?>" placeholder="e.g. Night Drift">
          </div>
          <div>
            <label>Artist (optional)</label>
            <input type="text" name="artist" value="<?= e($editMusic['artist'] ?? '') ?>" placeholder="e.g. Unknown">
          </div>
        </div>

        <label>Audio File <?= $editMusic ? '(leave empty to keep the current file)' : '' ?></label>
        <input type="file" name="audio" accept="audio/mpeg,audio/mp4,.mp3,.m4a,.wav,.ogg,.aac">
        <div class="hint">MP3/M4A/WAV/OGG/AAC format, 15MB max.</div>
        <?php if ($editMusic): ?>
          <div style="margin-top:10px;"><audio controls src="../<?= e($editMusic['file_path']) ?>" style="width:100%; max-width:320px; height:36px;"></audio></div>
        <?php endif; ?>

        <label>Play order (lower numbers play first)</label>
        <input type="text" name="sort_order" value="<?= e((string)($editMusic['sort_order'] ?? 0)) ?>" style="max-width:120px;">

        <div style="margin-top:22px; display:flex; gap:10px;">
          <button class="btn" type="submit"><?= $editMusic ? 'Save Changes' : 'Add Track' ?></button>
          <?php if ($editMusic): ?><a class="btn secondary" href="dashboard.php?tab=music">Cancel</a><?php endif; ?>
        </div>
      </form>
    </div>

    <div class="card">
      <h2>All Tracks (<?= count($musicItems) ?>)</h2>
      <div class="table-wrap">
      <table>
        <thead><tr><th>Preview</th><th>Title</th><th>Artist</th><th>Order</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($musicItems as $m): ?>
          <tr>
            <td data-label="Preview"><audio controls src="../<?= e($m['file_path']) ?>" style="width:180px; height:32px;"></audio></td>
            <td data-label="Title"><?= e($m['title']) ?></td>
            <td data-label="Artist"><?= e($m['artist'] ?: '—') ?></td>
            <td data-label="Order"><?= (int)$m['sort_order'] ?></td>
            <td data-label="Actions" class="actions">
              <a class="btn small secondary" href="dashboard.php?tab=music&edit_music=<?= (int)$m['id'] ?>">Edit</a>
              <form method="post" action="music-delete.php" onsubmit="return confirm('Delete this track?');" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <button class="btn small danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$musicItems): ?>
          <tr><td colspan="5" style="color:var(--ink-dim);">No tracks yet. Add one using the form above. (The music player is hidden on the main site when the playlist is empty.)</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>

  <?php else: ?>

    <form method="post" action="settings-save.php" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <div class="card">
        <h2>Hero (top section)</h2>
        <label>Small text above the title</label>
        <input type="text" name="hero_eyebrow" value="<?= e($settings['hero_eyebrow']) ?>">
        <label>Tagline (italic quote)</label>
        <input type="text" name="hero_tagline" value="<?= e($settings['hero_tagline']) ?>">
        <label>Short description paragraph</label>
        <textarea name="hero_sub"><?= e($settings['hero_sub']) ?></textarea>
        <label>Hero background image</label>
        <input type="file" name="hero_bg_image" accept="image/*">
        <div class="hint">Current: <?= e($settings['hero_bg_image']) ?> — leave empty to keep it.</div>
      </div>

      <div class="card">
        <h2>About Me</h2>
        <label>Greeting line</label>
        <input type="text" name="about_greeting" value="<?= e($settings['about_greeting']) ?>">
        <label>Paragraph content (separate paragraphs with a blank line)</label>
        <textarea name="about_text" style="min-height:140px;"><?= e($settings['about_text']) ?></textarea>
        <label>Profile photo / portrait</label>
        <input type="file" name="about_image" accept="image/*">
        <div class="hint">Current: <?= e($settings['about_image']) ?> — leave empty to keep it.</div>
      </div>

      <div class="card">
        <h2>Social Links</h2>
        <p style="color:var(--ink-dim); font-size:0.85rem; margin-top:-8px;">
          The "Handle" field is the short text shown on the card (e.g. your @username). Leave it
          empty to just show "open link" instead.
        </p>
        <div class="grid2">
          <div><label>Instagram URL</label><input type="text" name="social_instagram" value="<?= e($settings['social_instagram']) ?>"></div>
          <div><label>Instagram Handle</label><input type="text" name="social_instagram_handle" value="<?= e($settings['social_instagram_handle']) ?>" placeholder="@yourhandle"></div>

          <div><label>GitHub URL</label><input type="text" name="social_github" value="<?= e($settings['social_github']) ?>"></div>
          <div><label>GitHub Handle</label><input type="text" name="social_github_handle" value="<?= e($settings['social_github_handle']) ?>" placeholder="yourusername"></div>

          <div><label>MyAnimeList URL</label><input type="text" name="social_mal" value="<?= e($settings['social_mal']) ?>"></div>
          <div><label>MyAnimeList Handle</label><input type="text" name="social_mal_handle" value="<?= e($settings['social_mal_handle']) ?>" placeholder="yourusername"></div>

          <div><label>Spotify URL (leave empty if none yet)</label><input type="text" name="social_spotify" value="<?= e($settings['social_spotify']) ?>"></div>
          <div><label>Spotify Handle</label><input type="text" name="social_spotify_handle" value="<?= e($settings['social_spotify_handle']) ?>" placeholder="yourusername"></div>

          <div><label>Steam URL (leave empty if none yet)</label><input type="text" name="social_steam" value="<?= e($settings['social_steam']) ?>"></div>
          <div><label>Steam Handle</label><input type="text" name="social_steam_handle" value="<?= e($settings['social_steam_handle']) ?>" placeholder="yourusername"></div>

          <div><label>X / Twitter URL (leave empty if none yet)</label><input type="text" name="social_x" value="<?= e($settings['social_x']) ?>"></div>
          <div><label>X / Twitter Handle</label><input type="text" name="social_x_handle" value="<?= e($settings['social_x_handle']) ?>" placeholder="@yourhandle"></div>
        </div>
      </div>

      <div class="card">
        <h2>Footer</h2>
        <label>Footer quote</label>
        <input type="text" name="footer_quote" value="<?= e($settings['footer_quote']) ?>">
        <label>Credit text</label>
        <input type="text" name="footer_credit" value="<?= e($settings['footer_credit']) ?>">
      </div>

      <button class="btn" type="submit">Save Settings</button>
    </form>

  <?php endif; ?>
</div>

</body>
</html>