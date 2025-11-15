@extends('layouts.app')

@section('title','Edit Document')

@section('content')
    <div class="row mt-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Edit Document</h5>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('documents.update', $document) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Document Type</label>
                            <select name="document_type" class="form-select">
                                <option value="">-- Select type --</option>
                                <option value="Emp Transfer Orders" {{ old('document_type', $document->document_type) == 'Emp Transfer Orders' ? 'selected' : '' }}>Emp Transfer Orders</option>
                                <option value="DJ/Admin Orders" {{ old('document_type', $document->document_type) == 'DJ/Admin Orders' ? 'selected' : '' }}>DJ/Admin Orders</option>
                                <option value="Committee Orders" {{ old('document_type', $document->document_type) == 'Committee Orders' ? 'selected' : '' }}>Committee Orders</option>
                                <option value="Thana Transfer Orders" {{ old('document_type', $document->document_type) == 'Thana Transfer Orders' ? 'selected' : '' }}>Thana Transfer Orders</option>
                                <option value="File Transfer Orders" {{ old('document_type', $document->document_type) == 'File Transfer Orders' ? 'selected' : '' }}>File Transfer Orders</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Order No</label>
                            <input type="text" name="order_no" value="{{ old('order_no', $document->order_no) }}" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Order Date</label>
                            <input type="date" name="order_date" value="{{ old('order_date', optional($document->order_date)->format('Y-m-d')) }}" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="6" class="form-control">{{ old('notes', $document->notes ?? '') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" rows="3" class="form-control">{{ old('remarks', $document->remarks ?? '') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a class="btn btn-secondary" href="{{ route('documents.index') }}">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
