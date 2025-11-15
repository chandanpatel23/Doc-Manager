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

            // show preview in modal
            const url = URL.createObjectURL(blob);
            metaPreview.src = url;

            // reset modal fields from form (if any)
            metaDocumentType.value = formDocumentType?.value || '';
            metaOrderNo.value = formOrderNo?.value || '';
            metaOrderDate.value = formOrderDate?.value || '';
            metaRemarks.value = formRemarks?.value || '';

            metaModal.show();

            // autofocus the select in modal when shown
            metaModalEl.addEventListener('shown.bs.modal', function onShown() {
                metaDocumentType.focus();
                metaModalEl.removeEventListener('shown.bs.modal', onShown);
            });

            // when user confirms, upload
            const onUpload = async () => {
                // client-side validation: require document type
                if (!metaDocumentType.value) {
                    metaDocumentType.classList.add('is-invalid');
                    metaDocumentType.focus();
                    return;
                }

                metaDocumentType.classList.remove('is-invalid');

                const fd = new FormData();
                fd.append('file', blob, 'scan.jpg');
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
                    URL.revokeObjectURL(url);
                    metaUploadBtn.removeEventListener('click', onUpload);
                }
            };

            metaUploadBtn.addEventListener('click', onUpload);
        }, 'image/jpeg', 0.9);
    });
    });
})();
