@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h3>Edit User</h3>
  <form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf
    @method('PUT')
    <div class="mb-3">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control" required value="{{ old('name', $user->name) }}">
    </div>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}">
    </div>
    <div class="mb-3">
      {{-- Email removed: using placeholder generated from username if absent --}}
    </div>
    <div class="mb-3">
      <label class="form-label">Password (leave blank to keep)</label>
      <input type="password" name="password" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <input type="password" name="password_confirmation" class="form-control">
    </div>
    <div class="mb-3 form-check">
      <input type="checkbox" name="is_admin" class="form-check-input" id="is_admin" {{ $user->is_admin ? 'checked' : '' }}>
      <label for="is_admin" class="form-check-label">Is Admin</label>
    </div>
    <div>
      <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
      <button class="btn btn-primary">Save</button>
    </div>
  </form>
</div>
@endsection
