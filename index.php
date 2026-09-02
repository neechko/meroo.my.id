<?php
require_once __DIR__ . '/db.php';

$settings = get_settings($pdo);

// gambar galeri biasa (dipakai grid, tab filter, gacha, memory game)
$galleryRows = $pdo->query('SELECT * FROM gallery WHERE is_featured = 0 ORDER BY sort_order ASC, id ASC')->fetchAll();

// gambar karakter favorit (section "Tercinta")
$featuredRows = $pdo->query('SELECT * FROM gallery WHERE is_featured = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
$mainFeatured = $featuredRows[0] ?? null;
$subFeatured  = array_slice($featuredRows, 1, 2);

// daftar tag unik untuk tombol filter galeri
$tagRows = $pdo->query('SELECT DISTINCT tag FROM gallery WHERE is_featured = 0 ORDER BY tag ASC')->fetchAll();

// data untuk JS (gacha & memory game), path gambar dibuat relatif dari root situs
$galleryDataJs = array_map(function ($r) {
    return [
        'src'   => $r['image_path'],
        'tag'   => $r['tag'],
        'name'  => $r['name'],
        'quote' => $r['quote'] ?: ($r['name'] . ' — koleksi galeri'),
    ];
}, $galleryRows);

function about_paragraphs(string $text): string {
    $out = '';
    foreach (preg_split('/\n\s*\n/', trim($text)) as $p) {
        $p = trim($p);
        if ($p !== '') $out .= '<p>' . nl2br(htmlspecialchars($p, ENT_QUOTES, 'UTF-8')) . '</p>' . "\n";
    }
    return $out;
}

//untuk menampilkan @username dari link sosial media, misal https://twitter.com/username -> username
function extractSocialUsername($url) {
    $path = parse_url($url, PHP_URL_PATH);
    if (!$path) return '';
    // Bersihkan slash di awal/akhir
    $path = trim($path, '/');
    // Ambil segmen terakhir path (biasanya username)
    $segments = explode('/', $path);
    $username = end($segments);
    // Buang query string kalau ada yg nyangkut, dan decode
    $username = urldecode($username);
    return $username;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>meroo — elegi kecil</title>
<link rel="icon" type="image/png" href="icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --void:#0b0713;
    --void-2:#120a1c;
    --plum:#1c1229;
    --plum-2:#251733;
    --lavender:#c9b6e4;
    --orchid:#a879c9;
    --moth-pink:#e8b4d9;
    --gold:#d4b483;
    --ink:#f2ecf7;
    --ink-dim:#a99cc0;
    --line:rgba(201,182,228,0.16);
    --shadow:0 20px 60px rgba(0,0,0,0.5);
  }

  *{margin:0;padding:0;box-sizing:border-box;}

  html{scroll-behavior:smooth;}

  @media (prefers-reduced-motion: reduce){
    html{scroll-behavior:auto;}
    *{animation-duration:0.001ms !important; animation-iteration-count:1 !important; transition-duration:0.001ms !important;}
  }

  body{
    background:var(--void);
    color:var(--ink);
    font-family:'Poppins', sans-serif;
    font-weight:300;
    line-height:1.7;
    overflow-x:hidden;
  }

  ::selection{ background:var(--orchid); color:var(--void); }

  a{ color:inherit; text-decoration:none; }

  img{ max-width:100%; display:block; }

  .display{
    font-family:'Cormorant Garamond', serif;
  }

  .mono{
    font-family:'JetBrains Mono', monospace;
  }

  /* ---------- scroll progress ---------- */
  .progress-bar{
    position:fixed; top:0; left:0; height:2px; width:0%; z-index:100;
    background:linear-gradient(90deg, var(--orchid), var(--moth-pink), var(--gold));
    box-shadow:0 0 8px rgba(232,180,217,0.6);
    transition:width 0.08s linear;
  }

  /* ---------- cursor glow ---------- */
  .cursor-glow{
    position:fixed; top:0; left:0; width:280px; height:280px; z-index:1;
    pointer-events:none; border-radius:50%;
    background:radial-gradient(circle, rgba(168,121,201,0.10), transparent 70%);
    transform:translate(-50%,-50%);
    opacity:0; transition:opacity 0.4s ease;
    will-change:transform;
  }
  @media (hover:none), (max-width:760px){ .cursor-glow{ display:none; } }

  /* ---------- background atmosphere ---------- */
  .bg-glow{
    position:fixed; inset:0; z-index:0; pointer-events:none;
    background:
      radial-gradient(ellipse 60% 40% at 15% 8%, rgba(168,121,201,0.16), transparent 60%),
      radial-gradient(ellipse 50% 50% at 90% 70%, rgba(232,180,217,0.08), transparent 60%);
  }

  .moth{
    position:fixed; z-index:1; pointer-events:none; opacity:0.55;
    filter: drop-shadow(0 0 6px rgba(232,180,217,0.5));
    animation: drift 22s ease-in-out infinite;
  }
  .moth svg{ width:100%; height:100%; }
  .moth.m1{ top:12%; left:6%; width:26px; height:26px; animation-duration:26s; }
  .moth.m2{ top:60%; left:88%; width:18px; height:18px; animation-duration:19s; animation-delay:-4s; }
  .moth.m3{ top:35%; left:80%; width:22px; height:22px; animation-duration:24s; animation-delay:-9s; }
  .moth.m4{ top:80%; left:20%; width:16px; height:16px; animation-duration:17s; animation-delay:-2s; }
  .moth.m5{ top:20%; left:50%; width:14px; height:14px; animation-duration:21s; animation-delay:-13s; }

  @keyframes drift{
    0%,100%{ transform: translate(0,0) rotate(0deg); }
    25%{ transform: translate(18px,-24px) rotate(8deg); }
    50%{ transform: translate(-10px,10px) rotate(-6deg); }
    75%{ transform: translate(14px,18px) rotate(5deg); }
  }

  /* ---------- nav ---------- */
  nav{
    position:fixed; top:0; left:0; right:0; z-index:50;
    display:flex; align-items:center; justify-content:space-between;
    padding:22px 6vw;
    backdrop-filter: blur(10px);
    background:linear-gradient(to bottom, rgba(11,7,19,0.75), transparent);
  }
  .brand{
    font-family:'Cormorant Garamond', serif;
    font-style:italic;
    font-size:1.4rem;
    letter-spacing:0.02em;
    color:var(--ink);
  }
  .brand span{ color:var(--moth-pink); }
  .nav-links{
    display:flex; gap:2.2rem;
    font-size:0.78rem;
    letter-spacing:0.12em;
    text-transform:uppercase;
    font-family:'JetBrains Mono', monospace;
    color:var(--ink-dim);
  }
  .nav-links a{ position:relative; transition:color 0.3s; padding:4px 0;}
  .nav-links a:hover, .nav-links a:focus-visible{ color:var(--moth-pink); }
  .nav-links a::after{
    content:''; position:absolute; left:0; bottom:0; width:0; height:1px;
    background:var(--moth-pink); transition:width 0.3s;
  }
  .nav-links a:hover::after, .nav-links a:focus-visible::after{ width:100%; }
  .nav-links a.active{ color:var(--moth-pink); }
  .nav-links a.active::after{ width:100%; }
  .nav-toggle{ display:none; }

  @media (max-width:760px){
    .nav-links{
      position:fixed; top:0; right:0; height:100vh; width:min(72vw,300px);
      background:var(--void-2); flex-direction:column; justify-content:center; gap:2rem;
      padding:0 2.4rem; transform:translateX(100%); transition:transform 0.4s ease;
      border-left:1px solid var(--line);
    }
    .nav-links.open{ transform:translateX(0); }
    .nav-toggle{
      display:block; z-index:60; background:none; border:none; color:var(--ink);
      font-size:1.4rem; cursor:pointer; font-family:'JetBrains Mono',monospace;
    }
  }

  a:focus-visible, button:focus-visible{
    outline:1.5px solid var(--moth-pink);
    outline-offset:4px;
  }

  section{ position:relative; z-index:2; }

  /* ---------- hero ---------- */
  .hero{
    min-height:100vh;
    display:flex; align-items:center;
    padding:0 6vw;
    position:relative;
    overflow:hidden;
  }
  .hero-bg{
    position:absolute; inset:0; z-index:0;
  }
  .hero-bg img{
    width:100%; height:100%; object-fit:cover; object-position:75% 20%;
    opacity:0.55;
  }
  .hero-bg::before{
    content:''; position:absolute; inset:0;
    background:linear-gradient(100deg, var(--void) 15%, rgba(11,7,19,0.75) 45%, rgba(11,7,19,0.25) 75%, rgba(11,7,19,0.55) 100%);
  }
  .hero-bg::after{
    content:''; position:absolute; inset:0;
    background:linear-gradient(to top, var(--void) 0%, transparent 30%);
  }

  .moon{
    position:absolute; top:8%; right:9%; width:80px; height:80px; z-index:1;
    opacity:0.9; animation:pulse-moon 6s ease-in-out infinite;
  }
  @keyframes pulse-moon{
    0%,100%{ filter:drop-shadow(0 0 14px rgba(212,180,131,0.5)); }
    50%{ filter:drop-shadow(0 0 26px rgba(212,180,131,0.85)); }
  }

  .hero-content{ position:relative; z-index:2; max-width:640px; }
  .eyebrow{
    font-family:'JetBrains Mono', monospace;
    font-size:0.8rem; letter-spacing:0.18em; text-transform:uppercase;
    color:var(--moth-pink); margin-bottom:1.4rem; display:flex; align-items:center; gap:10px;
  }
  .eyebrow::before{ content:''; width:26px; height:1px; background:var(--moth-pink); display:inline-block; }

  .hero h1{
    font-size:clamp(3.2rem, 9vw, 6.4rem);
    font-weight:500;
    line-height:0.95;
    letter-spacing:-0.01em;
    color:var(--ink);
  }
  .hero h1 em{
    font-style:italic; color:var(--moth-pink); font-weight:400;
  }
  .hero-tagline{
    font-family:'Cormorant Garamond', serif;
    font-style:italic;
    font-size:clamp(1.2rem, 2.4vw, 1.6rem);
    color:var(--lavender);
    margin-top:1.4rem;
    max-width:480px;
  }
  .hero-sub{
    margin-top:1.8rem; font-size:0.95rem; color:var(--ink-dim); max-width:460px;
  }

  .scroll-cue{
    position:absolute; bottom:40px; left:6vw; z-index:2;
    font-family:'JetBrains Mono', monospace; font-size:0.72rem;
    color:var(--ink-dim); letter-spacing:0.1em; text-transform:uppercase;
    display:flex; align-items:center; gap:10px;
  }
  .scroll-cue .line{
    width:1px; height:34px; background:linear-gradient(to bottom, var(--moth-pink), transparent);
    animation:scrollline 2.4s ease-in-out infinite;
  }
  @keyframes scrollline{ 0%,100%{ opacity:0.3; } 50%{ opacity:1; } }

  /* ---------- vine divider ---------- */
  .divider{
    display:flex; align-items:center; justify-content:center;
    padding:2.5rem 6vw; opacity:0.75;
  }
  .divider svg{ width:min(520px, 80vw); height:auto; color:var(--orchid); }

  /* ---------- section shell ---------- */
  .section-inner{ max-width:1180px; margin:0 auto; padding:5rem 6vw; }
  .section-head{ margin-bottom:3rem; max-width:640px; }
  .section-head .eyebrow{ color:var(--gold); }
  .section-head .eyebrow::before{ background:var(--gold); }
  .section-head h2{
    font-size:clamp(2.2rem, 4.2vw, 3.2rem);
    font-weight:500;
    color:var(--ink);
  }
  .section-head h2 em{ font-style:italic; color:var(--lavender); font-weight:400;}
  .section-head p{ margin-top:1rem; color:var(--ink-dim); font-size:0.98rem; max-width:520px; }

  /* ---------- about ---------- */
  .about-grid{
    display:grid; grid-template-columns:280px 1fr; gap:4rem; align-items:center;
  }
  .about-portrait{
    position:relative; border-radius:4px; overflow:hidden;
    box-shadow:var(--shadow);
    border:1px solid var(--line);
  }
  .about-portrait::after{
    content:''; position:absolute; inset:0;
    background:linear-gradient(160deg, rgba(168,121,201,0.15), transparent 60%);
  }
  .about-portrait img{ filter:saturate(1.05); }

  .about-text p{ color:var(--ink-dim); margin-bottom:1.1rem; font-size:1rem; }
  .about-text strong{ color:var(--lavender); font-weight:500; }
  .about-text .display{ color:var(--ink); font-size:1.3rem; font-style:italic; margin-bottom:1.4rem; }

  .tag-row{ display:flex; flex-wrap:wrap; gap:0.6rem; margin-top:1.6rem; }
  .tag{
    font-family:'JetBrains Mono', monospace; font-size:0.76rem;
    padding:0.4rem 0.9rem; border:1px solid var(--line); border-radius:100px;
    color:var(--lavender); background:rgba(168,121,201,0.06);
  }

  @media (max-width:760px){
    .about-grid{ grid-template-columns:1fr; }
    .about-portrait{ max-width:220px; margin:0 auto; }
  }

  /* ---------- interests ---------- */
  .interest-grid{
    display:grid; grid-template-columns:repeat(3,1fr); gap:1.6rem;
  }
  .interest-card{
    background:linear-gradient(160deg, var(--plum), var(--plum-2));
    border:1px solid var(--line); border-radius:6px;
    padding:2.2rem 1.8rem; position:relative; overflow:hidden;
    transition:transform 0.35s ease, border-color 0.35s ease;
  }
  .interest-card:hover{ transform:translateY(-6px); border-color:rgba(232,180,217,0.4); }
  .interest-card .num{
    font-family:'JetBrains Mono', monospace; font-size:0.75rem; color:var(--ink-dim);
    letter-spacing:0.1em;
  }
  .interest-card h3{
    font-family:'Cormorant Garamond', serif; font-style:italic; font-weight:500;
    font-size:1.7rem; margin:1rem 0 0.8rem; color:var(--ink);
  }
  .interest-card p{ font-size:0.9rem; color:var(--ink-dim); }
  .interest-card .icon{
    width:34px; height:34px; margin-bottom:0.6rem; color:var(--moth-pink); opacity:0.9;
  }

  @media (max-width:860px){ .interest-grid{ grid-template-columns:1fr; } }

  /* ---------- characters ---------- */
  .char-grid{
    display:grid; grid-template-columns:1.2fr 1fr; gap:1.6rem;
  }
  .char-card{
    position:relative; border-radius:8px; overflow:hidden; border:1px solid var(--line);
    box-shadow:var(--shadow);
    min-height:420px;
    display:flex; align-items:flex-end;
  }
  .char-card img{
    position:absolute; inset:0; width:100%; height:100%; object-fit:cover;
    transition:transform 0.6s ease;
  }
  .char-card:hover img{ transform:scale(1.04); }
  .char-card::before{
    content:''; position:absolute; inset:0; z-index:1;
    background:linear-gradient(to top, rgba(11,7,19,0.95) 5%, rgba(11,7,19,0.15) 55%, transparent 80%);
  }
  .char-info{ position:relative; z-index:2; padding:1.8rem; }
  .char-info .fav{
    font-family:'JetBrains Mono', monospace; font-size:0.72rem; color:var(--gold);
    letter-spacing:0.12em; text-transform:uppercase; margin-bottom:0.5rem; display:block;
  }
  .char-info h3{
    font-family:'Cormorant Garamond', serif; font-style:italic;
    font-size:2.2rem; color:var(--ink);
  }
  .char-info p{ font-size:0.88rem; color:var(--ink-dim); margin-top:0.4rem; max-width:360px; }

  .char-sub-grid{ display:grid; grid-template-rows:1fr 1fr; gap:1.6rem; }
  .char-sub-grid .char-card{ min-height:200px; }

  @media (max-width:860px){
    .char-grid{ grid-template-columns:1fr; }
    .char-card{ min-height:340px; }
  }

  /* ---------- gallery ---------- */
  .gallery-tabs{
    display:flex; gap:0.6rem; margin-bottom:2rem; flex-wrap:wrap;
  }
  .gtab{
    font-family:'JetBrains Mono', monospace; font-size:0.76rem; letter-spacing:0.08em;
    text-transform:uppercase; color:var(--ink-dim); background:rgba(168,121,201,0.06);
    border:1px solid var(--line); border-radius:100px; padding:0.55rem 1.2rem;
    cursor:pointer; transition:color 0.3s, border-color 0.3s, background 0.3s;
  }
  .gtab:hover{ color:var(--ink); border-color:rgba(232,180,217,0.35); }
  .gtab.active{ color:var(--void); background:var(--moth-pink); border-color:var(--moth-pink); }

  .gallery-grid{
    display:grid; grid-template-columns:repeat(auto-fill, minmax(150px,1fr));
    gap:0.9rem; margin-bottom:4rem;
  }
  .gallery-item{
    position:relative; border-radius:6px; overflow:hidden; cursor:pointer;
    border:1px solid var(--line); aspect-ratio:1/1; opacity:0; transform:scale(0.94);
    animation:gpop 0.5s ease forwards;
  }
  @keyframes gpop{ to{ opacity:1; transform:scale(1); } }
  .gallery-item img{ width:100%; height:100%; object-fit:cover; transition:transform 0.5s ease; }
  .gallery-item:hover img{ transform:scale(1.08); }
  .gallery-item::after{
    content:''; position:absolute; inset:0;
    background:linear-gradient(to top, rgba(11,7,19,0.75), transparent 45%);
    opacity:0; transition:opacity 0.3s;
  }
  .gallery-item:hover::after{ opacity:1; }
  .gallery-item .tag-mini{
    position:absolute; left:0.6rem; bottom:0.5rem; z-index:2;
    font-family:'JetBrains Mono', monospace; font-size:0.62rem; letter-spacing:0.06em;
    text-transform:uppercase; color:var(--ink); opacity:0; transition:opacity 0.3s;
  }
  .gallery-item:hover .tag-mini{ opacity:1; }
  .gallery-item.hidden{ display:none; }

  /* ---------- gacha minigame ---------- */
  .gacha-box{
    border:1px solid var(--line); border-radius:12px;
    background:linear-gradient(160deg, var(--plum), var(--plum-2));
    padding:3rem 2rem; text-align:center; position:relative; overflow:hidden;
  }
  .gacha-box::before{
    content:''; position:absolute; inset:0; z-index:0;
    background:radial-gradient(ellipse 60% 50% at 50% 0%, rgba(232,180,217,0.12), transparent 70%);
  }
  .gacha-head, .gacha-stage, .gacha-btn, .gacha-stats{ position:relative; z-index:1; }
  .gacha-head h3{
    font-family:'Cormorant Garamond', serif; font-style:italic; font-weight:500;
    font-size:2rem; color:var(--ink); margin:0.4rem 0 0.6rem;
  }
  .gacha-head p{ color:var(--ink-dim); font-size:0.92rem; max-width:440px; margin:0 auto; }

  .gacha-stage{
    width:200px; height:200px; margin:2.2rem auto 1.6rem; border-radius:12px;
    border:1px solid var(--line); background:var(--void-2);
    display:flex; align-items:center; justify-content:center; overflow:hidden;
    box-shadow:var(--shadow); position:relative;
  }
  .gacha-placeholder{
    font-family:'Cormorant Garamond', serif; font-style:italic; font-size:3.4rem;
    color:var(--ink-dim); opacity:0.5; position:absolute; transition:opacity 0.3s ease;
  }
  .gacha-stage.revealing .gacha-placeholder, .gacha-stage.spinning .gacha-placeholder{ opacity:0; }
  .gacha-stage img{
    width:100%; height:100%; object-fit:cover; opacity:0; transform:scale(0.7) rotate(-6deg);
  }
  .gacha-stage.revealing img{
    animation:gachaReveal 0.7s cubic-bezier(.2,.8,.3,1.2) forwards;
  }
  @keyframes gachaReveal{
    0%{ opacity:0; transform:scale(0.6) rotate(-10deg); filter:brightness(2.5); }
    60%{ opacity:1; filter:brightness(1.6); }
    100%{ opacity:1; transform:scale(1) rotate(0deg); filter:brightness(1); }
  }
  .gacha-stage.spinning{ animation:gachaSpin 0.15s linear infinite; }
  @keyframes gachaSpin{
    0%{ box-shadow:0 0 0 rgba(232,180,217,0); }
    50%{ box-shadow:0 0 30px rgba(232,180,217,0.55); }
    100%{ box-shadow:0 0 0 rgba(232,180,217,0); }
  }

  .gacha-result{
    margin-bottom:1.4rem; min-height:56px;
  }
  .gacha-result .stars{ color:var(--gold); letter-spacing:2px; font-size:0.95rem; }
  .gacha-result .rname{
    font-family:'Cormorant Garamond', serif; font-style:italic; font-size:1.5rem;
    color:var(--moth-pink); margin:0.2rem 0 0.3rem;
  }
  .gacha-result .rquote{ color:var(--ink-dim); font-size:0.85rem; max-width:420px; margin:0 auto; }

  .gacha-btn{
    font-family:'JetBrains Mono', monospace; font-size:0.82rem; letter-spacing:0.08em;
    text-transform:uppercase; color:var(--void); background:linear-gradient(120deg, var(--moth-pink), var(--gold));
    border:none; border-radius:100px; padding:0.85rem 2.2rem; cursor:pointer;
    transition:transform 0.25s ease, box-shadow 0.25s ease;
    box-shadow:0 8px 24px rgba(232,180,217,0.25);
  }
  .gacha-btn:hover:not(:disabled){ transform:translateY(-2px); box-shadow:0 12px 30px rgba(232,180,217,0.4); }
  .gacha-btn:disabled{ opacity:0.6; cursor:default; }

  .gacha-stats{
    margin-top:1.2rem; font-size:0.72rem; color:var(--ink-dim);
    letter-spacing:0.08em; text-transform:uppercase;
  }

  @media (max-width:600px){
    .gallery-grid{ grid-template-columns:repeat(3,1fr); gap:0.5rem; }
    .gacha-box{ padding:2.2rem 1.2rem; }
  }

  /* ---------- memory game ---------- */
  .memory-box{
    border:1px solid var(--line); border-radius:12px;
    background:linear-gradient(160deg, var(--plum), var(--plum-2));
    padding:3rem 2rem; text-align:center; margin-top:2.5rem;
    position:relative; overflow:hidden;
  }
  .memory-box::before{
    content:''; position:absolute; inset:0; z-index:0;
    background:radial-gradient(ellipse 60% 50% at 50% 0%, rgba(212,180,131,0.10), transparent 70%);
  }
  .memory-head, .memory-stats-row, .memory-grid, .memory-msg{ position:relative; z-index:1; }
  .memory-head h3{
    font-family:'Cormorant Garamond', serif; font-style:italic; font-weight:500;
    font-size:2rem; color:var(--ink); margin:0.4rem 0 0.6rem;
  }
  .memory-head p{ color:var(--ink-dim); font-size:0.92rem; max-width:460px; margin:0 auto; }

  .memory-stats-row{
    display:flex; align-items:center; justify-content:center; gap:1.6rem;
    margin:1.8rem 0 1.4rem; flex-wrap:wrap;
  }
  .memory-stat{ font-family:'JetBrains Mono', monospace; font-size:0.76rem; color:var(--ink-dim); letter-spacing:0.06em; text-transform:uppercase; }
  .memory-stat b{ color:var(--moth-pink); font-size:1rem; }
  .memory-reset{
    font-family:'JetBrains Mono', monospace; font-size:0.74rem; letter-spacing:0.06em;
    text-transform:uppercase; color:var(--lavender); background:rgba(168,121,201,0.08);
    border:1px solid var(--line); border-radius:100px; padding:0.5rem 1.1rem; cursor:pointer;
    transition:border-color 0.3s, color 0.3s;
  }
  .memory-reset:hover{ border-color:rgba(232,180,217,0.4); color:var(--ink); }

  .memory-grid{
    display:grid; grid-template-columns:repeat(4, minmax(64px,84px));
    gap:0.7rem; justify-content:center; margin:0 auto 1.4rem; max-width:480px;
  }
  .memory-card{ aspect-ratio:3/4; perspective:900px; cursor:pointer; }
  .memory-card-inner{
    position:relative; width:100%; height:100%;
    transition:transform 0.5s cubic-bezier(.3,.7,.3,1); transform-style:preserve-3d;
  }
  .memory-card.flipped .memory-card-inner, .memory-card.matched .memory-card-inner{ transform:rotateY(180deg); }
  .memory-face{
    position:absolute; inset:0; backface-visibility:hidden; border-radius:8px; overflow:hidden;
    border:1px solid var(--line);
  }
  .memory-front{
    background:linear-gradient(160deg, var(--void-2), var(--plum));
    display:flex; align-items:center; justify-content:center;
  }
  .memory-front svg{ width:26px; height:26px; color:var(--moth-pink); opacity:0.75; }
  .memory-back{ transform:rotateY(180deg); }
  .memory-back img{ width:100%; height:100%; object-fit:cover; }
  .memory-card.matched{ opacity:0.55; cursor:default; }
  .memory-card.matched .memory-face{ border-color:rgba(232,180,217,0.5); }

  .memory-msg{
    font-family:'Cormorant Garamond', serif; font-style:italic; font-size:1.2rem;
    color:var(--gold); min-height:1.6rem;
  }

  @media (max-width:480px){
    .memory-grid{ grid-template-columns:repeat(4, minmax(52px,64px)); gap:0.5rem; }
  }

  /* ---------- connect ---------- */
  .connect-grid{
    display:grid; grid-template-columns:repeat(auto-fit, minmax(220px,1fr)); gap:1.2rem;
  }
  .connect-card{
    display:flex; align-items:center; gap:1rem;
    padding:1.3rem 1.4rem; border:1px solid var(--line); border-radius:6px;
    background:rgba(28,18,41,0.4);
    transition:border-color 0.3s, background 0.3s, transform 0.3s;
  }
  .connect-card:hover, .connect-card:focus-visible{
    border-color:rgba(232,180,217,0.45);
    background:rgba(168,121,201,0.08);
    transform:translateX(4px);
  }
  .connect-card .icon{ width:26px; height:26px; flex-shrink:0; color:var(--moth-pink); }
  .connect-card .meta{ display:flex; flex-direction:column; overflow:hidden; }
  .connect-card .label{ font-size:0.68rem; letter-spacing:0.1em; text-transform:uppercase; color:var(--ink-dim); font-family:'JetBrains Mono', monospace; }
  .connect-card .handle{ font-size:0.98rem; color:var(--ink); margin-top:0.15rem; white-space:nowrap; text-overflow:ellipsis; overflow:hidden; }
  .connect-card.disabled{ opacity:0.55; cursor:default; }
  .connect-card.disabled:hover{ transform:none; border-color:var(--line); background:rgba(28,18,41,0.4); }

  /* ---------- music toggle ---------- */
  .music-toggle{
    position:fixed; bottom:24px; right:24px; z-index:80;
    width:48px; height:48px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    background:rgba(20,13,30,0.92);
    border:1px solid var(--line); color:var(--moth-pink);
    cursor:pointer; transition:border-color 0.3s, transform 0.3s;
    will-change:transform; contain:layout paint;
  }
  .music-toggle:hover, .music-toggle:focus-visible{
    border-color:rgba(232,180,217,0.5); transform:scale(1.06);
  }
  .music-toggle svg{ width:20px; height:20px; }
  .music-toggle .bar{
    width:2.5px; background:var(--moth-pink); border-radius:2px;
    display:inline-block; margin:0 1.5px;
    animation:musicbar 1s ease-in-out infinite;
  }
  .music-toggle .bars{ display:flex; align-items:center; height:18px; gap:1px; }
  .music-toggle .bar:nth-child(1){ height:60%; animation-delay:0s; }
  .music-toggle .bar:nth-child(2){ height:100%; animation-delay:0.2s; }
  .music-toggle .bar:nth-child(3){ height:40%; animation-delay:0.4s; }
  .music-toggle.paused .bar{ animation-play-state:paused; height:30% !important; }
  @keyframes musicbar{
    0%,100%{ transform:scaleY(0.4); }
    50%{ transform:scaleY(1); }
  }

  /* ---------- reveal on scroll ---------- */
  .reveal{
    opacity:0; transform:translateY(26px);
    transition:opacity 0.8s cubic-bezier(.22,.85,.4,1), transform 0.8s cubic-bezier(.22,.85,.4,1);
  }
  .reveal.in{ opacity:1; transform:translateY(0); }
  .reveal-stagger.in > *{ animation:none; }

  /* ---------- interactive tilt ---------- */
  .interest-card, .char-card{ transform-style:preserve-3d; }

  /* ---------- character lightbox ---------- */
  .char-card{ cursor:pointer; }
  .char-card .zoom-hint{
    position:absolute; top:1rem; right:1rem; z-index:3;
    width:32px; height:32px; border-radius:50%;
    background:rgba(11,7,19,0.55); border:1px solid var(--line);
    display:flex; align-items:center; justify-content:center;
    color:var(--ink); opacity:0; transition:opacity 0.3s, border-color 0.3s;
    cursor:zoom-in; padding:0;
  }
  .char-card:hover .zoom-hint{ opacity:1; }
  .char-card .zoom-hint:hover, .char-card .zoom-hint:focus-visible{ border-color:rgba(232,180,217,0.5); }
  .char-card .zoom-hint svg{ width:15px; height:15px; }

  /* ---------- poke reaction ---------- */
  .poke-bubble{
    position:absolute; left:50%; top:38%; z-index:4;
    transform:translate(-50%,-40%) scale(0.85);
    background:rgba(14,9,20,0.92); border:1px solid rgba(232,180,217,0.35);
    border-radius:14px; padding:0.7rem 1rem; max-width:78%;
    font-family:'Cormorant Garamond', serif; font-style:italic;
    font-size:1.05rem; color:var(--ink); text-align:center;
    opacity:0; pointer-events:none; box-shadow:var(--shadow);
    transition:opacity 0.25s ease, transform 0.25s ease;
  }
  .poke-bubble.show{ opacity:1; transform:translate(-50%,-50%) scale(1); }

  .heart-pop{
    position:absolute; z-index:4; pointer-events:none;
    color:var(--moth-pink); font-size:1rem;
    opacity:0; animation:heartPop 0.9s ease forwards;
  }
  @keyframes heartPop{
    0%{ opacity:0; transform:translate(-50%,-50%) scale(0.4); }
    25%{ opacity:1; transform:translate(-50%,-90%) scale(1.1); }
    100%{ opacity:0; transform:translate(-50%,-160%) scale(0.9); }
  }

  .gallery-item{ position:relative; }
  .gallery-item .zoom-hint-mini{
    position:absolute; top:0.5rem; right:0.5rem; z-index:3;
    width:24px; height:24px; border-radius:50%;
    background:rgba(11,7,19,0.6); border:1px solid var(--line);
    display:flex; align-items:center; justify-content:center;
    color:var(--ink); opacity:0; transition:opacity 0.3s;
    cursor:zoom-in; padding:0;
  }
  .gallery-item:hover .zoom-hint-mini{ opacity:1; }
  .gallery-item .zoom-hint-mini svg{ width:11px; height:11px; }

  .lightbox{
    position:fixed; inset:0; z-index:200;
    background:rgba(6,4,10,0.92); backdrop-filter:blur(6px);
    display:flex; align-items:center; justify-content:center;
    opacity:0; pointer-events:none; transition:opacity 0.35s ease;
    padding:6vw;
  }
  .lightbox.open{ opacity:1; pointer-events:auto; }
  .lightbox img{
    max-width:min(720px,88vw); max-height:82vh; border-radius:6px;
    box-shadow:var(--shadow); border:1px solid var(--line);
    transform:scale(0.94); transition:transform 0.35s ease;
  }
  .lightbox.open img{ transform:scale(1); }
  .lightbox-close{
    position:absolute; top:24px; right:6vw;
    width:42px; height:42px; border-radius:50%;
    background:rgba(20,13,30,0.9); border:1px solid var(--line);
    color:var(--ink); display:flex; align-items:center; justify-content:center;
    cursor:pointer; transition:border-color 0.3s, transform 0.3s;
  }
  .lightbox-close:hover, .lightbox-close:focus-visible{ border-color:rgba(232,180,217,0.5); transform:scale(1.06); }
  .lightbox-close svg{ width:18px; height:18px; }
  .lightbox-caption{
    position:absolute; bottom:34px; left:50%; transform:translateX(-50%);
    font-family:'JetBrains Mono', monospace; font-size:0.75rem;
    letter-spacing:0.08em; color:var(--ink-dim); text-align:center;
  }

  /* ---------- footer ---------- */
  footer{
    text-align:center; padding:4rem 6vw 3rem; position:relative; z-index:2;
  }
  footer .display{
    font-size:1.3rem; font-style:italic; color:var(--lavender); max-width:520px; margin:0 auto 1.2rem;
  }
  footer .credit{ font-family:'JetBrains Mono', monospace; font-size:0.72rem; color:var(--ink-dim); letter-spacing:0.06em; }
  footer .credit span{ color:var(--moth-pink); }

</style>
</head>
<body>

<div class="progress-bar" id="progressBar"></div>
<div class="cursor-glow" id="cursorGlow"></div>
<div class="bg-glow"></div>

<audio id="bgm" preload="metadata"></audio>
<button class="music-toggle" id="musicToggle" aria-label="Nyalakan atau matikan musik" aria-pressed="true">
  <span class="bars"><span class="bar"></span><span class="bar"></span><span class="bar"></span></span>
</button>

<div class="moth m1"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-.6 3-2.4 4.6-5 5-3 .5-5 3-5 6.2 0 2.6 1.6 3.8 3 3.3.9-.3 1.6-1.4 2-3 .3 1.8 1.4 3.2 3 3.2 1.7 0 2.8-1.4 3.1-3.2.4 1.6 1.1 2.7 2 3 1.4.5 3-.7 3-3.3 0-3.2-2-5.7-5-6.2-2.6-.4-4.4-2-5-5z"/></svg></div>
<div class="moth m2"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-.6 3-2.4 4.6-5 5-3 .5-5 3-5 6.2 0 2.6 1.6 3.8 3 3.3.9-.3 1.6-1.4 2-3 .3 1.8 1.4 3.2 3 3.2 1.7 0 2.8-1.4 3.1-3.2.4 1.6 1.1 2.7 2 3 1.4.5 3-.7 3-3.3 0-3.2-2-5.7-5-6.2-2.6-.4-4.4-2-5-5z"/></svg></div>
<div class="moth m3"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-.6 3-2.4 4.6-5 5-3 .5-5 3-5 6.2 0 2.6 1.6 3.8 3 3.3.9-.3 1.6-1.4 2-3 .3 1.8 1.4 3.2 3 3.2 1.7 0 2.8-1.4 3.1-3.2.4 1.6 1.1 2.7 2 3 1.4.5 3-.7 3-3.3 0-3.2-2-5.7-5-6.2-2.6-.4-4.4-2-5-5z"/></svg></div>
<div class="moth m4"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-.6 3-2.4 4.6-5 5-3 .5-5 3-5 6.2 0 2.6 1.6 3.8 3 3.3.9-.3 1.6-1.4 2-3 .3 1.8 1.4 3.2 3 3.2 1.7 0 2.8-1.4 3.1-3.2.4 1.6 1.1 2.7 2 3 1.4.5 3-.7 3-3.3 0-3.2-2-5.7-5-6.2-2.6-.4-4.4-2-5-5z"/></svg></div>
<div class="moth m5"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-.6 3-2.4 4.6-5 5-3 .5-5 3-5 6.2 0 2.6 1.6 3.8 3 3.3.9-.3 1.6-1.4 2-3 .3 1.8 1.4 3.2 3 3.2 1.7 0 2.8-1.4 3.1-3.2.4 1.6 1.1 2.7 2 3 1.4.5 3-.7 3-3.3 0-3.2-2-5.7-5-6.2-2.6-.4-4.4-2-5-5z"/></svg></div>

<nav>
  <div class="brand">mer<span>oo__</span></div>
  <button class="nav-toggle" id="navToggle" aria-label="Buka menu">☰</button>
  <div class="nav-links" id="navLinks">
    <a href="#about">About</a>
    <a href="#interests">Interests</a>
    <a href="#characters">Beloved</a>
    <a href="#gallery">Gallery</a>
    <a href="#connect">Connect</a>
  </div>
</nav>

<header class="hero">
  <div class="hero-bg">
    <img src="<?= htmlspecialchars($settings['hero_bg_image']) ?>" alt="" aria-hidden="true">
  </div>
  <svg class="moon" viewBox="0 0 100 100" fill="none">
    <path d="M62 15C45 20 34 36 34 54c0 22 18 40 40 40 5 0 10-1 14-3-10 8-23 12-37 10C25 98 6 78 5 51 4 26 22 6 47 3c5.5 6 10.5 8 15 12z" fill="var(--gold)" opacity="0.9"/>
  </svg>
  <div class="hero-content">
    <div class="eyebrow"><?= htmlspecialchars($settings['hero_eyebrow']) ?></div>
    <h1 class="display">mer<em>oo__</em></h1>
    <p class="hero-tagline">"<?= htmlspecialchars($settings['hero_tagline']) ?>"</p>
    <p class="hero-sub"><?= htmlspecialchars($settings['hero_sub']) ?></p>
  </div>
  <div class="scroll-cue"><span class="line"></span> gulir</div>
</header>

<div class="divider">
  <svg viewBox="0 0 500 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M0 20 Q60 5 100 20 T200 20" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <circle cx="250" cy="20" r="4" fill="currentColor" opacity="0.8"/>
    <path d="M240 20 Q235 8 225 10" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <path d="M260 20 Q265 8 275 10" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <path d="M300 20 Q440 5 400 20 T500 20" stroke="currentColor" stroke-width="1" opacity="0.5"/>
  </svg>
</div>

<section id="about">
  <div class="section-inner">
    <div class="about-grid">
      <div class="about-portrait reveal">
        <img src="<?= htmlspecialchars($settings['about_image']) ?>" alt="Foto profil / portrait">
      </div>
      <div class="about-text reveal">
        <p class="display"><?= htmlspecialchars($settings['about_greeting']) ?></p>
        <?= about_paragraphs($settings['about_text']) ?>
        <div class="tag-row">
          <span class="tag">genshin impact</span>
          <span class="tag">anime</span>
          <span class="tag">it &amp; programming</span>
          <span class="tag">honkai: star rail (tp dah pensi)</span>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="divider">
  <svg viewBox="0 0 500 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M0 20 Q60 5 100 20 T200 20" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <circle cx="250" cy="20" r="4" fill="currentColor" opacity="0.8"/>
    <path d="M240 20 Q235 8 225 10" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <path d="M260 20 Q265 8 275 10" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <path d="M300 20 Q440 5 400 20 T500 20" stroke="currentColor" stroke-width="1" opacity="0.5"/>
  </svg>
</div>

<section id="interests">
  <div class="section-inner">
    <div class="section-head reveal">
      <div class="eyebrow">what I'm into</div>
      <h2>Three things I <em>always look</em> forward to</h2>
      <p>Not in order of priority, just three things that are part of my daily routine — though honestly, the first two win more often lol.</p>
    </div>
    <div class="interest-grid">
      <div class="interest-card reveal tilt">
        <div class="num mono">01</div>
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M12 2l2.4 5.8L20 9l-4 4.6L17 20l-5-3-5 3 1-6.4L4 9l5.6-1.2L12 2z"/></svg>
        <h3>Genshin Impact</h3>
        <p>Exploring Teyvat, collecting characters, and overthinking about the "most meta" build. Odette is definitely my waifu, no debate.</p>
      </div>
      <div class="interest-card reveal tilt">
        <div class="num mono">02</div>
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="4" width="18" height="13" rx="1.5"/><path d="M8 21h8M12 17v4"/></svg>
        <h3>Watching Anime</h3>
        <p>From slice of life to the more intense genres. Full list's on my MyAnimeList, just check the link section.</p>
      </div>
      <div class="interest-card reveal tilt">
        <div class="num mono">03</div>
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M8 4L2 12l6 8M16 4l6 8-6 8"/></svg>
        <h3>IT &amp; Coding</h3>
        <p>I enjoy tinkering with small side projects and trying out new things in tech, whenever I have the time (and the motivation).</p>
      </div>
    </div>
  </div>
</section>

<div class="divider">
  <svg viewBox="0 0 500 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M0 20 Q60 5 100 20 T200 20" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <circle cx="250" cy="20" r="4" fill="currentColor" opacity="0.8"/>
    <path d="M240 20 Q235 8 225 10" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <path d="M260 20 Q265 8 275 10" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <path d="M300 20 Q440 5 400 20 T500 20" stroke="currentColor" stroke-width="1" opacity="0.5"/>
  </svg>
</div>

<section id="characters">
  <div class="section-inner">
    <div class="section-head reveal">
      <div class="eyebrow">closest to my heart</div>
      <h2>My <em>wife</em></h2>
      <p>So many gacha characters have come and gone, but only a few truly stuck as real favorites.</p>
    </div>
    <div class="char-grid">
      <?php if ($mainFeatured):
        $cap = $mainFeatured['name'] . ' — ' . preg_replace('/^✦\s*/', '', $mainFeatured['featured_subtitle'] ?: '');
      ?>
      <div class="char-card reveal" data-char="<?= htmlspecialchars($mainFeatured['tag']) ?>" data-char-name="<?= htmlspecialchars($mainFeatured['name']) ?>" data-caption="<?= htmlspecialchars($cap) ?>">
        <img src="<?= htmlspecialchars($mainFeatured['image_path']) ?>" alt="<?= htmlspecialchars($mainFeatured['name']) ?>">
        <button class="zoom-hint" type="button" aria-label="Lihat gambar penuh"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M9 11h4M11 9v4"/></svg></button>
        <div class="char-info">
          <?php if ($mainFeatured['featured_subtitle']): ?><span class="fav"><?= htmlspecialchars($mainFeatured['featured_subtitle']) ?></span><?php endif; ?>
          <h3><?= htmlspecialchars($mainFeatured['name']) ?></h3>
          <?php if ($mainFeatured['featured_desc']): ?><p><?= nl2br(htmlspecialchars($mainFeatured['featured_desc'])) ?></p><?php endif; ?>
        </div>
      </div>
      <?php else: ?>
      <div class="char-card reveal" style="align-items:center; justify-content:center; min-height:200px; color:var(--ink-dim); text-align:center; padding:2rem;">
        Belum ada karakter favorit. Tambahkan lewat panel admin.
      </div>
      <?php endif; ?>

      <?php if ($subFeatured): ?>
      <div class="char-sub-grid">
        <?php foreach ($subFeatured as $sf):
          $capSub = $sf['name'] . ' — ' . preg_replace('/^✦\s*/', '', $sf['featured_subtitle'] ?: '');
        ?>
        <div class="char-card reveal" data-char="<?= htmlspecialchars($sf['tag']) ?>" data-char-name="<?= htmlspecialchars($sf['name']) ?>" data-caption="<?= htmlspecialchars($capSub) ?>">
          <img src="<?= htmlspecialchars($sf['image_path']) ?>" alt="<?= htmlspecialchars($sf['name']) ?>">
          <button class="zoom-hint" type="button" aria-label="Lihat gambar penuh"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M9 11h4M11 9v4"/></svg></button>
          <div class="char-info">
            <?php if ($sf['featured_subtitle']): ?><span class="fav"><?= htmlspecialchars($sf['featured_subtitle']) ?></span><?php endif; ?>
            <h3><?= htmlspecialchars($sf['name']) ?></h3>
            <?php if ($sf['featured_desc']): ?><p><?= nl2br(htmlspecialchars($sf['featured_desc'])) ?></p><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<div class="divider">
  <svg viewBox="0 0 500 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M0 20 Q60 5 100 20 T200 20" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <circle cx="250" cy="20" r="4" fill="currentColor" opacity="0.8"/>
    <path d="M240 20 Q235 8 225 10" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <path d="M260 20 Q265 8 275 10" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <path d="M300 20 Q440 5 400 20 T500 20" stroke="currentColor" stroke-width="1" opacity="0.5"/>
  </svg>
</div>

<section id="gallery">
  <div class="section-inner">
    <div class="section-head reveal">
      <div class="eyebrow">a collection of memories</div>
      <h2>Galeri &amp; <em>Gacha</em></h2>
      <p>A collection of my favorite splash art. Click to see it bigger, or try a wish pull below — guaranteed always 5★.</p>
    </div>

    <div class="gallery-tabs" id="galleryTabs">
      <button class="gtab active" data-filter="all" type="button">All</button>
      <?php foreach ($tagRows as $tr): ?>
      <button class="gtab" data-filter="<?= htmlspecialchars($tr['tag']) ?>" type="button"><?= htmlspecialchars(ucfirst($tr['tag'])) ?></button>
      <?php endforeach; ?>
    </div>

    <div class="gallery-grid reveal" id="galleryGrid"></div>

    <?php if (count($galleryDataJs) > 0): ?>
    <div class="gacha-box reveal">
      <div class="gacha-head">
        <span class="eyebrow" style="margin-bottom:0">a bit of playful wishing</span>
        <h3>Try Your Luck</h3>
        <p>Pull a wish and see who shows up from the gallery. No pity system — everyone's already 5★ anyway.</p>
      </div>
      <div class="gacha-stage" id="gachaStage">
        <span class="gacha-placeholder">?</span>
        <img id="gachaImg" src="" alt="">
      </div>
      <div class="gacha-result" id="gachaResult"></div>
      <button class="gacha-btn" id="gachaBtn" type="button">Pull Wish ✦</button>
      <div class="gacha-stats mono" id="gachaStats">total pulls: 0</div>
    </div>

    <div class="memory-box reveal">
      <div class="memory-head">
        <span class="eyebrow" style="margin-bottom:0">memory practice</span>
        <h3>Match the Cards</h3>
        <p>Flip two cards, find the matching pair. All cards will be revealed once they're all flipped open.</p>
      </div>
      <div class="memory-stats-row">
        <span class="memory-stat">Moves: <b id="memoryMoves">0</b></span>
        <span class="memory-stat">Pairs: <b id="memoryPairs">0</b>/<b id="memoryTotalPairs">6</b></span>
        <button class="memory-reset" id="memoryReset" type="button">Shuffle Again</button>
      </div>
      <div class="memory-grid" id="memoryGrid"></div>
      <div class="memory-msg" id="memoryMsg"></div>
    </div>
    <?php else: ?>
    <p style="color:var(--ink-dim); text-align:center;">Belum ada gambar di galeri. Tambahkan lewat panel admin untuk mengaktifkan gacha &amp; game memori.</p>
    <?php endif; ?>
  </div>
</section>

<div class="divider">
  <svg viewBox="0 0 500 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M0 20 Q60 5 100 20 T200 20" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <circle cx="250" cy="20" r="4" fill="currentColor" opacity="0.8"/>
    <path d="M240 20 Q235 8 225 10" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <path d="M260 20 Q265 8 275 10" stroke="currentColor" stroke-width="1" opacity="0.5"/>
    <path d="M300 20 Q440 5 400 20 T500 20" stroke="currentColor" stroke-width="1" opacity="0.5"/>
  </svg>
</div>

<section id="connect">
  <div class="section-inner">
    <div class="section-head reveal">
      <div class="eyebrow">find me here</div>
      <h2>Let's <em>connect</em></h2>
      <p>Cards that look dimmed don't have an active link yet, coming soon once they're ready.</p>
    </div>
    <div class="connect-grid">
      <?php
      $socials = [
        ['key' => 'social_instagram', 'label' => 'Instagram',   'icon' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1"/>'],
        ['key' => 'social_github',    'label' => 'GitHub',      'icon' => '<path d="M12 2a10 10 0 0 0-3.16 19.5c.5.1.68-.22.68-.48v-1.7c-2.78.6-3.37-1.34-3.37-1.34-.46-1.15-1.11-1.46-1.11-1.46-.9-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.9 1.52 2.34 1.08 2.91.83.09-.65.35-1.08.63-1.33-2.22-.25-4.56-1.11-4.56-4.93 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.65 0 0 .84-.27 2.75 1.02a9.6 9.6 0 0 1 5 0c1.91-1.3 2.75-1.02 2.75-1.02.55 1.38.2 2.4.1 2.65.64.7 1.03 1.59 1.03 2.68 0 3.83-2.34 4.68-4.57 4.92.36.31.68.92.68 1.85v2.74c0 .27.18.58.69.48A10 10 0 0 0 12 2z" fill="currentColor"/>', 'filled' => true],
        ['key' => 'social_mal',       'label' => 'MyAnimeList', 'icon' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 15V9l2.5 3L12 9v6M15 9v6h3"/>'],
        ['key' => 'social_spotify',   'label' => 'Spotify',     'icon' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3"/>'],
        ['key' => 'social_steam',     'label' => 'Steam',       'icon' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 9h10M7 13h6"/>'],
        ['key' => 'social_x',         'label' => 'X (Twitter)', 'icon' => '<path d="M4 4l7.5 8.5L4 21h2.4l6-6.8 4.9 6.8H21l-7.9-9.3L20.6 4h-2.4l-5.5 6.2L7.4 4H4z" fill="currentColor" stroke="none"/>'],
      ];
      foreach ($socials as $s):
          $url = trim($settings[$s['key']] ?? '');
          $hasUrl = $url !== '';
          $fillAttr = !empty($s['filled']) ? 'fill="currentColor"' : 'fill="none" stroke="currentColor" stroke-width="1.5"';
          $username = $hasUrl ? extractSocialUsername($url) : '';
      ?>
      <?php if ($hasUrl): ?>
      <a class="connect-card reveal" href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer">
          <svg class="icon" viewBox="0 0 24 24" <?= $fillAttr ?>><?= $s['icon'] ?></svg>
          <div class="meta">
              <span class="label"><?= htmlspecialchars($s['label']) ?></span>
              <span class="handle"><?= $username !== '' ? htmlspecialchars('@' . $username) : 'buka tautan' ?></span>
          </div>
      </a>
      <?php else: ?>
      <div class="connect-card disabled reveal" aria-disabled="true">
          <svg class="icon" viewBox="0 0 24 24" <?= $fillAttr ?>><?= $s['icon'] ?></svg>
          <div class="meta"><span class="label"><?= htmlspecialchars($s['label']) ?></span><span class="handle">segera hadir</span></div>
      </div>
      <?php endif; ?>
      <?php endforeach; ?>
      
    </div>
  </div>
</section>

<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Pratinjau gambar karakter">
  <button class="lightbox-close" id="lightboxClose" aria-label="Tutup pratinjau">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 5l14 14M19 5L5 19"/></svg>
  </button>
  <img id="lightboxImg" src="" alt="">
  <div class="lightbox-caption" id="lightboxCaption"></div>
</div>

<footer>
  <p class="display"><?= htmlspecialchars($settings['footer_quote']) ?></p>
  <p class="credit"><?= htmlspecialchars($settings['footer_credit']) ?></p>
</footer>

<script>
  const navToggle = document.getElementById('navToggle');
  const navLinks = document.getElementById('navLinks');
  navToggle?.addEventListener('click', () => {
    navLinks.classList.toggle('open');
  });
  navLinks?.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => navLinks.classList.remove('open'));
  });

  // ---------- scroll progress bar ----------
  const progressBar = document.getElementById('progressBar');
  function updateProgress(){
    const h = document.documentElement;
    const scrolled = h.scrollTop;
    const max = h.scrollHeight - h.clientHeight;
    progressBar.style.width = (max > 0 ? (scrolled / max) * 100 : 0) + '%';
  }
  document.addEventListener('scroll', updateProgress, { passive:true });
  updateProgress();

  // ---------- cursor glow (desktop only) ----------
  const cursorGlow = document.getElementById('cursorGlow');
  if(window.matchMedia('(hover:hover)').matches){
    window.addEventListener('mousemove', (e) => {
      cursorGlow.style.opacity = '1';
      cursorGlow.style.transform = `translate(${e.clientX}px, ${e.clientY}px) translate(-50%,-50%)`;
    });
    window.addEventListener('mouseleave', () => { cursorGlow.style.opacity = '0'; });
  }

  // ---------- reveal on scroll ----------
  const revealEls = document.querySelectorAll('.reveal');
  if('IntersectionObserver' in window){
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if(entry.isIntersecting){
          entry.target.classList.add('in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold:0.15, rootMargin:'0px 0px -60px 0px' });
    revealEls.forEach(el => io.observe(el));
  } else {
    revealEls.forEach(el => el.classList.add('in'));
  }

  // ---------- active nav link on scroll ----------
  const sections = document.querySelectorAll('section[id]');
  const navAnchors = navLinks ? navLinks.querySelectorAll('a') : [];
  if('IntersectionObserver' in window && sections.length){
    const navIO = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        const link = navLinks?.querySelector(`a[href="#${entry.target.id}"]`);
        if(!link) return;
        if(entry.isIntersecting){
          navAnchors.forEach(a => a.classList.remove('active'));
          link.classList.add('active');
        }
      });
    }, { rootMargin:'-45% 0px -50% 0px' });
    sections.forEach(sec => navIO.observe(sec));
  }

  // ---------- tilt effect on cards ----------
  document.querySelectorAll('.tilt').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const r = card.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width - 0.5;
      const y = (e.clientY - r.top) / r.height - 0.5;
      card.style.transform = `translateY(-6px) rotateX(${(-y*7).toFixed(2)}deg) rotateY(${(x*7).toFixed(2)}deg)`;
    });
    card.addEventListener('mouseleave', () => { card.style.transform = ''; });
  });

  // ---------- character lightbox ----------
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxCaption = document.getElementById('lightboxCaption');
  const lightboxClose = document.getElementById('lightboxClose');

  function openLightbox(src, alt, caption){
    lightboxImg.src = src;
    lightboxImg.alt = alt;
    lightboxCaption.textContent = caption || '';
    lightbox.classList.add('open');
    document.body.style.overflow = 'hidden';
    lightboxClose.focus();
  }
  function closeLightbox(){
    lightbox.classList.remove('open');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('.char-card .zoom-hint').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const card = btn.closest('.char-card');
      const img = card?.querySelector('img');
      if(!img) return;
      openLightbox(img.src, img.alt, card.dataset.caption);
    });
  });
  lightboxClose?.addEventListener('click', closeLightbox);
  lightbox?.addEventListener('click', (e) => { if(e.target === lightbox) closeLightbox(); });
  window.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeLightbox(); });

  // ---------- poke / head-pat reaction ----------
  const pokeLines = {
    castorice: [
      "he? ...ada apa?",
      "jangan sering-sering nyentuh, nanti kebiasaan.",
      "hm. lumayan, boleh lanjut.",
      "...jangan bikin aku ketawa di depan orang.",
      "sekali lagi juga nggak apa-apa, kok.",
      "kamu ini... lucu juga ternyata."
    ],
    odette: [
      "eh?! kaget aku tau!",
      "hihi, geli~ tapi boleh lagi.",
      "ada perlu apa, hm?",
      "jangan keseringan ya, nanti aku manja.",
      "kamu selalu begini deh tiap ketemu.",
      "oke, aku maafin. tapi cuma kali ini~"
    ]
  };
  let lastPoke = {};

  function pickPokeLine(charKey){
    const pool = pokeLines[charKey] || pokeLines.castorice;
    let line;
    do{ line = pool[Math.floor(Math.random() * pool.length)]; }
    while(pool.length > 1 && line === lastPoke[charKey]);
    lastPoke[charKey] = line;
    return line;
  }

  function spawnHearts(container, x, y){
    for(let i = 0; i < 3; i++){
      const h = document.createElement('span');
      h.className = 'heart-pop';
      h.textContent = '♥';
      h.style.left = (x + (Math.random() * 30 - 15)) + 'px';
      h.style.top = y + 'px';
      h.style.animationDelay = (i * 0.08) + 's';
      container.appendChild(h);
      setTimeout(() => h.remove(), 1000);
    }
  }

  function triggerPoke(container, charKey, clickEvent, displayName){
    const charName = displayName || (charKey ? charKey.charAt(0).toUpperCase() + charKey.slice(1) : 'Dia');
    let bubble = container.querySelector('.poke-bubble');
    if(!bubble){
      bubble = document.createElement('div');
      bubble.className = 'poke-bubble';
      container.appendChild(bubble);
    }
    bubble.textContent = `${charName}: "${pickPokeLine(charKey)}"`;
    bubble.classList.remove('show');
    void bubble.offsetWidth;
    bubble.classList.add('show');
    clearTimeout(bubble._hideTimer);
    bubble._hideTimer = setTimeout(() => bubble.classList.remove('show'), 2200);

    if(clickEvent){
      const r = container.getBoundingClientRect();
      spawnHearts(container, clickEvent.clientX - r.left, clickEvent.clientY - r.top);
    }
  }

  document.querySelectorAll('.char-card[data-char]').forEach(card => {
    card.addEventListener('click', (e) => {
      if(e.target.closest('.zoom-hint')) return;
      triggerPoke(card, card.dataset.char, e, card.dataset.charName);
    });
  });

  // ---------- gallery data (dari database, lihat db.php & panel admin) ----------
  const galleryData = <?= str_replace('</', '<\/', json_encode($galleryDataJs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>;

  const galleryGrid = document.getElementById('galleryGrid');
  if(galleryGrid){
    galleryData.forEach((item, i) => {
      const fig = document.createElement('div');
      fig.className = 'gallery-item';
      fig.style.animationDelay = (i * 0.04) + 's';
      fig.dataset.tag = item.tag;
      fig.dataset.caption = `${item.name} — koleksi galeri`;
      fig.innerHTML = `
        <img src="${item.src}" alt="${item.name}, koleksi galeri" loading="lazy">
        <span class="tag-mini">${item.name}</span>
        <button class="zoom-hint-mini" type="button" aria-label="Lihat gambar penuh">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M9 11h4M11 9v4"/></svg>
        </button>`;
      fig.addEventListener('click', (e) => {
        if(e.target.closest('.zoom-hint-mini')) return;
        triggerPoke(fig, item.tag, e, item.name);
      });
      fig.querySelector('.zoom-hint-mini').addEventListener('click', (e) => {
        e.stopPropagation();
        openLightbox(item.src, `${item.name}, koleksi galeri`, fig.dataset.caption);
      });
      galleryGrid.appendChild(fig);
    });
  }

  // ---------- gallery tab filter ----------
  const galleryTabs = document.getElementById('galleryTabs');
  galleryTabs?.addEventListener('click', (e) => {
    const btn = e.target.closest('.gtab');
    if(!btn) return;
    galleryTabs.querySelectorAll('.gtab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filter = btn.dataset.filter;
    galleryGrid.querySelectorAll('.gallery-item').forEach(item => {
      item.classList.toggle('hidden', filter !== 'all' && item.dataset.tag !== filter);
    });
  });

  // ---------- gacha minigame ----------
  const gachaBtn = document.getElementById('gachaBtn');
  const gachaStage = document.getElementById('gachaStage');
  const gachaImg = document.getElementById('gachaImg');
  const gachaResult = document.getElementById('gachaResult');
  const gachaStats = document.getElementById('gachaStats');

  let pullCount = Number(localStorage.getItem('meroo_gacha_pulls') || 0);
  let lastPullSrc = null;
  if(gachaStats) gachaStats.textContent = `total pulls: ${pullCount}`;

  function pickGachaItem(){
    let pick;
    do{
      pick = galleryData[Math.floor(Math.random() * galleryData.length)];
    } while(galleryData.length > 1 && pick.src === lastPullSrc);
    lastPullSrc = pick.src;
    return pick;
  }

  gachaBtn?.addEventListener('click', () => {
    gachaBtn.disabled = true;
    gachaResult.innerHTML = '';
    gachaImg.style.opacity = '0';
    gachaStage.classList.remove('revealing');
    gachaStage.classList.add('spinning');

    setTimeout(() => {
      const item = pickGachaItem();
      gachaStage.classList.remove('spinning');
      gachaImg.src = item.src;
      gachaImg.alt = item.name;
      void gachaImg.offsetWidth;
      gachaStage.classList.add('revealing');

      pullCount++;
      localStorage.setItem('meroo_gacha_pulls', String(pullCount));
      gachaStats.textContent = `total tarikan: ${pullCount}`;

      gachaResult.innerHTML = `
        <div class="stars">★★★★★</div>
        <div class="rname">${item.name}</div>
        <div class="rquote">${item.quote}</div>
      `;
      gachaBtn.disabled = false;
    }, 900);
  });

  // ---------- memory matching game ----------
  const memoryGrid = document.getElementById('memoryGrid');
  const memoryMoves = document.getElementById('memoryMoves');
  const memoryPairs = document.getElementById('memoryPairs');
  const memoryMsg = document.getElementById('memoryMsg');
  const memoryReset = document.getElementById('memoryReset');
  const PAIR_COUNT = Math.max(2, Math.min(6, galleryData.length));

  let memState = { flipped: [], matched: 0, moves: 0, locked: false };

  function shuffleArray(arr){
    const a = arr.slice();
    for(let i = a.length - 1; i > 0; i--){
      const j = Math.floor(Math.random() * (i + 1));
      [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
  }

  function buildMemoryDeck(){
    const chosen = shuffleArray(galleryData).slice(0, PAIR_COUNT);
    const deck = shuffleArray(chosen.concat(chosen).map((item, i) => ({ ...item, uid: i })));
    return deck;
  }

  function renderMemoryGame(){
    memState = { flipped: [], matched: 0, moves: 0, locked: false };
    memoryMoves.textContent = '0';
    memoryPairs.textContent = '0';
    const totalEl = document.getElementById('memoryTotalPairs');
    if(totalEl) totalEl.textContent = String(PAIR_COUNT);
    memoryMsg.textContent = '';
    memoryGrid.innerHTML = '';

    const deck = buildMemoryDeck();
    deck.forEach((item, idx) => {
      const card = document.createElement('div');
      card.className = 'memory-card';
      card.dataset.src = item.src;
      card.dataset.index = idx;
      card.innerHTML = `
        <div class="memory-card-inner">
          <div class="memory-face memory-front">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-.6 3-2.4 4.6-5 5-3 .5-5 3-5 6.2 0 2.6 1.6 3.8 3 3.3.9-.3 1.6-1.4 2-3 .3 1.8 1.4 3.2 3 3.2 1.7 0 2.8-1.4 3.1-3.2.4 1.6 1.1 2.7 2 3 1.4.5 3-.7 3-3.3 0-3.2-2-5.7-5-6.2-2.6-.4-4.4-2-5-5z"/></svg>
          </div>
          <div class="memory-face memory-back"><img src="${item.src}" alt="${item.name}" loading="lazy"></div>
        </div>`;
      card.addEventListener('click', () => onMemoryCardClick(card));
      memoryGrid.appendChild(card);
    });
  }

  function onMemoryCardClick(card){
    if(memState.locked) return;
    if(card.classList.contains('flipped') || card.classList.contains('matched')) return;
    if(memState.flipped.length === 2) return;

    card.classList.add('flipped');
    memState.flipped.push(card);

    if(memState.flipped.length === 2){
      memState.moves++;
      memoryMoves.textContent = String(memState.moves);
      const [a, b] = memState.flipped;
      if(a.dataset.src === b.dataset.src){
        a.classList.add('matched');
        b.classList.add('matched');
        memState.matched++;
        memoryPairs.textContent = String(memState.matched);
        memState.flipped = [];
        if(memState.matched === PAIR_COUNT){
          memoryMsg.textContent = `Semua kepasangkan dalam ${memState.moves} langkah — mantap!`;
        }
      } else {
        memState.locked = true;
        setTimeout(() => {
          a.classList.remove('flipped');
          b.classList.remove('flipped');
          memState.flipped = [];
          memState.locked = false;
        }, 700);
      }
    }
  }

  memoryReset?.addEventListener('click', renderMemoryGame);
  if(memoryGrid) renderMemoryGame();

  // ---------- musik latar (playlist) ----------
  const bgm = document.getElementById('bgm');
  const musicToggle = document.getElementById('musicToggle');

  // Tinggal tambah/kurangi nama file di sini kalau mau nambah lagu lain
  const playlist = ['musik/lagu1.m4a', 'musik/lagu2.mp3', 'musik/lagu3.mp3'];
  let trackIndex = 0;
  let skipCount = 0;

  function setPausedUI(isPaused){
    musicToggle.classList.toggle('paused', isPaused);
    musicToggle.setAttribute('aria-pressed', String(!isPaused));
  }

  function loadCurrentTrack(){
    bgm.src = playlist[trackIndex];
    bgm.load();
  }

  function playCurrent(){
    if(!bgm) return;
    const p = bgm.play();
    if(p !== undefined){
      p.then(() => setPausedUI(false)).catch(() => {
        // Browser memblokir autoplay, tunggu interaksi pertama
        setPausedUI(true);
        const resume = () => {
          bgm.play().then(() => setPausedUI(false)).catch(() => {});
          window.removeEventListener('click', resume);
          window.removeEventListener('keydown', resume);
          window.removeEventListener('touchstart', resume);
          window.removeEventListener('scroll', resume);
        };
        window.addEventListener('click', resume, { once:true });
        window.addEventListener('keydown', resume, { once:true });
        window.addEventListener('touchstart', resume, { once:true });
        window.addEventListener('scroll', resume, { once:true, passive:true });
      });
    }
  }

  // Kalau file lagu di trackIndex ini nggak ketemu (404 dll), otomatis loncat ke lagu berikutnya
  bgm.addEventListener('error', () => {
    skipCount++;
    if(skipCount < playlist.length){
      trackIndex = (trackIndex + 1) % playlist.length;
      loadCurrentTrack();
      playCurrent();
    }
    // kalau semua lagu di playlist nggak ketemu, diam saja (nggak ada yang bisa diputar)
  });

  // Track ini valid, reset penghitung supaya siklus berikutnya bisa dicoba lagi dari awal
  bgm.addEventListener('canplay', () => {
    skipCount = 0;
  });

  // Lagu selesai, otomatis lanjut ke lagu berikutnya (muter ulang dari awal kalau sudah habis)
  bgm.addEventListener('ended', () => {
    trackIndex = (trackIndex + 1) % playlist.length;
    loadCurrentTrack();
    playCurrent();
  });

  loadCurrentTrack();
  playCurrent();

  musicToggle?.addEventListener('click', () => {
    if(!bgm) return;
    if(bgm.paused){
      bgm.play().then(() => setPausedUI(false)).catch(() => {});
    } else {
      bgm.pause();
      setPausedUI(true);
    }
  });
</script>

</body>
</html>
