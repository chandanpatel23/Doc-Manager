@extends('layouts.app')

@section('title','Documents')

@section('content')
    <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
        <h3 class="mb-0">Documents</h3>
                <div class="d-flex gap-2">
                    <form class="d-flex" method="GET" action="{{ route('documents.index') }}">
                        <input type="search" name="q" class="form-control form-control-sm me-2" placeholder="Search title, filename, type..." value="{{ request('q', '') }}">
                        <button class="btn btn-sm btn-outline-primary me-2" type="submit">Search</button>
                        @if(request('q'))
                            <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </form>
                    <a class="btn btn-primary" href="{{ route('documents.create') }}">Scan / Upload</a>
                </div>
    </div>

    @if($documents->count())
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Preview</th>
                                <th>Title / Filename</th>
                                <th class="d-none d-sm-table-cell">Document Type</th>
                                <th class="d-none d-sm-table-cell">Order No</th>
                                <th class="d-none d-sm-table-cell">Order Date</th>
                                <th class="d-none d-sm-table-cell">MIME</th>
                                <th class="d-none d-sm-table-cell">Size</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $d)
                                @php $isImage = Str::startsWith($d->mime_type ?? '', 'image/'); @endphp
                                <tr>
                                    <td style="width:84px">
                                        @if($d->thumbnail)
                                            <img class="thumb" src="{{ Storage::url($d->thumbnail) }}" alt="{{ $d->title }}">
                                        @elseif($isImage && Storage::exists($d->filename))
                                            <img class="thumb" src="{{ route('documents.show', $d) }}" alt="{{ $d->title }}">
                                        @else
                                            <div class="border rounded d-flex align-items-center justify-content-center" style="width:72px;height:72px;color:#666">FILE</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $d->title ?? $d->filename }}</div>
                                        <div class="text-muted small">{{ $d->filename }}</div>
                                    </td>
                                    <td class="d-none d-sm-table-cell text-muted">{{ $d->document_type ?? '-' }}</td>
                                    <td class="d-none d-sm-table-cell text-muted">{{ $d->order_no ?? '-' }}</td>
                                    <td class="d-none d-sm-table-cell text-muted">{{ optional($d->order_date)->format('Y-m-d') ?? '-' }}</td>
                                    <td class="d-none d-sm-table-cell text-muted">{{ $d->remarks ? 
                                        Str::limit($d->remarks, 60) : '-' }}</td>
                                    <td class="d-none d-sm-table-cell text-muted">{{ $d->mime_type ?? '-' }}</td>
                                    <td class="d-none d-sm-table-cell text-muted">{{ $d->size ? number_format($d->size) . ' bytes' : '-' }}</td>
                                    <td class="text-muted">{{ $d->created_at }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('documents.show', $d) }}" target="_blank">View</a>
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('documents.show', $d) }}?download=1">Download</a>
                                        <a class="btn btn-sm btn-outline-info" href="{{ route('documents.edit', $d) }}">Edit</a>
                                        <form style="display:inline" method="POST" action="{{ route('documents.destroy', $d) }}" onsubmit="return confirm('Delete this document? This will remove the file and record. Continue?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            {{ $documents->links() }}
        </div>
    @else
        <div class="alert alert-info mt-3">No documents yet.</div>
    @endif
@endsection
