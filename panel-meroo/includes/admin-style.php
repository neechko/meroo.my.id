<style>
  :root{
    --void:#0b0713; --void-2:#120a1c; --plum:#1c1229; --plum-2:#251733;
    --lavender:#c9b6e4; --orchid:#a879c9; --moth-pink:#e8b4d9; --gold:#d4b483;
    --ink:#f2ecf7; --ink-dim:#a99cc0; --line:rgba(201,182,228,0.16);
    --danger:#e88a8a; --ok:#8ae8b0;
  }
  *{box-sizing:border-box;}
  body{
    background:var(--void); color:var(--ink); font-family:'Segoe UI',Poppins,sans-serif;
    margin:0; line-height:1.6;
  }
  a{ color:var(--moth-pink); }
  .wrap{ max-width:1100px; margin:0 auto; padding:32px 20px 80px; }
  .topbar{
    display:flex; align-items:center; justify-content:space-between;
    padding:16px 20px; background:var(--void-2); border-bottom:1px solid var(--line);
    position:sticky; top:0; z-index:10;
  }
  .brand{ font-size:1.1rem; font-weight:600; }
  .brand span{ color:var(--moth-pink); }
  .topbar nav a{ margin-left:18px; font-size:0.9rem; text-decoration:none; color:var(--ink-dim); }
  .topbar nav a:hover, .topbar nav a.active{ color:var(--moth-pink); }
  h1{ font-size:1.6rem; margin:0 0 6px; }
  h2{ font-size:1.15rem; margin:0 0 14px; color:var(--lavender); }
  .card{
    background:var(--plum); border:1px solid var(--line); border-radius:14px;
    padding:22px 24px; margin-bottom:24px;
  }
  .grid2{ display:grid; grid-template-columns:1fr 1fr; gap:18px; }
  @media (max-width:720px){ .grid2{ grid-template-columns:1fr; } }
  label{ display:block; font-size:0.82rem; color:var(--ink-dim); margin:14px 0 6px; }
  input[type=text], input[type=password], input[type=url], input[type=file], textarea, select{
    width:100%; padding:10px 12px; background:var(--void-2); border:1px solid var(--line);
    border-radius:8px; color:var(--ink); font-size:0.92rem; font-family:inherit;
  }
  textarea{ min-height:90px; resize:vertical; }
  input:focus, textarea:focus, select:focus{ outline:1.5px solid var(--orchid); }
  .btn{
    display:inline-block; padding:10px 20px; border-radius:8px; border:none;
    background:linear-gradient(135deg, var(--orchid), var(--moth-pink)); color:var(--void);
    font-weight:600; cursor:pointer; font-size:0.9rem; text-decoration:none;
  }
  .btn:hover{ filter:brightness(1.08); }
  .btn.secondary{ background:var(--void-2); color:var(--ink); border:1px solid var(--line); }
  .btn.danger{ background:var(--danger); color:var(--void); }
  .btn.small{ padding:6px 12px; font-size:0.78rem; }
  .checkrow{ display:flex; align-items:center; gap:8px; margin-top:14px; }
  .checkrow input{ width:auto; }
  .msg{ padding:12px 16px; border-radius:8px; margin-bottom:18px; font-size:0.9rem; }
  .msg.ok{ background:rgba(138,232,176,0.12); color:var(--ok); border:1px solid rgba(138,232,176,0.3); }
  .msg.err{ background:rgba(232,138,138,0.12); color:var(--danger); border:1px solid rgba(232,138,138,0.3); }
  table{ width:100%; border-collapse:collapse; font-size:0.88rem; }
  th, td{ text-align:left; padding:10px 8px; border-bottom:1px solid var(--line); vertical-align:middle; }
  th{ color:var(--ink-dim); font-weight:500; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.04em; }
  td img{ width:52px; height:52px; object-fit:cover; border-radius:6px; }
  .tag-pill{ display:inline-block; padding:2px 9px; background:var(--void-2); border-radius:20px; font-size:0.75rem; color:var(--lavender); }
  .star{ color:var(--gold); }
  .actions{ display:flex; gap:6px; flex-wrap:wrap; }
  .login-box{
    max-width:380px; margin:12vh auto; padding:34px 30px; background:var(--plum);
    border:1px solid var(--line); border-radius:16px;
  }
  .login-box h1{ text-align:center; }
  .login-box .sub{ text-align:center; color:var(--ink-dim); font-size:0.85rem; margin-bottom:20px; }
  .hint{ font-size:0.78rem; color:var(--ink-dim); margin-top:4px; }
</style>
