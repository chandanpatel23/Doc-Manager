@extends('layouts.app')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title text-center">Login</h4>
          <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" value="{{ old('username') }}" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" name="remember" class="form-check-input" id="remember">
              <label for="remember" class="form-check-label">Remember me</label>
            </div>
            <div class="d-flex justify-content-between">
              <a href="{{ route('documents.index') }}" class="btn btn-light">Back</a>
              <button class="btn btn-primary" type="submit">Login</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
