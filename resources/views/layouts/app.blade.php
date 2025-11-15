<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Documents')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-" crossorigin="anonymous">
    <style>
      body{padding-top:56px}
      .thumb{width:72px;height:72px;object-fit:cover;border-radius:6px}
    </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
      <div class="container">
        <a class="navbar-brand" href="/">Doc Manager</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="{{ route('documents.index') }}">Documents</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('documents.create') }}">Scan / Upload</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <main class="container">
      @if(session('status'))
        <div class="alert alert-success mt-3">{{ session('status') }}</div>
      @endif
      @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="" crossorigin="anonymous"></script>
  </body>
 </html>
