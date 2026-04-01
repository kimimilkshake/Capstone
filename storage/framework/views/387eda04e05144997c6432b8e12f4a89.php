
<?php $__env->startSection('page-title', 'QR SCANNER'); ?>
<?php $__env->startSection('content'); ?>

<style>
.staff-body.scannerbox{
    margin-right:0;
    padding:28px 20px 32px;
}

.scanner-page-shell{
    width:100%;
    max-width: 980px;
    margin: 0 auto;
    padding: 0 16px;
}

.scanner-page-header{
    position:relative;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
    min-height:42px;
    gap:16px;
}

.scanner-page-title{
    margin:0;
    font-size:0.9rem;
    font-weight:800;
    letter-spacing:0.24rem;
    color:#485B8C;
    text-align:center;
    flex:1 1 auto;
    min-width:0;
}

.scanner-page-header form{
    flex:0 0 auto;
}

.scanner-header-logout{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1px solid rgba(72, 91, 140, 0.22);
    background:#fff;
    color:#485B8C;
    border-radius:999px;
    padding:0.55rem 1.15rem;
    font-size:0.9rem;
    font-weight:700;
    transition:background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}

.scanner-header-logout:hover{
    background:#485B8C;
    border-color:#485B8C;
    color:#fff;
}

/* Scanner container */
.scanner-wrapper{
    width:100%;
    max-width: 760px;
    margin:auto;
}

.scanner-card-body{
    padding:32px !important;
}

#reader{
    width:100%;
    max-width:520px;
    margin:0 auto;
    overflow:hidden;
}

.scanner-intro{
    max-width:420px;
    margin:0 auto 1.5rem;
    padding:0;
    text-align:center;
    font-size:0.98rem;
    line-height:1.6;
}

#reader > div,
#reader video,
#reader canvas,
#reader img,
#reader table {
    max-width:100% !important;
}

#reader video,
#reader canvas,
#reader img {
    width:100% !important;
    height:auto !important;
    object-fit:cover;
    border-radius:12px;
}

#reader__scan_region,
#reader__dashboard,
#reader__dashboard_section,
#reader__dashboard_section_csr {
    width:100% !important;
}

#reader__dashboard {
    margin-top:16px;
}

#reader__dashboard button,
#reader__dashboard select,
#reader__dashboard input {
    max-width:100%;
}

.scanner-actions{
    display:flex;
    justify-content:center;
    margin-bottom:1rem;
}

.scanner-message{
    margin-bottom:1rem;
    color:#485B8C;
    font-weight:700;
    text-align:center;
    line-height:1.5;
    word-break:break-word;
}

@media (max-width: 991.98px){
    .staff-body.scannerbox{
        margin-right:0;
        padding-right:16px;
        padding-left:16px;
    }

    .scanner-page-shell{
        padding:0;
    }
}

@media (max-width: 768px){
    .staff-body.scannerbox{
        margin-left:0;
        min-height:auto;
        padding:92px 12px 20px;
    }

    .scanner-page-shell{
        padding:0;
    }

    .scanner-page-header{
        min-height:auto;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:10px;
    }

    .scanner-page-header form{
        width:100%;
        display:flex;
        justify-content:center;
    }

    .scanner-wrapper{
        max-width:100%;
    }

    .scanner-card-body{
        padding:20px 16px !important;
    }

    .scanner-page-title{
        font-size:0.9rem;
        letter-spacing:0.08rem;
        line-height:1.35;
        width:100%;
    }

    .scanner-header-logout{
        width:100%;
        max-width:280px;
    }

    .scanner-intro{
        margin-bottom:1.25rem;
        font-size:0.92rem;
    }

    .scanner-actions{
        width:100%;
    }

    #reader{
        max-width:100%;
    }
}

@media (max-width: 480px){
    .staff-body.scannerbox{
        padding-top:88px;
        padding-left:10px;
        padding-right:10px;
    }

    .scanner-card-body{
        padding:18px 12px !important;
    }

    .scanner-page-title{
        font-size:0.84rem;
        letter-spacing:0.04rem;
    }

    .scanner-header-logout{
        padding:0.7rem 1rem;
        font-size:0.88rem;
    }

    .scanner-intro,
    .scanner-message{
        font-size:0.9rem;
    }
}
</style>

<div class="staff-body scannerbox">
    <div class="scanner-page-shell">
        <div class="scanner-page-header">
                <!-- CSRF Token for AJAX requests -->
                <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
            <p class="scanner-page-title">QR SCANNER</p>

            <form action="<?php echo e(route('logout')); ?>" method="POST" class="m-0">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="redirect_to" value="scanner">
                <button type="submit" class="scanner-header-logout">Logout</button>
            </form>
        </div>

        <div class="card shadow-sm border-0 scanner-wrapper" style="border-radius:12px;">
            <div class="card-body scanner-card-body">

                <p class="text-muted scanner-intro">
                    Scan a QR code to board a passenger.
                </p>
                <div id="qr-message" class="scanner-message"></div>
                <div class="scanner-actions">
                    <button type="button" id="retry-camera" class="scanner-header-logout" style="display:none;">
                        Retry Camera
                    </button>
                </div>
                <div id="reader"></div>

            </div>
        </div>
    </div>
</div>

<script src="/js/html5-qrcode.min.js" defer></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let lastText = '';
    let lastScanAt = 0;
    let isSubmitting = false;
    let html5QrCode = null;
    let isStartingCamera = false;
    const recentScans = new Map();
    const scanCooldownMs = 8000;
    const resumeDelayMs = 2500;
    const readerElementId = 'reader';
    const retryButton = document.getElementById('retry-camera');

    if (retryButton) {
        retryButton.addEventListener('click', function() {
            startCameraScanner();
        });
    }

    function showMessage(msg, color = '#485B8C') {
        const msgDiv = document.getElementById('qr-message');
        msgDiv.textContent = msg;
        msgDiv.style.color = color;
    }

    function showScannerError(message) {
        showMessage(message, 'red');

        if (retryButton) {
            retryButton.style.display = 'inline-flex';
        }

        if (typeof showToast === 'function') {
            showToast(message, 'danger');
        }
    }

    function hideRetryButton() {
        if (retryButton) {
            retryButton.style.display = 'none';
        }
    }

    function getQrSize() {
        if (window.innerWidth <= 480) {
            return 180;
        }

        if (window.innerWidth <= 768) {
            return 220;
        }

        return 250;
    }

    function getScannerConfig() {
        const qrSize = getQrSize();

        return {
            fps: 10,
            qrbox: { width: qrSize, height: qrSize },
            aspectRatio: window.innerWidth <= 768 ? 1 : 1.333334
        };
    }

    function isCameraSecureContext() {
        return window.isSecureContext || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    }

    function normalizeCameraError(error) {
        const message = String(error || 'Unknown camera error.');

        if (!isCameraSecureContext()) {
            return 'Camera access on phones requires HTTPS or localhost. Open this page over HTTPS to use the scanner.';
        }

        if (message.includes('NotAllowedError') || message.includes('Permission denied')) {
            return 'Camera permission was blocked. Allow camera access in the browser and try again.';
        }

        if (message.includes('NotFoundError') || message.includes('OverconstrainedError')) {
            return 'No compatible rear camera was found. Try again or use a different browser on the device.';
        }

        return 'Unable to access the camera. ' + message;
    }

    function selectPreferredCamera(cameras) {
        if (!Array.isArray(cameras) || cameras.length === 0) {
            return null;
        }

        const rearCamera = cameras.find(function(camera) {
            const label = (camera.label || '').toLowerCase();
            return label.includes('back') || label.includes('rear') || label.includes('environment');
        });

        return rearCamera || cameras[0];
    }

    function cleanupRecentScans(now) {
        recentScans.forEach((timestamp, code) => {
            if (now - timestamp >= scanCooldownMs) {
                recentScans.delete(code);
            }
        });
    }

    function pauseAndResumeScanner() {
        if (!html5QrCode || typeof html5QrCode.pause !== 'function' || typeof html5QrCode.resume !== 'function') {
            return;
        }

        try {
            html5QrCode.pause(true);
            setTimeout(function() {
                try {
                    html5QrCode.resume();
                } catch (error) {
                    showMessage('Scanner resumed. Point the camera at the next QR code.', '#485B8C');
                }
            }, resumeDelayMs);
        } catch (error) {
            // Ignore pause/resume support issues and keep scanner running.
        }
    }

    async function startScannerWithSource(cameraSource) {
        return html5QrCode.start(
            cameraSource,
            getScannerConfig(),
            function(decodedText, decodedResult) {
                const now = Date.now();
                cleanupRecentScans(now);

                if (isSubmitting) {
                    return;
                }

                if (recentScans.has(decodedText)) {
                    return;
                }

                if (decodedText === lastText && now - lastScanAt < 1500) {
                    return;
                }

                lastText = decodedText;
                lastScanAt = now;

                const parts = decodedText.split(':');
                if (parts.length !== 2) {
                    showScannerError('Invalid QR code format.');
                    return;
                }

                const booking_ref_no = parts[0];
                const passenger_id = parts[1];
                isSubmitting = true;

                fetch('/qr/board-passenger', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ booking_ref_no, passenger_id })
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'An error occurred while processing the QR code.');
                    }
                    return data;
                })
                .then(data => {
                    recentScans.set(decodedText, Date.now());
                    showMessage(data.message, data.success ? 'green' : 'red');
                    if (data.success) {
                        pauseAndResumeScanner();
                        setTimeout(function() {
                            const msgDiv = document.getElementById('qr-message');
                            if (msgDiv.style.color === 'green') {
                                msgDiv.textContent = '';
                            }
                        }, 5000);
                    }
                })
                .catch(error => {
                    showScannerError(error.message || 'An error occurred while processing the QR code.');
                })
                .finally(() => {
                    isSubmitting = false;
                });
            },
            function(errorMessage) {
                // Ignore frame-level decode misses.
            }
        );
    }

    async function startCameraScanner() {
        if (isStartingCamera) {
            return;
        }

        if (typeof Html5Qrcode === 'undefined') {
            showScannerError('QR scanner library failed to load.');
            return;
        }

        isStartingCamera = true;
        hideRetryButton();
        showMessage('Requesting camera access...', '#485B8C');

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode(readerElementId);
        }

        try {
            await startScannerWithSource({ facingMode: { exact: 'environment' } });
            showMessage('Camera ready. Point it at a QR code.', '#485B8C');
            return;
        } catch (preferredError) {
            try {
                await startScannerWithSource({ facingMode: 'environment' });
                showMessage('Camera ready. Point it at a QR code.', '#485B8C');
                return;
            } catch (fallbackFacingModeError) {
                try {
                    const cameras = await Html5Qrcode.getCameras();
                    const selectedCamera = selectPreferredCamera(cameras);

                    if (!selectedCamera) {
                        throw fallbackFacingModeError;
                    }

                    await startScannerWithSource(selectedCamera.id);
                    showMessage('Camera ready. Point it at a QR code.', '#485B8C');
                    return;
                } catch (cameraListError) {
                    showScannerError(normalizeCameraError(cameraListError || fallbackFacingModeError || preferredError));
                }
            }
        } finally {
            isStartingCamera = false;
        }
    }

    // Wait for Html5Qrcode to be available, then start camera
    const waitForLibrary = setInterval(function(){
        if(typeof Html5Qrcode !== 'undefined'){
            clearInterval(waitForLibrary);
            startCameraScanner();
        }
    },100);
});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/staff/qr_scanner.blade.php ENDPATH**/ ?>