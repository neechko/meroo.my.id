<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&family=Poppins:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
  :root{
    --void:#0b0713; --void-2:#120a1c; --plum:#1c1229; --plum-2:#251733;
    --lavender:#c9b6e4; --orchid:#a879c9; --moth-pink:#e8b4d9; --gold:#d4b483;
    --ink:#f2ecf7; --ink-dim:#a99cc0; --line:rgba(201,182,228,0.16);
    --danger:#e88a8a; --ok:#8ae8b0;
    --shadow:0 20px 60px rgba(0,0,0,0.5);
  }
  *{box-sizing:border-box;}

  body{
    background:var(--void); color:var(--ink);
    font-family:'Poppins', sans-serif; font-weight:300;
    margin:0; line-height:1.7;
  }
  a{ color:var(--moth-pink); }
  ::selection{ background:var(--orchid); color:var(--void); }

  .display{ font-family:'Cormorant Garamond', serif; }
  .mono{ font-family:'JetBrains Mono', monospace; }

  .wrap{ max-width:1100px; margin:0 auto; padding:32px 20px 80px; position:relative; z-index:2; }

  /* ---------- topbar ---------- */
  .topbar{
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 24px; background:rgba(18,10,28,0.85); backdrop-filter:blur(10px);
    border-bottom:1px solid var(--line);
    position:sticky; top:0; z-index:10;
  }
  .brand{
    font-family:'Cormorant Garamond', serif; font-style:italic;
    font-size:1.25rem; letter-spacing:0.02em; font-weight:500;
  }
  .brand span{ color:var(--moth-pink); }
  .topbar nav{
    display:flex; align-items:center; gap:20px;
    overflow-x:auto; overflow-y:hidden;
    scrollbar-width:none; -ms-overflow-style:none;
    -webkit-overflow-scrolling:touch;
  }
  .topbar nav::-webkit-scrollbar{ display:none; }
  .topbar nav a{
    margin-left:0; flex:0 0 auto;
    font-family:'JetBrains Mono', monospace;
    font-size:0.8rem; letter-spacing:0.03em;
    text-decoration:none; color:var(--ink-dim); transition:color 0.3s;
    white-space:nowrap;
  }
  .topbar nav a:hover, .topbar nav a.active{ color:var(--moth-pink); }

  @media (max-width:720px){
    .topbar{
      flex-wrap:wrap; gap:12px 0; padding:14px 16px;
    }
    .brand{ flex:1 1 auto; font-size:1.1rem; }
    .topbar nav{
      flex:1 1 100%; gap:16px;
      justify-content:flex-start;
      padding-bottom:2px;
      -webkit-mask-image:linear-gradient(to right, black 88%, transparent 100%);
      mask-image:linear-gradient(to right, black 88%, transparent 100%);
    }
    .topbar nav a{ font-size:0.72rem; }
  }

  /* ---------- headings ---------- */
  h1{
    font-family:'Cormorant Garamond', serif; font-weight:500;
    font-size:2rem; margin:0 0 6px; color:var(--ink);
  }
  h2{
    font-family:'Cormorant Garamond', serif; font-weight:500; font-style:italic;
    font-size:1.4rem; margin:0 0 14px; color:var(--lavender);
  }
  h3{ font-family:'Cormorant Garamond', serif; font-style:italic; font-weight:500; }
  em{ font-style:italic; color:var(--moth-pink); }

  .eyebrow{
    font-family:'JetBrains Mono', monospace; font-size:0.74rem;
    letter-spacing:0.14em; text-transform:uppercase; color:var(--gold);
    display:flex; align-items:center; gap:8px; margin-bottom:0.8rem;
  }
  .eyebrow::before{ content:''; width:20px; height:1px; background:var(--gold); display:inline-block; }

  /* ---------- card ---------- */
  .card{
    background:linear-gradient(160deg, var(--plum), var(--plum-2));
    border:1px solid var(--line); border-radius:12px;
    padding:24px 26px; margin-bottom:24px;
    box-shadow:var(--shadow);
  }

  .grid2{ display:grid; grid-template-columns:1fr 1fr; gap:18px; }
  @media (max-width:720px){ .grid2{ grid-template-columns:1fr; } }

  /* ---------- forms ---------- */
  label{
    display:block; font-family:'JetBrains Mono', monospace;
    font-size:0.78rem; letter-spacing:0.02em;
    color:var(--ink-dim); margin:14px 0 6px;
  }
  input[type=text], input[type=password], input[type=url], input[type=file], textarea, select{
    width:100%; padding:11px 13px; background:var(--void-2); border:1px solid var(--line);
    border-radius:8px; color:var(--ink); font-size:0.92rem; font-family:'Poppins', sans-serif;
    transition:border-color 0.3s;
  }
  textarea{ min-height:90px; resize:vertical; }
  input:focus, textarea:focus, select:focus{
    outline:none; border-color:var(--orchid);
    box-shadow:0 0 0 3px rgba(168,121,201,0.15);
  }

  /* ---------- buttons ---------- */
  .btn{
    display:inline-block; padding:10px 22px; border-radius:100px; border:none;
    background:linear-gradient(120deg, var(--moth-pink), var(--gold)); color:var(--void);
    font-family:'JetBrains Mono', monospace; font-weight:500;
    cursor:pointer; font-size:0.86rem; text-decoration:none;
    box-shadow:0 8px 24px rgba(232,180,217,0.2);
    transition:transform 0.25s ease, box-shadow 0.25s ease, filter 0.25s;
  }
  .btn:hover{ transform:translateY(-2px); box-shadow:0 12px 30px rgba(232,180,217,0.35); }
  .btn.secondary{
    background:rgba(168,121,201,0.08); color:var(--ink);
    border:1px solid var(--line); box-shadow:none;
  }
  .btn.secondary:hover{ border-color:rgba(232,180,217,0.4); transform:translateY(-2px); box-shadow:none; }
  .btn.danger{ background:linear-gradient(120deg, var(--danger), #c96a6a); color:var(--void); }
  .btn.small{ padding:6px 14px; font-size:0.72rem; }

  .checkrow{ display:flex; align-items:center; gap:8px; margin-top:14px; }
  .checkrow input{ width:auto; }

  /* ---------- messages ---------- */
  .msg{
    padding:12px 16px; border-radius:8px; margin-bottom:18px;
    font-size:0.9rem; border:1px solid transparent;
  }
  .msg.ok{ background:rgba(138,232,176,0.1); color:var(--ok); border-color:rgba(138,232,176,0.3); }
  .msg.err{ background:rgba(232,138,138,0.1); color:var(--danger); border-color:rgba(232,138,138,0.3); }

  /* ---------- table ---------- */
  .table-wrap{
    width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch;
    border-radius:8px;
  }
  table{ width:100%; border-collapse:collapse; font-size:0.88rem; min-width:560px; }
  th, td{ text-align:left; padding:12px 10px; border-bottom:1px solid var(--line); vertical-align:middle; }
  th{
    font-family:'JetBrains Mono', monospace; color:var(--ink-dim);
    font-weight:500; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.06em;
    white-space:nowrap;
  }
  td img{ width:52px; height:52px; object-fit:cover; border-radius:6px; border:1px solid var(--line); }

  .tag-pill{
    display:inline-block; padding:3px 11px; background:rgba(168,121,201,0.1);
    border:1px solid var(--line); border-radius:100px;
    font-family:'JetBrains Mono', monospace; font-size:0.72rem; color:var(--lavender);
  }
  .star{ color:var(--gold); }
  .actions{ display:flex; gap:6px; flex-wrap:wrap; }

  @media (max-width:640px){
    table{ min-width:520px; font-size:0.82rem; }
    th, td{ padding:10px 8px; }
    .actions{ gap:5px; }
    .btn.small{ padding:6px 11px; font-size:0.68rem; }
    .card{ padding:18px 16px; border-radius:10px; }
  }

  @media (max-width:420px){
    .actions{ flex-direction:column; align-items:stretch; }
    .actions .btn{ text-align:center; width:100%; }
  }

  /* ---------- login ---------- */
  .login-box{
    max-width:380px; margin:12vh auto; padding:38px 34px;
    background:linear-gradient(160deg, var(--plum), var(--plum-2));
    border:1px solid var(--line); border-radius:16px;
    box-shadow:var(--shadow); position:relative; z-index:2;
  }
  .login-box h1{
    text-align:center; font-style:italic;
  }
  .login-box .sub{
    text-align:center; color:var(--ink-dim); font-family:'JetBrains Mono', monospace;
    font-size:0.76rem; letter-spacing:0.08em; text-transform:uppercase;
    margin-bottom:24px;
  }
  .login-box .btn{ width:100%; text-align:center; margin-top:8px; }

  .hint{ font-size:0.76rem; color:var(--ink-dim); margin-top:4px; }
</style>