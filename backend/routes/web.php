<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $spaIndexPath = public_path('spa/index.html');

    if (File::exists($spaIndexPath)) {
        return response()->file($spaIndexPath);
    }

    $html = <<<'HTML'
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>League Simulator</title>
    <style>
      body {
        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial,
          "Apple Color Emoji", "Segoe UI Emoji";
        margin: 0;
        padding: 2rem;
        background: #0b1220;
        color: #e5e7eb;
      }
      a { color: #93c5fd; }
      code { background: rgba(255,255,255,.08); padding: .2rem .35rem; border-radius: .35rem; }
      .card {
        max-width: 760px;
        margin: 0 auto;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.12);
        border-radius: .75rem;
        padding: 1.5rem;
      }
      h1 { margin: 0 0 .75rem; font-size: 1.5rem; }
      p { margin: .5rem 0; color: rgba(229,231,235,.9); }
      ul { margin: .75rem 0 0; padding-left: 1.25rem; line-height: 1.6; color: rgba(229,231,235,.85); }
    </style>
  </head>
  <body>
    <div class="card">
      <h1>League Simulator</h1>
      <p>This is the Laravel API for the case project.</p>
      <p><strong>Frontend SPA not built.</strong></p>
      <ul>
        <li>Dev UI: run <code>cd frontend &amp;&amp; npm run dev</code></li>
        <li>Build SPA into backend: run <code>cd frontend &amp;&amp; npm run build</code></li>
        <li>API state: <a href="/api/simulation/state">/api/simulation/state</a></li>
      </ul>
    </div>
  </body>
</html>
HTML;

    return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
});

Route::fallback(function () {
    $spaIndexPath = public_path('spa/index.html');

    abort_unless(File::exists($spaIndexPath), 404);

    return response()->file($spaIndexPath);
});
