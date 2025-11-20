<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Documents')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
      body{padding-top:56px}
      .thumb{width:72px;height:72px;object-fit:cover;border-radius:6px}
      /* Header/button hover polish */
      .navbar-brand {
        transition: transform 150ms ease, color 150ms ease;
      }
      .navbar-brand:hover {
        transform: scale(1.03);
        color: #0d6efd !important;
      }
      .navbar .nav-link {
        transition: background-color 160ms ease, color 160ms ease, transform 120ms ease;
        border-radius: 6px;
        padding: 6px 8px;
      }
      .navbar .nav-link:hover {
        background-color: rgba(13,110,253,0.06);
        color: #0d6efd !important;
        transform: translateY(-1px);
      }
      /* Header action buttons (Scan/Upload, Search/Clear) */
      .d-flex .btn {
        transition: box-shadow 160ms ease, transform 120ms ease, background-color 120ms ease;
      }
      .d-flex .btn:hover {
        box-shadow: 0 6px 18px rgba(13,110,253,0.12);
        transform: translateY(-2px);
      }
      /* Small touch for pagination buttons in header */
      #documents-pagination-top .page-link:hover {
        background-color: rgba(0,0,0,0.03);
        transform: translateY(-1px);
      }
      /* Reduce pagination arrow size */
      .pagination .page-link svg {
        width: 0.5em;
        height: 0.5em;
      }
      .pagination .page-link {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
      }
      .pagination .page-item:first-child .page-link,
      .pagination .page-item:last-child .page-link {
        padding: 0.25rem 0.5rem;
      }
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
          <ul class="navbar-nav ms-3">
            @guest
              <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
            @else
              @if(auth()->user()->is_admin)
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminMenu">
                    <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Users</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.user-logs.index') }}">User Logs</a></li>
                  </ul>
                </li>
              @endif
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ auth()->user()->username ?? auth()->user()->name }}</a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                  <li>
                    <a class="dropdown-item" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a>
                  </li>
                </ul>
              </li>
            @endguest
          </ul>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </div>
      </div>
    </nav>

    <main class="container">
      @if(session('status'))
        <div class="alert alert-success mt-3">{{ session('status') }}</div>
      @endif
      @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous" defer></script>
    @stack('scripts')
  </body>
 </html>
