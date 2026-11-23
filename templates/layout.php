<?php
$pageTitle = [
    'login'        => 'Acceso',
    'dashboard'    => 'Panel',
    'publish'      => 'Nuevo episodio',
    'episodes'     => 'Episodios',
    'drafts'       => 'Borradores',
    'local_drafts' => 'Borradores locales',
    'podcasts'     => 'Podcasts',
    'templates'    => 'Plantillas',
][$page] ?? 'CastoPOST';

// Podcast tab colors - cycles through these
$tabColors = [
    '#2d6a4f', // verde bosque
    '#1d3557', // azul marino
    '#6d2b3d', // burdeos
    '#4a3728', // cafe oscuro
    '#2d4356', // azul pizarra
    '#4a235a', // morado oscuro
    '#1b4332', // verde oscuro
    '#7b3f00', // naranja quemado
];

// Build full podcast list: default first, then stored
$defaultPodcast = ['name' => CASTOPOD_PODCAST_HANDLE, 'handle' => CASTOPOD_PODCAST_HANDLE, 'default' => true];
$allPodcastsNav = array_merge([$defaultPodcast], array_filter($allPodcasts, fn($p) => $p['handle'] !== CASTOPOD_PODCAST_HANDLE));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> - CastoPOST</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,300;0,400;0,600;0,700;1,400&family=Noto+Sans+Mono:wght@300;400;500&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400&display=swap" rel="stylesheet">
<style>
/* ── DARK THEMES ─────────────────────────────────────────── */
html[data-theme="steel"] {
  --bg:      #0f1117; --bg2: #16181f; --bg3: #1c1e27;
  --line:    #262830; --line2: #32343e;
  --text:    #e8eaed; --text2: #9ba1ad; --text3: #5c6170;
  --accent:  #3d9be9; --accent-btn: #0f1117;
  --red:     #f06050; --green: #34d399; --r: 10px;
}
html[data-theme="violet"] {
  --bg:      #0f0f10; --bg2: #17171a; --bg3: #1f1f24;
  --line:    #28282f; --line2: #38383f;
  --text:    #f0eff4; --text2: #9998a8; --text3: #55546a;
  --accent:  #c9b8ff; --accent-btn: #0f0f10;
  --red:     #f87171; --green: #6ee7b7; --r: 9px;
}
html[data-theme="teal"] {
  --bg:      #0a1012; --bg2: #111d20; --bg3: #172428;
  --line:    #1e3035; --line2: #2a4550;
  --text:    #e8f5f7; --text2: #7aaeb5; --text3: #3d6b72;
  --accent:  #4dd9e0; --accent-btn: #0a1012;
  --red:     #f87171; --green: #6ee7b7; --r: 9px;
}
html[data-theme="amber"] {
  --bg:      #110e09; --bg2: #1c170f; --bg3: #251f15;
  --line:    #2e2618; --line2: #453a25;
  --text:    #faf5eb; --text2: #b8a882; --text3: #6b5c38;
  --accent:  #f5c542; --accent-btn: #110e09;
  --red:     #f87171; --green: #6ee7b7; --r: 9px;
}
/* ── LIGHT THEMES ────────────────────────────────────────── */
html[data-theme="steel-light"] {
  --bg:      #f4f5f7; --bg2: #ffffff; --bg3: #ecedf1;
  --line:    #d8dae0; --line2: #c8cad2;
  --text:    #1a1c22; --text2: #52566a; --text3: #888da0;
  --accent:  #2b7fd4; --accent-btn: #f4f5f7;
  --red:     #dc2626; --green: #0f8a5f; --r: 10px;
}
html[data-theme="light"] {
  --bg:      #f8f7f5; --bg2: #eeecea; --bg3: #e4e1dd;
  --line:    #d8d4cf; --line2: #bfbab3;
  --text:    #1a1917; --text2: #57534e; --text3: #a8a29e;
  --accent:  #6d28d9; --accent-btn: #f8f7f5;
  --red:     #dc2626; --green: #059669; --r: 9px;
}
html[data-theme="light-blue"] {
  --bg:      #f0f4f8; --bg2: #e2eaf3; --bg3: #d4e0ed;
  --line:    #c5d5e8; --line2: #a8c0d8;
  --text:    #0f1e2e; --text2: #3d5a7a; --text3: #7a9ab8;
  --accent:  #1d6fb8; --accent-btn: #f0f4f8;
  --red:     #dc2626; --green: #059669; --r: 9px;
}
html[data-theme="light-rose"] {
  --bg:      #fdf4f5; --bg2: #f7e8ea; --bg3: #f0dade;
  --line:    #e8cdd0; --line2: #d9b3b8;
  --text:    #2d1012; --text2: #7a3a40; --text3: #b8878d;
  --accent:  #be1a2a; --accent-btn: #fdf4f5;
  --red:     #be1a2a; --green: #059669; --r: 9px;
}
/* Fallback - default to steel */
:root {
  --bg:      #0f1117; --bg2: #16181f; --bg3: #1c1e27;
  --line:    #262830; --line2: #32343e;
  --text:    #e8eaed; --text2: #9ba1ad; --text3: #5c6170;
  --accent:  #3d9be9; --accent-btn: #0f1117;
  --red:     #f06050; --green: #34d399; --r: 10px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 16px; -webkit-text-size-adjust: 100%; }
body {
  font-family: 'Ubuntu', sans-serif;
  font-weight: 300;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
}

/* ── NAV ── */
nav {
  position: sticky; top: 0; z-index: 200;
  background: var(--bg);
  border-bottom: 1px solid var(--line);
  padding: 0 1.25rem; height: 52px;
  display: flex; align-items: center; justify-content: space-between;
}
.nav-logo {
  font-family: 'Noto Serif', serif;
  font-weight: 700; font-size: 1.2rem;
  color: var(--accent); text-decoration: none; letter-spacing: -.01em;
}
.nav-logo span { color: var(--text2); font-weight: 300; }
.nav-right { display: flex; align-items: center; gap: .2rem; }
.nav-btn {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .32rem .7rem; border-radius: 6px;
  font-size: .78rem; font-weight: 400; font-family: 'Ubuntu', sans-serif;
  color: var(--text2); text-decoration: none;
  border: none; background: none; cursor: pointer;
  transition: background .15s, color .15s;
}
.nav-btn:hover { background: var(--bg3); color: var(--text); }
.nav-btn.active { color: var(--accent); background: rgba(201,184,255,.08); }
.nav-btn.cta {
  background: var(--accent); color: var(--accent-btn);
  font-weight: 500; margin-left: .25rem;
}
.nav-btn.cta:hover { opacity: .88; background: var(--accent); }
.nav-btn.exit { color: var(--text3); }
.nav-btn.exit:hover { color: var(--red); background: none; }

/* ── MAIN ── */
main { max-width: 560px; margin: 0 auto; padding: 2rem 1.25rem 4rem; }

/* ── PAGE HEAD ── */
.page-head { margin-bottom: 1.75rem; }
.page-head h1 {
  font-family: 'Noto Serif', serif;
  font-size: 1.85rem; font-weight: 600;
  letter-spacing: -.02em; line-height: 1.2;
}
.page-head p { font-size: .85rem; color: var(--text2); margin-top: .3rem; }

/* ── ALERTS ── */
.alert {
  padding: .7rem 1rem; border-radius: var(--r);
  font-size: .83rem; margin-bottom: 1.4rem;
  border: 1px solid; animation: sIn .25s ease;
}
.alert-ok  { border-color: rgba(110,231,183,.25); color: var(--green); background: rgba(110,231,183,.06); }
.alert-err { border-color: rgba(248,113,113,.25); color: var(--red); background: rgba(248,113,113,.06); }
@keyframes sIn { from { opacity:0; transform:translateY(-5px); } to { opacity:1; transform:translateY(0); } }

/* ── PODCAST TABS ── */
.podcast-tabs { display: flex; gap: .35rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.ptab {
  padding: .32rem .85rem; border-radius: 20px;
  font-size: .78rem; font-weight: 400;
  font-family: 'Ubuntu', sans-serif;
  border: 1px solid var(--line2);
  background: none; color: var(--text2);
  cursor: pointer; text-decoration: none;
  transition: all .15s; display: inline-block;
}
.ptab:hover { color: var(--text); border-color: var(--text3); }
.ptab.active { color: #fff; border-color: transparent; font-weight: 500; }

/* ── DIVIDER ── */
.divider { height: 1px; background: var(--line); margin: 1.5rem 0; }
.group-label {
  font-family: 'Noto Sans Mono', monospace;
  font-size: .63rem; font-weight: 500;
  letter-spacing: .1em; text-transform: uppercase;
  color: var(--text3); margin-bottom: .85rem;
}

/* ── FORM ── */
.field { margin-bottom: 1rem; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
@media (max-width: 380px) { .field-row { grid-template-columns: 1fr; } }
label {
  display: block; font-size: .73rem; font-weight: 400;
  color: var(--text2); margin-bottom: .3rem; letter-spacing: .02em;
  font-family: 'Ubuntu', sans-serif;
}
label .req { color: var(--red); margin-left: 2px; }
label .sub { color: var(--text3); }

input[type="text"], input[type="password"], input[type="url"],
input[type="number"], input[type="datetime-local"], textarea, select {
  width: 100%;
  background: var(--bg2); border: 1px solid var(--line2);
  border-radius: var(--r); padding: .62rem .88rem;
  color: var(--text); font-family: 'Ubuntu', sans-serif;
  font-size: .875rem; font-weight: 300;
  outline: none; transition: border-color .15s, box-shadow .15s;
  appearance: none; -webkit-appearance: none;
}
input:focus, textarea:focus, select:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(201,184,255,.1);
}
textarea { resize: vertical; min-height: 100px; line-height: 1.6; }
select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' fill='none'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%2355546a' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right .88rem center;
  padding-right: 2.2rem; cursor: pointer;
}
input[type="checkbox"] {
  width: 15px; height: 15px; border: 1px solid var(--line2);
  border-radius: 3px; background: var(--bg2);
  accent-color: var(--accent); cursor: pointer; flex-shrink: 0;
}
.check-row { display: flex; align-items: center; gap: .5rem; cursor: pointer; font-size: .85rem; color: var(--text2); }

/* ── UPLOAD ── */
.upload-zone {
  position: relative; border: 1px dashed var(--line2);
  border-radius: var(--r); padding: 1.35rem 1rem;
  text-align: center; cursor: pointer;
  transition: border-color .2s, background .2s;
}
.upload-zone:hover, .upload-zone.over { border-color: var(--accent); background: rgba(201,184,255,.04); }
.upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; width: 100%; height: 100%; cursor: pointer; }
.upload-icon { font-size: 1.3rem; margin-bottom: .35rem; }
.upload-main { font-size: .82rem; color: var(--text2); font-weight: 400; }
.upload-sub  { font-size: .72rem; color: var(--text3); margin-top: .15rem; }
.upload-name {
  display: none; margin-top: .6rem;
  font-family: 'Noto Sans Mono', monospace; font-size: .72rem; color: var(--accent);
  background: rgba(201,184,255,.08); border-radius: 5px; padding: .28rem .55rem;
}
.or-line {
  display: flex; align-items: center; gap: .7rem;
  font-size: .72rem; color: var(--text3); margin: .8rem 0;
}
.or-line::before, .or-line::after { content:''; flex:1; height:1px; background:var(--line); }

/* ── BUTTONS ── */
.btn {
  display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
  padding: .62rem 1.35rem; border-radius: var(--r);
  font-family: 'Ubuntu', sans-serif; font-weight: 500; font-size: .875rem;
  cursor: pointer; border: none; text-decoration: none; transition: all .15s;
}
.btn-primary { background: var(--accent); color: var(--accent-btn); }
.btn-primary:hover { opacity: .88; }
.btn-ghost { background: var(--bg3); color: var(--text2); border: 1px solid var(--line2); }
.btn-ghost:hover { color: var(--text); border-color: var(--text3); }
.btn-danger { background: rgba(248,113,113,.1); color: var(--red); border: 1px solid rgba(248,113,113,.25); }
.btn-danger:hover { background: rgba(248,113,113,.18); }
.btn-full { width: 100%; }
.btn-sm { padding: .35rem .8rem; font-size: .78rem; }
.btn[disabled] { opacity:.4; pointer-events:none; }

/* ── LOGIN ── */
.login-wrap {
  min-height: 100vh; display: flex; flex-direction: column;
  align-items: center; justify-content: center; padding: 2rem 1.25rem;
}
.login-mark {
  font-family: 'Noto Serif', serif; font-size: 2.75rem; font-weight: 700;
  color: var(--accent); margin-bottom: .25rem;
}
.login-mark span { color: var(--text2); font-weight: 300; }
.login-sub {
  font-size: .72rem; color: var(--text3); margin-bottom: 2.5rem;
  letter-spacing: .08em; text-transform: uppercase;
  font-family: 'Noto Sans Mono', monospace;
}
.login-box { width: 100%; max-width: 320px; }

/* ── STATS ── */
.stats { display: grid; grid-template-columns: 1fr 1fr; gap: .65rem; margin-bottom: 1.75rem; }
.stat {
  background: var(--bg2); border: 1px solid var(--line);
  border-radius: var(--r); padding: .95rem 1rem;
  transition: border-color .15s;
}
.stat:hover { border-color: var(--line2); }
.stat-n {
  font-family: 'Noto Serif', serif; font-size: 2rem;
  line-height: 1; color: var(--text);
}
.stat-n a { color: inherit; text-decoration: none; }
.stat-n a:hover { color: var(--accent); }
.stat-l {
  font-size: .65rem; color: var(--text3); margin-top: .25rem;
  text-transform: uppercase; letter-spacing: .07em;
  font-family: 'Noto Sans Mono', monospace;
}

/* ── EPISODE LIST ── */
.ep-list { display: flex; flex-direction: column; }
.ep-item {
  display: flex; align-items: center; gap: .8rem;
  padding: .75rem 0; border-bottom: 1px solid var(--line);
}
.ep-item:last-child { border-bottom: none; }
.ep-num {
  font-family: 'Noto Sans Mono', monospace; font-size: .68rem;
  color: var(--text3); min-width: 26px; text-align: right; flex-shrink: 0;
}
.ep-info { flex: 1; min-width: 0; }
.ep-title { font-size: .88rem; font-weight: 400; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ep-meta  { font-size: .7rem; color: var(--text3); margin-top: .12rem; font-family: 'Noto Sans Mono', monospace; }
.ep-type  {
  font-size: .6rem; font-family: 'Noto Sans Mono', monospace;
  padding: .18rem .4rem; border-radius: 4px;
  border: 1px solid var(--line2); color: var(--text3); flex-shrink: 0;
}
.ep-draft {
  font-size: .6rem; font-family: 'Noto Sans Mono', monospace;
  padding: .18rem .4rem; border-radius: 4px;
  border: 1px solid rgba(201,184,255,.2);
  color: var(--accent); background: rgba(201,184,255,.06); flex-shrink: 0;
}

/* ── EMPTY ── */
.empty { text-align: center; padding: 2.5rem 1rem; color: var(--text3); }
.empty-icon { font-size: 2rem; margin-bottom: .6rem; opacity:.3; }
.empty p { font-size: .85rem; margin-bottom: 1.1rem; }

/* ── RECORDER ── */
.recorder {
  background: var(--bg2); border: 1px solid var(--line);
  border-radius: var(--r); padding: 1.1rem; text-align: center;
}
.rec-timer {
  font-family: 'Noto Sans Mono', monospace; font-size: 1.9rem; font-weight: 300;
  letter-spacing: .06em; color: var(--text); margin: .4rem 0; transition: color .3s;
}
.rec-timer.on { color: var(--red); }
.rec-status { font-size: .75rem; color: var(--text3); min-height: 1.2em; margin-bottom: .65rem; }
.rec-canvas { width: 100%; height: 44px; display: block; margin: .4rem 0; }
.rec-btns { display: flex; gap: .45rem; justify-content: center; flex-wrap: wrap; }
audio { width: 100%; margin-top: .75rem; border-radius: 6px; outline: none; }

/* ── PODCAST CARDS ── */
.podcast-card {
  display: flex; align-items: center; gap: .75rem;
  padding: .75rem 1rem; background: var(--bg2);
  border: 1px solid var(--line); border-radius: var(--r);
  margin-bottom: .5rem;
}
.podcast-card-color { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.podcast-card-info { flex: 1; }
.podcast-card-name { font-size: .9rem; font-weight: 400; }
.podcast-card-handle { font-family: 'Noto Sans Mono', monospace; font-size: .7rem; color: var(--text3); }
.podcast-card-badge {
  font-size: .62rem; font-family: 'Noto Sans Mono', monospace;
  padding: .15rem .4rem; border-radius: 4px;
  border: 1px solid var(--line2); color: var(--text3);
}

/* ── TMP FILES ── */
.tmp-item {
  display: flex; align-items: center; gap: .75rem;
  padding: .6rem 0; border-bottom: 1px solid var(--line); font-size: .82rem;
}
.tmp-item:last-child { border-bottom: none; }
.tmp-name { font-family: 'Noto Sans Mono', monospace; font-size: .72rem; flex: 1; color: var(--text2); }
.tmp-meta { font-size: .7rem; color: var(--text3); font-family: 'Noto Sans Mono', monospace; }

@keyframes fadeUp { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
.fade { animation: fadeUp .3s ease both; }

/* ── PODCAST INFO CARD ── */
.podcast-info-card {
  display: flex; gap: 1rem; align-items: flex-start;
  background: var(--bg2); border: 1px solid var(--line);
  border-radius: var(--r); padding: 1rem; margin-bottom: 1.5rem;
}
.podcast-cover {
  width: 72px; height: 72px; border-radius: 8px;
  object-fit: cover; flex-shrink: 0;
}
.podcast-info-body { flex: 1; min-width: 0; }
.podcast-info-title { font-family: 'Noto Serif', serif; font-size: 1.05rem; font-weight: 600; }
.podcast-info-desc {
  font-size: .78rem; color: var(--text2); margin-top: .25rem;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.podcast-info-meta { display: flex; gap: .5rem; margin-top: .5rem; align-items: center; flex-wrap: wrap; }
.podcast-info-meta span { font-size: .65rem; color: var(--text3); font-family: 'Noto Sans Mono', monospace; text-transform: uppercase; letter-spacing: .05em; }
.podcast-info-link {
  font-size: .65rem; font-family: 'Noto Sans Mono', monospace;
  color: var(--accent); text-decoration: none;
  border: 1px solid rgba(201,184,255,.2); border-radius: 4px;
  padding: .1rem .4rem;
}

/* ── PREVIEW BOX ── */
.preview-box {
  background: var(--bg2); border: 1px solid var(--line);
  border-radius: var(--r); padding: 1rem;
}
.preview-title { font-family: 'Noto Serif', serif; font-size: 1.05rem; font-weight: 600; margin-bottom: .25rem; }
.preview-meta { font-size: .72rem; color: var(--text3); font-family: 'Noto Sans Mono', monospace; margin-bottom: .6rem; }
.preview-desc { font-size: .82rem; color: var(--text2); line-height: 1.6; white-space: pre-wrap; }

/* ── DRAFT CHIPS ── */
.drafts-bar {
  display: flex; gap: .4rem; flex-wrap: wrap; align-items: center;
  background: rgba(110,231,183,.05); border: 1px solid rgba(110,231,183,.15);
  border-radius: var(--r); padding: .6rem .9rem; margin-bottom: 1.25rem;
}
.draft-chip {
  padding: .22rem .65rem; border-radius: 20px;
  font-size: .75rem; color: var(--green);
  background: rgba(110,231,183,.1); border: 1px solid rgba(110,231,183,.2);
  text-decoration: none; transition: background .15s;
  white-space: nowrap; max-width: 160px;
  overflow: hidden; text-overflow: ellipsis;
}
.draft-chip:hover { background: rgba(110,231,183,.2); }

/* ── CARD ── */
.card {
  background: var(--bg2); border: 1px solid var(--line);
  border-radius: var(--r); padding: 1rem;
}

/* ── TEMPLATE PREVIEW ── */
.tpl-preview {
  font-family: 'Noto Sans Mono', monospace; font-size: .72rem;
  color: var(--text2); white-space: pre-wrap; line-height: 1.5;
  max-height: 120px; overflow: hidden;
  mask-image: linear-gradient(to bottom, black 60%, transparent);
  -webkit-mask-image: linear-gradient(to bottom, black 60%, transparent);
}

/* ── THEME PICKER ── */
.theme-wrap { position: relative; }
.theme-btn { font-size: .9rem; }
.theme-dd {
  position: absolute; top: calc(100% + 6px); right: 0; z-index: 300;
  background: var(--bg2); border: 1px solid var(--line2);
  border-radius: var(--r); padding: .35rem;
  min-width: 140px; display: none;
  box-shadow: 0 8px 24px rgba(0,0,0,.35);
}
.theme-dd.open { display: block; }
.theme-dd-section {
  font-size: .6rem; font-family: 'Noto Sans Mono', monospace;
  text-transform: uppercase; letter-spacing: .08em;
  color: var(--text3); padding: .3rem .5rem .15rem;
}
.theme-opt {
  display: flex; align-items: center; gap: .5rem;
  width: 100%; padding: .38rem .55rem; border-radius: 6px;
  font-size: .8rem; font-family: 'Ubuntu', sans-serif;
  color: var(--text2); background: none; border: none;
  cursor: pointer; transition: background .1s, color .1s; text-align: left;
}
.theme-opt:hover { background: var(--bg3); color: var(--text); }
.theme-opt.active { color: var(--accent); font-weight: 500; }
.theme-dot {
  width: 11px; height: 11px; border-radius: 50%; flex-shrink: 0;
}

/* ── HAMBURGER (pure CSS checkbox trick) ── */
#navToggle { display: none; }

.nav-hamburger {
  display: none;
  width: 36px; height: 36px;
  flex-direction: column; justify-content: center; align-items: center; gap: 5px;
  cursor: pointer; border-radius: 6px; padding: 4px;
  transition: background .15s; flex-shrink: 0;
}
.nav-hamburger:hover { background: var(--bg3); }
.nav-hamburger .bar {
  display: block; width: 20px; height: 1.5px;
  background: var(--text2); border-radius: 2px;
  transition: all .25s; transform-origin: center;
}

/* Animated X when checked */
#navToggle:checked ~ nav .bar:nth-child(1) { transform: translateY(6.5px) rotate(45deg); }
#navToggle:checked ~ nav .bar:nth-child(2) { opacity: 0; transform: scaleX(0); }
#navToggle:checked ~ nav .bar:nth-child(3) { transform: translateY(-6.5px) rotate(-45deg); }

.draft-badge {
  background: rgba(201,184,255,.15); color: var(--accent);
  border-radius: 10px; padding: .05rem .45rem; font-size: .65rem;
}

/* Mobile dropdown */
.nav-mobile {
  display: none;
  position: fixed; top: 52px; left: 0; right: 0; z-index: 190;
  background: var(--bg2); border-bottom: 1px solid var(--line);
  flex-direction: column; padding: .5rem 0;
  box-shadow: 0 8px 24px rgba(0,0,0,.5);
}
#navToggle:checked ~ .nav-mobile { display: flex; }

.nav-mobile-item {
  padding: .75rem 1.5rem; font-size: .95rem; font-weight: 400;
  font-family: 'Ubuntu', sans-serif;
  color: var(--text2); text-decoration: none;
  display: flex; align-items: center; gap: .5rem;
  transition: background .1s, color .1s;
}
.nav-mobile-item:hover, .nav-mobile-item:active { background: var(--bg3); color: var(--text); }
.nav-mobile-item.active { color: var(--accent); }
.nav-mobile-item.exit { color: var(--text3); border-top: 1px solid var(--line); margin-top: .25rem; }
.nav-mobile-item.exit:hover { color: var(--red); background: none; }

/* Floating publish button (mobile only) */
.fab {
  display: none;
  position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 180;
  background: var(--accent); color: var(--accent-btn);
  border-radius: 50px; padding: .75rem 1.4rem;
  font-family: 'Ubuntu', sans-serif; font-weight: 600; font-size: .95rem;
  text-decoration: none; box-shadow: 0 4px 20px rgba(0,0,0,.3);
  transition: transform .15s, box-shadow .15s;
}
.fab:hover { transform: translateY(-2px); box-shadow: 0 6px 28px rgba(201,184,255,.45); }
.fab:active { transform: translateY(0); }

@media (max-width: 640px) {
  .nav-right { display: none; }
  .nav-hamburger { display: flex; }
  .fab { display: flex; align-items: center; gap: .4rem; }
  main { padding-bottom: 6rem; }
}
</style>
<!-- Apply saved theme immediately to avoid flash -->
<script>
(function(){
  var t = localStorage.getItem('cp_theme');
  var valid = ['steel','violet','teal','amber','steel-light','light','light-blue','light-rose'];
  document.documentElement.setAttribute('data-theme', valid.indexOf(t) >= 0 ? t : 'steel');
})();
</script>
</head>
<body>

<?php if ($page !== 'login'): ?>
<input type="checkbox" id="navToggle">
<nav>
  <a class="nav-logo" href="index.php?page=dashboard">Casto<span>POST</span></a>
  <div class="nav-right">
    <a href="index.php?page=dashboard" class="nav-btn <?= $page==='dashboard'?'active':'' ?>">Panel</a>
    <a href="index.php?page=episodes&podcast=<?= urlencode($activePodcast) ?>" class="nav-btn <?= $page==='episodes'?'active':'' ?>">Episodios</a>
    <a href="index.php?page=drafts&podcast=<?= urlencode($activePodcast) ?>" class="nav-btn <?= $page==='drafts'?'active':'' ?>">Borradores<?php if (!empty($drafts)): ?> <span class="draft-badge"><?= count($drafts) ?></span><?php endif; ?></a>
    <a href="index.php?page=podcasts" class="nav-btn <?= $page==='podcasts'?'active':'' ?>">Podcasts</a>
    <a href="index.php?page=templates" class="nav-btn <?= $page==='templates'?'active':'' ?>">Plantillas</a>
    <!-- Theme picker -->
    <div class="theme-wrap" id="themeWrap">
      <button class="nav-btn theme-btn" id="themeBtn" title="Tema">&#9680;</button>
      <div class="theme-dd" id="themeDd">
        <div class="theme-dd-section">Oscuro</div>
        <button class="theme-opt" data-theme="steel"><span class="theme-dot" style="background:#3d9be9"></span>Steel</button>
        <button class="theme-opt" data-theme="violet"><span class="theme-dot" style="background:#c9b8ff"></span>Violeta</button>
        <button class="theme-opt" data-theme="teal"><span class="theme-dot" style="background:#4dd9e0"></span>Teal</button>
        <button class="theme-opt" data-theme="amber"><span class="theme-dot" style="background:#f5c542"></span>Ambar</button>
        <div class="theme-dd-section">Claro</div>
        <button class="theme-opt" data-theme="steel-light"><span class="theme-dot" style="background:#2b7fd4"></span>Steel Claro</button>
        <button class="theme-opt" data-theme="light"><span class="theme-dot" style="background:#6d28d9"></span>Blanco</button>
        <button class="theme-opt" data-theme="light-blue"><span class="theme-dot" style="background:#1d6fb8"></span>Azul</button>
        <button class="theme-opt" data-theme="light-rose"><span class="theme-dot" style="background:#be1a2a"></span>Rosa</button>
      </div>
    </div>
    <a href="index.php?page=publish&podcast=<?= urlencode($activePodcast) ?>" class="nav-btn cta">+ Episodio</a>
    <a href="index.php?page=logout" class="nav-btn exit">Salir</a>
  </div>
  <label for="navToggle" class="nav-hamburger" aria-label="Menu">
    <span class="bar"></span>
    <span class="bar"></span>
    <span class="bar"></span>
  </label>
</nav>
<div class="nav-mobile">
  <a href="index.php?page=dashboard" class="nav-mobile-item <?= $page==='dashboard'?'active':'' ?>">Panel</a>
  <a href="index.php?page=episodes&podcast=<?= urlencode($activePodcast) ?>" class="nav-mobile-item <?= $page==='episodes'?'active':'' ?>">Episodios</a>
  <a href="index.php?page=drafts&podcast=<?= urlencode($activePodcast) ?>" class="nav-mobile-item <?= $page==='drafts'?'active':'' ?>">Borradores<?php if (!empty($drafts)): ?> <span class="draft-badge"><?= count($drafts) ?></span><?php endif; ?></a>
  <a href="index.php?page=podcasts" class="nav-mobile-item <?= $page==='podcasts'?'active':'' ?>">Podcasts</a>
  <a href="index.php?page=templates" class="nav-mobile-item <?= $page==='templates'?'active':'' ?>">Plantillas</a>
  <a href="index.php?page=local_drafts&podcast=<?= urlencode($activePodcast) ?>" class="nav-mobile-item <?= $page==='local_drafts'?'active':'' ?>">Borradores locales<?php if(!empty($localDrafts)): ?> <span class="draft-badge"><?= count($localDrafts) ?></span><?php endif; ?></a>
  <!-- Theme selector in mobile menu -->
  <div class="nav-mobile-item" style="flex-direction:column;align-items:flex-start;gap:.5rem;padding-bottom:.85rem">
    <span style="font-size:.65rem;color:var(--text3);letter-spacing:.06em;text-transform:uppercase;font-family:'Noto Sans Mono',monospace">Tema</span>
    <div style="display:flex;gap:.4rem;flex-wrap:wrap">
      <button class="theme-opt theme-opt-mobile" data-theme="steel"   style="background:var(--bg3)"><span class="theme-dot" style="background:#3d9be9"></span>Steel</button>
      <button class="theme-opt theme-opt-mobile" data-theme="violet"  style="background:var(--bg3)"><span class="theme-dot" style="background:#c9b8ff"></span>Violeta</button>
      <button class="theme-opt theme-opt-mobile" data-theme="teal"    style="background:var(--bg3)"><span class="theme-dot" style="background:#4dd9e0"></span>Teal</button>
      <button class="theme-opt theme-opt-mobile" data-theme="amber"   style="background:var(--bg3)"><span class="theme-dot" style="background:#f5c542"></span>Ambar</button>
      <button class="theme-opt theme-opt-mobile" data-theme="light"   style="background:var(--bg3)"><span class="theme-dot" style="background:#6d28d9"></span>Blanco</button>
      <button class="theme-opt theme-opt-mobile" data-theme="steel-light" style="background:var(--bg3)"><span class="theme-dot" style="background:#2b7fd4"></span>Steel Claro</button>
      <button class="theme-opt theme-opt-mobile" data-theme="light-blue" style="background:var(--bg3)"><span class="theme-dot" style="background:#1d6fb8"></span>Azul</button>
      <button class="theme-opt theme-opt-mobile" data-theme="light-rose" style="background:var(--bg3)"><span class="theme-dot" style="background:#be1a2a"></span>Rosa</button>
    </div>
  </div>
  <a href="index.php?page=logout" class="nav-mobile-item exit">Salir</a>
</div>
<!-- FAB: floating publish button, mobile only -->
<?php if ($page !== 'publish'): ?>
<a href="index.php?page=publish&podcast=<?= urlencode($activePodcast) ?>" class="fab">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
  Publicar
</a>
<?php endif; ?>
<?php endif; ?>

<main>
<?php if ($error):   ?><div class="alert alert-err fade"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-ok  fade"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<?php
// Podcast tabs (shown on all pages except login/podcasts)
if ($page !== 'login' && $page !== 'podcasts' && !empty($allPodcastsNav) ):
?>
<div class="podcast-tabs">
<?php foreach ($allPodcastsNav as $i => $pod):
    $color = $tabColors[$i % count($tabColors)];
    $isActive = $pod['handle'] === $activePodcast;
    $style = $isActive ? "background:{$color};border-color:{$color}" : '';
?>
  <a href="index.php?page=<?= urlencode($page) ?>&podcast=<?= urlencode($pod['handle']) ?>"
     class="ptab <?= $isActive ? 'active' : '' ?>"
     style="<?= $style ?>">
    <?= htmlspecialchars($pod['name']) ?>
  </a>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php
switch ($page) {
    case 'login':        include __DIR__ . '/login.php';         break;
    case 'dashboard':    include __DIR__ . '/dashboard.php';     break;
    case 'publish':      include __DIR__ . '/publish.php';       break;
    case 'episodes':     include __DIR__ . '/episodes.php';      break;
    case 'drafts':       include __DIR__ . '/drafts.php';        break;
    case 'local_drafts': include __DIR__ . '/local_drafts.php';  break;
    case 'podcasts':     include __DIR__ . '/podcasts_page.php'; break;
    case 'templates':    include __DIR__ . '/templates_page.php';break;
    default:             include __DIR__ . '/dashboard.php';
}
?>
</main>

<script>
// ── THEME PICKER ─────────────────────────────────────────
(function() {
  var VALID = ['steel','violet','teal','amber','steel-light','light','light-blue','light-rose'];

  function applyTheme(t) {
    if (VALID.indexOf(t) < 0) return;
    document.documentElement.setAttribute('data-theme', t);
    localStorage.setItem('cp_theme', t);
    document.querySelectorAll('.theme-opt').forEach(function(btn) {
      btn.classList.toggle('active', btn.dataset.theme === t);
    });
  }

  // Mark current active option
  var current = document.documentElement.getAttribute('data-theme') || 'steel';
  document.querySelectorAll('.theme-opt').forEach(function(btn) {
    btn.classList.toggle('active', btn.dataset.theme === current);
  });

  var btn = document.getElementById('themeBtn');
  var dd  = document.getElementById('themeDd');
  if (btn && dd) {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      dd.classList.toggle('open');
    });
    document.addEventListener('click', function() {
      dd.classList.remove('open');
    });
    document.querySelectorAll('.theme-opt').forEach(function(opt) {
      opt.addEventListener('click', function(e) {
        e.stopPropagation();
        applyTheme(opt.dataset.theme);
        dd.classList.remove('open');
      });
    });
  }

  window.getAccentColor = function() {
    return getComputedStyle(document.documentElement).getPropertyValue('--accent').trim();
  };
  window.getText3Color = function() {
    return getComputedStyle(document.documentElement).getPropertyValue('--text3').trim();
  };
})();

// FILE UPLOAD
document.querySelectorAll('.upload-zone').forEach(zone => {
  const input = zone.querySelector('input[type="file"]');
  const nameEl = zone.querySelector('.upload-name');
  if (!input || !nameEl) return;
  input.addEventListener('change', () => {
    if (input.files[0]) {
      nameEl.textContent = input.files[0].name + ' - ' + (input.files[0].size/1024/1024).toFixed(1) + ' MB';
      nameEl.style.display = 'block';
    }
  });
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('over'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('over'));
  zone.addEventListener('drop', e => { e.preventDefault(); zone.classList.remove('over'); });
});

// FORM SUBMIT LOADER
document.querySelectorAll('form[data-loading]').forEach(form => {
  form.addEventListener('submit', () => {
    const btn = form.querySelector('.btn-primary[type="submit"]');
    if (btn) { btn.disabled = true; btn.textContent = 'Publicando...'; }
  });
});

// SLUG AUTO
const titleEl = document.getElementById('ep-title');
const slugEl  = document.getElementById('ep-slug');
if (titleEl && slugEl) {
  titleEl.addEventListener('input', () => {
    if (!slugEl.dataset.m) slugEl.value = titleEl.value.toLowerCase()
      .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
      .replace(/[^a-z0-9\s-]/g,'').trim().replace(/\s+/g,'-').substring(0,80);
  });
  slugEl.addEventListener('input', () => { slugEl.dataset.m = '1'; });
}
</script>
</body>
</html>
