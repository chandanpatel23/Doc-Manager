@extends('layouts.app')

@section('title','Scan / Upload')

@section('content')
    <div class="row mt-3">
        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Scan Document</h5>

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span id="cameraStatus" class="badge bg-secondary">Camera: inactive</span>
                            </div>
                            <div id="cameraFallback" class="text-danger small" style="display:none">Camera not available in this browser.</div>
                        </div>
                        <video id="video" playsinline autoplay class="w-100" style="max-height:420px;border:1px solid #ddd;border-radius:6px"></video>
                        <canvas id="canvas" style="display:none"></canvas>
                    </div>

                    <div class="mb-2">
                        <button id="startBtn" class="btn btn-outline-primary btn-sm">Start Camera</button>
                        <button id="captureBtn" class="btn btn-primary btn-sm">Capture</button>
                        <button id="stopBtn" class="btn btn-outline-secondary btn-sm">Stop</button>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Save</h5>
                    <form id="uploadForm" method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="cameraImage" id="cameraImage">
                        <div class="mb-3">
                            <label class="form-label">Document Type</label>
                            <select id="formDocumentType" name="document_type" class="form-select">
                                <option value="">-- Select type --</option>
                                <option value="Emp Transfer Orders" {{ old('document_type') == 'Emp Transfer Orders' ? 'selected' : '' }}>Emp Transfer Orders</option>
                                <option value="DJ/Admin Orders" {{ old('document_type') == 'DJ/Admin Orders' ? 'selected' : '' }}>DJ/Admin Orders</option>
                                <option value="Committee Orders" {{ old('document_type') == 'Committee Orders' ? 'selected' : '' }}>Committee Orders</option>
                                <option value="Thana Transfer Orders" {{ old('document_type') == 'Thana Transfer Orders' ? 'selected' : '' }}>Thana Transfer Orders</option>
                                <option value="File Transfer Orders" {{ old('document_type') == 'File Transfer Orders' ? 'selected' : '' }}>File Transfer Orders</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Order No</label>
                            <input id="formOrderNo" name="order_no" type="text" value="{{ old('order_no') }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Order Date</label>
                            <input id="formOrderDate" name="order_date" type="date" value="{{ old('order_date') }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea id="formRemarks" name="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Or upload file</label>
                            <input type="file" name="file" class="form-control">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Save Document</button>
                            <a class="btn btn-secondary" href="{{ route('documents.index') }}">View Documents</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for confirming metadata before upload -->
    <div class="modal fade" id="metaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm document details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <!-- Crop/Rotate Controls -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" id="rotateLeftBtn" class="btn btn-outline-secondary" title="Rotate Left">↶ 90°</button>
                                <button type="button" id="rotateRightBtn" class="btn btn-outline-secondary" title="Rotate Right">90° ↷</button>
                                <button type="button" id="resetCropBtn" class="btn btn-outline-secondary" title="Reset">Reset</button>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary aspect-ratio-btn active" data-aspect="NaN">Free</button>
                                <button type="button" class="btn btn-outline-secondary aspect-ratio-btn" data-aspect="1">Square</button>
                                <button type="button" class="btn btn-outline-secondary aspect-ratio-btn" data-aspect="1.414">A4</button>
                            </div>
                        </div>
                        <!-- Image preview with cropper -->
                        <div style="max-height:400px;overflow:hidden">
                            <img id="metaPreview" src="#" alt="Preview" style="max-width:100%;display:block" />
                        </div>
                    </div>
                                <div class="mb-3">
                                    <label class="form-label">Document Type</label>
                                    <select id="metaDocumentType" class="form-select" aria-describedby="metaTypeFeedback">
                            <option value="">-- Select type --</option>
                            <option value="Emp Transfer Orders">Emp Transfer Orders</option>
                            <option value="DJ/Admin Orders">DJ/Admin Orders</option>
                            <option value="Committee Orders">Committee Orders</option>
                            <option value="Thana Transfer Orders">Thana Transfer Orders</option>
                            <option value="File Transfer Orders">File Transfer Orders</option>
                        </select>
                                    <div id="metaTypeFeedback" class="invalid-feedback">Please select a document type.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Order No</label>
                        <input id="metaOrderNo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Order Date</label>
                        <input id="metaOrderDate" type="date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea id="metaRemarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="metaUploadBtn" class="btn btn-primary">Save Document</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Cropper.js for image crop/rotate - loaded only on this page -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js" defer></script>
    <script src="/js/camera.js" defer></script>
@endpush
