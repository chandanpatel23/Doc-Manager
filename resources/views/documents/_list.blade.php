@php use Illuminate\Support\Str; @endphp
<div id="documents-pagination-top" class="mb-2">
    {{ $documents->links() }}
</div>

<tbody id="documents-tbody">
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
            <td class="d-none d-sm-table-cell text-muted">{{ $d->remarks ? Str::limit($d->remarks, 60) : '-' }}</td>
            <td class="d-none d-sm-table-cell text-muted">{{ $d->mime_type ?? '-' }}</td>
            <td class="d-none d-sm-table-cell text-muted">{{ $d->size ? number_format($d->size) . ' bytes' : '-' }}</td>
            <td class="text-muted">{{ $d->created_at }}</td>
            <td>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('documents.show', $d) }}" target="_blank">View</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('documents.show', $d) }}?download=1">Download</a>
                <a class="btn btn-sm btn-outline-info" href="{{ route('documents.edit', $d) }}">Edit</a>
                @if(auth()->user()->is_admin)
                <form style="display:inline" method="POST" action="{{ route('documents.destroy', $d) }}" onsubmit="return confirm('Delete this document? This will remove the file and record. Continue?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
                @endif
            </td>
        </tr>
    @endforeach
</tbody>

<div id="documents-pagination" class="mt-3">
    {{ $documents->links() }}
</div>
