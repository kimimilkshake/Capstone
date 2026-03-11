<?php $__env->startSection('page-title', 'QR SCANNER'); ?>
<?php $__env->startSection('content'); ?>

<style>
.scanner-page-shell{
    max-width: 980px;
    margin: 0 auto;
    padding: 28px 16px 0;
}

.scanner-page-header{
    display:grid;
    grid-template-columns:1fr auto 1fr;
    align-items:center;
    gap:16px;
    margin-bottom:20px;
}

.scanner-page-title{
    grid-column:2;
    justify-self:center;
    margin:0;
    font-size:0.9rem;
    font-weight:800;
    letter-spacing:0.34rem;
    color:#485B8C;
}

.scanner-page-header form{
    grid-column:3;
    justify-self:end;
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

/* Mobile adjustments */
@media (max-width: 768px){

    .scanner-page-shell{
        padding:20px 10px 0;
    }

    .scanner-page-header{
        grid-template-columns:1fr;
        align-items:flex-start;
    }

    .scanner-page-title,
    .scanner-page-header form{
        grid-column:1;
    }

    .scanner-page-title{
        justify-self:center;
        width:100%;
        text-align:center;
    }

    .scanner-page-header form{
        justify-self:stretch;
        width:100%;
    }

    .scanner-header-logout{
        width:100%;
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
        text-align:center;
    }

    .btn{
        width:100%;
    }

}
</style>

<div class="staff-body scannerbox">
    <div class="scanner-page-shell">
        <div class="scanner-page-header">
            <p class="scanner-page-title">QR SCANNER</p>

            <form action="<?php echo e(route('logout')); ?>" method="POST" class="m-0">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="redirect_to" value="scanner">
                <button type="submit" class="scanner-header-logout">Logout</button>
            </form>
        </div>

        <div class="card shadow-sm border-0 scanner-wrapper" style="border-radius:12px;">
            <div class="card-body p-4">

                <p class="text-muted mb-4 scannertxt">
                    This scans any QR code and displays the raw decoded text.
                </p>

                <div id="reader"></div>

                <div class="mt-4">
                    <label class="form-label fw-bold" for="scanResult">Scanned Result</label>
                    <textarea id="scanResult" class="form-control" rows="4" readonly
                        placeholder="No QR code scanned yet."></textarea>

                    <small id="scanMeta" class="text-muted d-block mt-2"></small>
                </div>

                <div class="d-flex flex-column flex-md-row gap-2 mt-3">
                    <button id="clearResult" class="btn btn-outline-secondary" type="button">
                        Clear Result
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" defer></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const resultField = document.getElementById('scanResult');
    const metaField = document.getElementById('scanMeta');
    const clearButton = document.getElementById('clearResult');

    let lastText = '';
    let lastScanAt = 0;

    function onScanSuccess(decodedText) {

        const now = Date.now();

        if (decodedText === lastText && now - lastScanAt < 1500) {
            return;
        }

        lastText = decodedText;
        lastScanAt = now;

        resultField.value = decodedText;
        metaField.textContent = 'Last scanned: ' + new Date(now).toLocaleString();
    }

    function onScanFailure(){}

    function initScanner(){

        if(typeof Html5QrcodeScanner === 'undefined'){
            metaField.textContent = 'QR scanner library failed to load.';
            return;
        }

        // smaller scan box on mobile
        const qrSize = window.innerWidth < 768 ? 200 : 250;

        const scanner = new Html5QrcodeScanner(
            "reader",
            {
                fps:10,
                qrbox:{ width: qrSize, height: qrSize },
                rememberLastUsedCamera:true,
                supportedScanTypes:[Html5QrcodeScanType.SCAN_TYPE_CAMERA]
            },
            false
        );

        scanner.render(onScanSuccess, onScanFailure);

        clearButton.addEventListener('click',function(){
            resultField.value='';
            metaField.textContent='';
            lastText='';
            lastScanAt=0;
        });
    }

    const waitForLibrary = setInterval(function(){
        if(typeof Html5QrcodeScanner !== 'undefined'){
            clearInterval(waitForLibrary);
            initScanner();
        }
    },100);

});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Sophia\Documents\Capstone\resources\views/authorized/staff/qr_scanner.blade.php ENDPATH**/ ?>