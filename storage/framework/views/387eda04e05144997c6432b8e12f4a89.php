
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
                    Scan a QR code to open the link automatically.
                </p>

                <div id="reader"></div>

            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode" defer></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    let lastText = '';
    let lastScanAt = 0;

    function onScanSuccess(decodedText) {

        const now = Date.now();

        if (decodedText === lastText && now - lastScanAt < 1500) {
            return;
        }

        lastText = decodedText;
        lastScanAt = now;

        // Check if it's a URL
        if (decodedText.startsWith('http://') || decodedText.startsWith('https://')) {
            window.open(decodedText, '_blank');
        } else {
            // For non-URLs, maybe alert or do nothing
            alert('Scanned: ' + decodedText);
        }
    }

    function onScanFailure(){}

    function initScanner(){

        if(typeof Html5QrcodeScanner === 'undefined'){
            alert('QR scanner library failed to load.');
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