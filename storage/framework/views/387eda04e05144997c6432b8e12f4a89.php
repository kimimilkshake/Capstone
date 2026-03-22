
<?php $__env->startSection('page-title', 'QR SCANNER'); ?>
<?php $__env->startSection('content'); ?>

<style>
.scanner-page-shell{
    max-width: 980px;
    margin: 0 auto;
    padding: 28px 16px 0;
}

.scanner-page-header{
    position:relative;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    margin-bottom:20px;
    min-height:42px;
}

.scanner-page-title{
    margin:0;
    font-size:0.9rem;
    font-weight:800;
    letter-spacing:0.34rem;
    color:#485B8C;
    text-align:center;
}

.scanner-page-header form{
    position:absolute;
    top:0;
    right:104px;
}

.scanner-header-logout{
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
    max-width: 760px;
    margin:auto;
}

/* Camera box */
#reader{
    width:100%;
    max-width:520px;
    margin:auto;
}

.scannertxt{
    margin-left: 12rem;
}

/* Mobile adjustments */
@media (max-width: 768px){

    .scanner-page-shell{
        padding:20px 10px 0;
    }

    .scanner-page-header{
        min-height:36px;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .scanner-page-title{
        width:100%;
        text-align:center;
        margin: 0;
    }

    .scanner-page-header form{
        position: static;
        margin: 0;
    }

    .scannerbox{
        padding:10px;
    }

    .scanner-wrapper{
        margin:10px;
    }

    .card-body{
        padding:20px !important;
    }

    #reader{
        max-width:100%;
    }

    textarea{
        font-size:14px;
    }

    .scannertxt{
        font-size:14px;
        margin-left: 20px;
    }

    .btn{
        width:100%;
    }

      .scanner-header-logout{
          width:auto;
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
            <div class="card-body p-4">

                <p class="text-muted mb-4 scannertxt" style="padding-left:1.5rem;">
                    Scan a QR code to board a passenger.
                </p>
                <div id="qr-message" style="margin-bottom: 1rem; color: #485B8C; font-weight: bold; text-align: center;"></div>
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
    const recentScans = new Map();
    const scanCooldownMs = 8000;
    const resumeDelayMs = 2500;

    function showMessage(msg, color = '#485B8C') {
        const msgDiv = document.getElementById('qr-message');
        msgDiv.textContent = msg;
        msgDiv.style.color = color;
    }

    function showScannerError(message) {
        showMessage(message, 'red');

        if (typeof showToast === 'function') {
            showToast(message, 'danger');
        }
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

    function startCameraScanner() {
        if (typeof Html5Qrcode === 'undefined') {
            showScannerError('QR scanner library failed to load.');
            return;
        }
        const qrSize = window.innerWidth < 768 ? 200 : 250;
        html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: { width: qrSize, height: qrSize }
            },
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

                // Expecting format: 1046:44
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
                // Optionally show scanning errors
            }
        ).catch(err => {
            showScannerError('Unable to access camera: ' + err);
        });
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