@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h3>Create User</h3>
  <form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
    </div>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" value="{{ old('username') }}">
    </div>
    <div class="mb-3">
      {{-- Email removed: will be auto-generated from username if not provided --}}
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <input type="password" name="password_confirmation" class="form-control" required>
    </div>
    <div class="mb-3 form-check">
      <input type="checkbox" name="is_admin" class="form-check-input" id="is_admin">
      <label for="is_admin" class="form-check-label">Is Admin</label>
    </div>
    <div>
      <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
      <button class="btn btn-primary">Create</button>
    </div>
  </form>
</div>
@endsection
