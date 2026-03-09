
<?php $__env->startSection('page-title', 'QR SCANNER'); ?>
<?php $__env->startSection('content'); ?>
<?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<style>
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\clint\Desktop\Capstone\resources\views/authorized/staff/qr_scanner.blade.php ENDPATH**/ ?>