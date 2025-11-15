@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>User Logs</h3>
    <div>
      <a href="?export=csv{{ request()->getQueryString() ? '&' . request()->getQueryString() : '' }}" class="btn btn-sm btn-outline-secondary">Export CSV</a>
    </div>
  </div>

  <form class="row g-2 mb-3">
    <div class="col-auto">
      <input type="text" name="user" value="{{ request('user') }}" class="form-control" placeholder="username or email">
    </div>
    <div class="col-auto">
      <select name="event" class="form-select">
        <option value="">All events</option>
        <option value="login" {{ request('event')=='login'?'selected':'' }}>login</option>
        <option value="logout" {{ request('event')=='logout'?'selected':'' }}>logout</option>
      </select>
    </div>
    <div class="col-auto">
      <input type="date" name="from" value="{{ request('from') }}" class="form-control">
    </div>
    <div class="col-auto">
      <input type="date" name="to" value="{{ request('to') }}" class="form-control">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Filter</button>
      <a href="{{ route('admin.user-logs.index') }}" class="btn btn-light">Clear</a>
    </div>
  </form>
  <table class="table table-sm">
    <thead>
      <tr>
        <th>#</th>
        <th>User</th>
        <th>Event</th>
        <th>IP</th>
        <th>Meta</th>
        <th>When</th>
      </tr>
    </thead>
    <tbody>
      @foreach($logs as $log)
        <tr>
          <td>{{ $log->id }}</td>
          <td>{{ $log->user->username ?? $log->user->name ?? 'Guest' }}</td>
          <td>{{ $log->event }}</td>
          <td>{{ $log->ip_address }}</td>
          <td><pre style="white-space:pre-wrap">{{ json_encode($log->meta) }}</pre></td>
          <td>{{ $log->created_at->diffForHumans() }}</td>
          <td>
            <button class="btn btn-sm btn-outline-primary view-log-btn" data-meta='@json($log->meta)'>View</button>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{ $logs->links() }}
</div>
@endsection

@push('scripts')
<script>
$(function(){
  $(document).on('click', '.view-log-btn', function(){
    var meta = $(this).data('meta') || {};
    var pretty = JSON.stringify(meta, null, 2);
    var modalHtml = '<div class="modal fade" id="logModal" tabindex="-1" aria-hidden="true">\n  <div class="modal-dialog modal-lg">\n    <div class="modal-content">\n      <div class="modal-header">\n        <h5 class="modal-title">Log Details</h5>\n        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>\n      </div>\n      <div class="modal-body"><pre style="white-space:pre-wrap">' + $('<div/>').text(pretty).html() + '</pre></div>\n    </div>\n  </div>\n</div>';
    // ensure single modal
    $('#logModal').remove();
    $('body').append(modalHtml);
    var m = new bootstrap.Modal(document.getElementById('logModal'));
    m.show();
  });
});
</script>
@endpush
