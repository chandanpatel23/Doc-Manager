(() => {
    window.addEventListener('load', () => {
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const captureBtn = document.getElementById('captureBtn');
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const cameraImage = document.getElementById('cameraImage');
        const titleInput = document.getElementById('titleInput');
        const metaModalEl = document.getElementById('metaModal');
        const metaPreview = document.getElementById('metaPreview');
        const metaDocumentType = document.getElementById('metaDocumentType');
        const metaOrderNo = document.getElementById('metaOrderNo');
        const metaOrderDate = document.getElementById('metaOrderDate');
        const metaRemarks = document.getElementById('metaRemarks');
        const metaUploadBtn = document.getElementById('metaUploadBtn');
        const formDocumentType = document.getElementById('formDocumentType');
        const formOrderNo = document.getElementById('formOrderNo');
        const formOrderDate = document.getElementById('formOrderDate');
        const formRemarks = document.getElementById('formRemarks');

        const metaModal = new bootstrap.Modal(metaModalEl);

        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const cameraStatus = document.getElementById('cameraStatus');
        const cameraFallback = document.getElementById('cameraFallback');

        let stream = null;
        let cropperInstance = null;

        startBtn.addEventListener('click', async () => {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    cameraFallback.style.display = '';
                    return;
                }

                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
                video.srcObject = stream;
                await video.play();
                cameraStatus.classList.remove('bg-secondary');
                cameraStatus.classList.add('bg-success');
                cameraStatus.textContent = 'Camera: active';
            } catch (e) {
                alert('Could not access camera: ' + e.message);
                cameraFallback.style.display = '';
            }
        });

        stopBtn.addEventListener('click', () => {
            if (stream) {
                stream.getTracks().forEach(t => t.stop());
                video.srcObject = null;
                stream = null;
                cameraStatus.classList.remove('bg-success');
                cameraStatus.classList.add('bg-secondary');
                cameraStatus.textContent = 'Camera: inactive';
            }
        });

        // Store current capture data
        let currentCaptureBlob = null;
        let currentBlobUrl = null;

        // Modal shown handler - initialize Cropper.js once
        const handleModalShown = () => {
            setTimeout(() => {
                if (cropperInstance) cropperInstance.destroy();
                cropperInstance = new Cropper(metaPreview, {
                    aspectRatio: NaN,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    movable: true,
                    rotatable: true,
                    scalable: true,
                    zoomable: true
                });
            }, 100);
            metaDocumentType.focus();
        };

        // Modal hidden handler - cleanup
        const handleModalHidden = () => {
            if (cropperInstance) {
                cropperInstance.destroy();
                cropperInstance = null;
            }
            if (currentBlobUrl) {
                URL.revokeObjectURL(currentBlobUrl);
                currentBlobUrl = null;
            }
            currentCaptureBlob = null;
        };

        // Register modal event listeners once
        metaModalEl.addEventListener('shown.bs.modal', handleModalShown);
        metaModalEl.addEventListener('hidden.bs.modal', handleModalHidden);

        // Cropper control buttons
        document.getElementById('rotateLeftBtn').addEventListener('click', () => {
            if (cropperInstance) cropperInstance.rotate(-90);
        });
        document.getElementById('rotateRightBtn').addEventListener('click', () => {
            if (cropperInstance) cropperInstance.rotate(90);
        });
        document.getElementById('resetCropBtn').addEventListener('click', () => {
            if (cropperInstance) cropperInstance.reset();
        });
        document.querySelectorAll('.aspect-ratio-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (cropperInstance) cropperInstance.setAspectRatio(parseFloat(btn.dataset.aspect));
                document.querySelectorAll('.aspect-ratio-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

        // Upload handler
        const handleUpload = async () => {
            // client-side validation: require document type
            if (!metaDocumentType.value) {
                metaDocumentType.classList.add('is-invalid');
                metaDocumentType.focus();
                return;
            }

            metaDocumentType.classList.remove('is-invalid');

            // Get cropped canvas from Cropper.js
            const croppedCanvas = cropperInstance ? cropperInstance.getCroppedCanvas() : canvas;
            croppedCanvas.toBlob(async (croppedBlob) => {
                if (!croppedBlob) { alert('Failed to process image'); return; }

                const fd = new FormData();
                fd.append('file', croppedBlob, 'scan.jpg');
                fd.append('document_type', metaDocumentType.value || '');
                fd.append('order_no', metaOrderNo.value || '');
                fd.append('order_date', metaOrderDate.value || '');
                fd.append('remarks', metaRemarks.value || '');
                fd.append('title', titleInput?.value || metaDocumentType.value || 'Camera scan');

                try {
                    const res = await fetch('/documents', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: fd,
                    });

                    if (!res.ok) {
                        const text = await res.text();
                        alert('Upload failed: ' + res.status + '\n' + text.substring(0, 200));
                        return;
                    }

                    const json = await res.json();
                    alert('Upload successful');
                    window.location = '/documents';
                } catch (e) {
                    alert('Upload error: ' + e.message);
                } finally {
                    metaModal.hide();
                }
            }, 'image/jpeg', 0.9);
        };

        // Register upload button listener once
        metaUploadBtn.addEventListener('click', handleUpload);

        // Capture button handler
        captureBtn.addEventListener('click', async () => {
            if (!video.videoWidth) {
                alert('Start the camera first');
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // convert to blob and show modal to confirm metadata before uploading
            canvas.toBlob((blob) => {
                if (!blob) { alert('Failed to capture image'); return; }

                // Clean up previous blob URL if exists
                if (currentBlobUrl) {
                    URL.revokeObjectURL(currentBlobUrl);
                }

                // show preview in modal
                currentBlobUrl = URL.createObjectURL(blob);
                currentCaptureBlob = blob;
                metaPreview.src = currentBlobUrl;

                // reset modal fields from form (if any)
                metaDocumentType.value = formDocumentType?.value || '';
                metaOrderNo.value = formOrderNo?.value || '';
                metaOrderDate.value = formOrderDate?.value || '';
                metaRemarks.value = formRemarks?.value || '';

                metaModal.show();
            }, 'image/jpeg', 0.9);
        });
    });
})();
