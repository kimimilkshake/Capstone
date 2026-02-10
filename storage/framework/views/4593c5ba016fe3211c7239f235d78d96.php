<?php $__env->startSection('page-title', 'Cargo Auto Placement'); ?>
<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('components.authHeader', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.staff_nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="staff-body">
        <h3 class="text-center mb-4">Cargo Auto Placement</h3>

        <div class="scs-form_container">
            <form method="GET" action="<?php echo e(route('staff.cargo.placement')); ?>" class="mb-4">
                <div class="form-group">
                    <label for="voyage_id">Select Voyage:</label>
                    <select name="voyage_id" id="voyage_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select a Voyage --</option>
                        <?php $__currentLoopData = $voyages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voyage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($voyage->voyage_id); ?>"
                                <?php echo e($selectedVoyageId == $voyage->voyage_id ? 'selected' : ''); ?>>
                                <?php echo e($voyage->voyage_code); ?> -
                                <?php echo e($voyage->routePort->route_origin); ?> → <?php echo e($voyage->routePort->route_destination); ?>

                                (<?php echo e(\Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M j, Y')); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </form>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p><?php echo e($error); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <?php if($selectedVoyageId && $placementData): ?>
                <?php if(isset($placementData['error'])): ?>
                    <div class="alert alert-warning"><?php echo e($placementData['error']); ?></div>
                <?php else: ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5>Voyage Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Voyage Code:</strong> <?php echo e($placementData['voyage']->voyage_code); ?></p>
                            <p><strong>Vessel:</strong> <?php echo e($placementData['voyage']->vessel->vessel_name); ?></p>
                            <p><strong>Route:</strong> <?php echo e($placementData['voyage']->routePort->route_origin); ?> →
                                <?php echo e($placementData['voyage']->routePort->route_destination); ?></p>
                            <p><strong>Total Hatches:</strong> <?php echo e($placementData['hatches']->count()); ?></p>
                            <p><strong>Total Cargo Items:</strong> <?php echo e($placementData['cargoReceipts']->count()); ?></p>

                            <script>
                                // Auto-trigger visualization for selected voyage on page load.
                                window.__visualizeQueue = window.__visualizeQueue || [];

                                function queueOrCallVisualize(vId) {
                                    if (typeof window.visualizeCargoPlacement === 'function') {
                                        try {
                                            window.visualizeCargoPlacement(vId);
                                        } catch (e) {
                                            console.error('visualize call failed', e);
                                        }
                                    } else {
                                        window.__visualizeQueue.push(vId);
                                        console.log('visualizeCargoPlacement queued (auto)', vId);
                                    }
                                }

                                document.addEventListener('DOMContentLoaded', function() {
                                    const selected = <?php echo e($selectedVoyageId ?? 'null'); ?>;
                                    if (selected) {
                                        // show container immediately
                                        const container = document.getElementById('visualizationContainer');
                                        if (container) container.style.display = 'block';
                                        queueOrCallVisualize(selected);
                                    }
                                });
                            </script>
                        </div>
                    </div>

                    <!-- 3D Visualization Container -->
                    <div id="visualizationContainer" style="display: none; position: relative; z-index: 1;"
                        class="mb-4 mt-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 style="margin: 0;">3D Cargo Visualization</h5>
                            </div>
                            <div style="height: 600px; width: 100%; overflow-y: clip; overflow-x: hidden;">
                                <div id="cargoViewer"
                                    style="width: 100%; height: 100%; margin: 0; padding: 0; box-sizing: border-box; border: 1px solid #ddd;">
                                </div>
                            </div>
                            <div class="mt-3 p-3">
                                <div id="visualizationStats"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5>Hatch Specifications</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Hatch</th>
                                        <th>Length (m)</th>
                                        <th>Width (m)</th>
                                        <th>Height (m)</th>
                                        <th>Weight Capacity (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $placementData['hatches']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hatch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($hatch->hatch_label); ?></td>
                                            <td><?php echo e($hatch->hatch_length); ?></td>
                                            <td><?php echo e($hatch->hatch_width); ?></td>
                                            <td><?php echo e($hatch->hatch_height); ?></td>
                                            <td><?php echo e($hatch->hatch_weight_capacity); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5>Cargo Items</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Receipt ID</th>
                                        <th>Booking Ref</th>
                                        <th>Item Description</th>
                                        <th>Qty</th>
                                        <th>L × W × H (m)</th>
                                        <th>Weight (kg)</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $placementData['cargoReceipts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receipt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $booking = \App\Models\CargoBooking::where(
                                                'booking_ref_no',
                                                $receipt->booking_ref_no,
                                            )->first();
                                            $itemDescription =
                                                $receipt->cargoItem->cargo_item_description ??
                                                'Item ' . $receipt->cargo_receipt_id;
                                        ?>
                                        <tr>
                                            <td><?php echo e($receipt->cargo_receipt_id); ?></td>
                                            <td><?php echo e($receipt->booking_ref_no); ?></td>
                                            <td><?php echo e($itemDescription); ?></td>
                                            <td><?php echo e($receipt->cargo_item_qty ?? 1); ?></td>
                                            <td>
                                                <?php if($booking): ?>
                                                    <?php echo e($booking->length); ?> × <?php echo e($booking->width); ?> ×
                                                    <?php echo e($booking->height); ?>

                                                <?php else: ?>
                                                    No dimensions
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($booking->weight ?? 'N/A'); ?></td>
                                            <td class="text-center">
                                                <?php if($booking): ?>
                                                    <button type="button" class="btn btn-sm btn-info isolate-btn"
                                                        data-item-id="<?php echo e($booking->cargo_booking_id); ?>"
                                                        data-description="<?php echo e(addslashes($itemDescription)); ?>"
                                                        onclick="toggleIsolateItem(this)">
                                                        <i class="fas fa-eye"></i> Isolate
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if(session('placement_results')): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5>Placement Results</h5>
                    </div>
                    <div class="card-body">
                        <?php $__currentLoopData = session('placement_results'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $hatchResult): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="mb-4">
                                <h6><?php echo e($hatchResult['hatch']->hatch_label); ?></h6>

                                <?php if(isset($hatchResult['error'])): ?>
                                    <div class="alert alert-danger"><?php echo e($hatchResult['error']); ?></div>
                                <?php else: ?>
                                    <?php
                                        $result = $hatchResult['result'];
                                        $packedItems = $result['response']['packed_items'] ?? [];
                                        $unpackedItems = $result['response']['unpacked_items'] ?? [];
                                    ?>

                                    <p><strong>Packed Items:</strong> <?php echo e(count($packedItems)); ?></p>
                                    <p><strong>Unpacked Items:</strong> <?php echo e(count($unpackedItems)); ?></p>

                                    <?php if(!empty($packedItems)): ?>
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Item ID</th>
                                                    <th>Position (x, y, z)</th>
                                                    <th>Dimensions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__currentLoopData = $packedItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($item['id'] ?? 'N/A'); ?></td>
                                                        <td><?php echo e($item['x'] ?? 0); ?>, <?php echo e($item['y'] ?? 0); ?>,
                                                            <?php echo e($item['z'] ?? 0); ?></td>
                                                        <td><?php echo e($item['w'] ?? 0); ?> × <?php echo e($item['h'] ?? 0); ?> ×
                                                            <?php echo e($item['d'] ?? 0); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if(session('remaining_items') && count(session('remaining_items')) > 0): ?>
                            <div class="alert alert-warning">
                                <strong>Warning:</strong> <?php echo e(count(session('remaining_items'))); ?> items could not be placed
                                in any hatch.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script type="module">
        // Use a reliable three.js ESM build URL
        import * as THREE from 'https://unpkg.com/three@0.128.0/build/three.module.js';
        // expose THREE to the window so the visualization module can use it
        window.THREE = THREE;

        let visualizer = null;
        // ensure closeVisualization exists so the close button won't throw before module loads
        if (!window.closeVisualization) {
            window.closeVisualization = function() {
                const c = document.getElementById('visualizationContainer');
                if (c) c.style.display = 'none';
            };
        }
        let CargoVisualizerClass = window.CargoVisualizer || null;

        // Inline fallback renderer helpers (used if module import fails)
        let __inlineFallback = {
            renderer: null,
            scene: null,
            camera: null
        };

        function disposeInlineFallback() {
            try {
                if (__inlineFallback.renderer) {
                    __inlineFallback.renderer.dispose();
                    const el = document.getElementById('cargoViewer');
                    if (el && el.firstChild) el.removeChild(el.firstChild);
                }
            } catch (e) {
                console.warn('disposeInlineFallback error', e);
            }
            __inlineFallback = {
                renderer: null,
                scene: null,
                camera: null
            };
        }

        function inlineFallbackRender(data) {
            console.log('inlineFallbackRender called', data);
            disposeInlineFallback();
            const container = document.getElementById('cargoViewer');
            if (!container) {
                console.error('No cargoViewer container');
                return;
            }

            const width = container.clientWidth || 800;
            const height = container.clientHeight || 600;
            const scene = new THREE.Scene();
            scene.background = new THREE.Color(0xf8f8f8);
            const camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 10000);
            camera.position.set(30, 20, 30);
            camera.lookAt(0, 0, 0);

            const renderer = new THREE.WebGLRenderer({
                antialias: true
            });
            renderer.setSize(width, height);
            renderer.domElement.style.width = '100%';
            renderer.domElement.style.height = '100%';
            renderer.domElement.style.position = 'absolute';
            renderer.domElement.style.top = '0';
            renderer.domElement.style.left = '0';
            container.appendChild(renderer.domElement);

            const ambient = new THREE.AmbientLight(0xffffff, 0.6);
            scene.add(ambient);
            const dir = new THREE.DirectionalLight(0xffffff, 0.8);
            dir.position.set(50, 50, 50);
            scene.add(dir);
            const grid = new THREE.GridHelper(200, 20);
            scene.add(grid);

            (data.hatches || []).forEach((h) => {
                const geom = new THREE.BoxGeometry(h.width, h.height, h.depth);
                const mat = new THREE.MeshStandardMaterial({
                    color: 0x888888,
                    transparent: true,
                    opacity: 0.12
                });
                const mesh = new THREE.Mesh(geom, mat);
                mesh.position.set(h.width / 2, h.height / 2, h.depth / 2);
                scene.add(mesh);
                const helper = new THREE.BoxHelper(mesh, 0x222222);
                scene.add(helper);
            });

            const firstH = (data.hatches && data.hatches[0]) || null;
            const items = [];
            (data.cargo || []).forEach(c => {
                const qty = c.quantity || 1;
                for (let i = 0; i < qty; i++) items.push(c);
            });
            if (firstH) {
                let cx = 0,
                    cz = 0;
                const gap = 0.1;
                items.forEach((it, idx) => {
                    if (cx + it.width > firstH.width) {
                        cx = 0;
                        cz += it.depth + gap;
                    }
                    const geom = new THREE.BoxGeometry(it.width, it.height, it.depth);
                    const mat = new THREE.MeshStandardMaterial({
                        color: 0x77a1ff
                    });
                    const mesh = new THREE.Mesh(geom, mat);
                    mesh.position.set(cx + it.width / 2, it.height / 2, cz + it.depth / 2);
                    scene.add(mesh);
                    const helper = new THREE.BoxHelper(mesh, 0x000000);
                    scene.add(helper);
                    cx += it.width + gap;
                });
            }

            function animate() {
                requestAnimationFrame(animate);
                renderer.render(scene, camera);
            }
            animate();

            __inlineFallback = {
                renderer,
                scene,
                camera
            };
        }
        try {
            console.log('Attempting dynamic import of cargo-visualizer');
            const cacheBuster = Math.random().toString(36).substr(2,
            9); // Generate random cache buster for true cache-busting
            const mod = await import('<?php echo e(asset('js/cargo-visualizer.js')); ?>?cb=' + cacheBuster);
            CargoVisualizerClass = mod.CargoVisualizer || window.CargoVisualizer || CargoVisualizerClass;
            console.log('cargo-visualizer imported', !!CargoVisualizerClass);
        } catch (e) {
            console.error('Failed to import cargo-visualizer module:', e);
            // leave CargoVisualizerClass as whatever is on window (fallback)
        }

        window.visualizeCargoPlacement = async function(voyageId) {
            // Show visualization container
            document.getElementById('visualizationContainer').style.display = 'block';

            try {
                // Fetch packing data from API (include credentials so auth cookie is sent)
                // Add cache buster timestamp to force fresh data
                const cacheBuster = new Date().getTime();
                const response = await fetch(
                    `<?php echo e(route('staff.cargo.packing-data')); ?>?voyage_id=${voyageId}&_t=${cacheBuster}`, {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Cache-Control': 'no-cache, no-store, must-revalidate'
                        }
                    });

                console.log('packing-data fetch status', response.status, response.statusText, response.headers.get(
                    'content-type'));

                if (!response.ok) {
                    const text = await response.text();
                    console.error('Packing-data fetch failed:', text);
                    alert('Failed to fetch packing data (check console).');
                    return;
                }

                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Unexpected packing-data response (not JSON):', text);
                    alert('Unexpected response when fetching packing data (check console).');
                    return;
                }

                const data = await response.json();
                console.log('packing-data response', data);

                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }

                // Dispose previous visualizer
                if (visualizer) {
                    visualizer.dispose();
                }

                // Create new visualizer (use dynamically imported class or window fallback)
                if (!CargoVisualizerClass) {
                    console.error('No CargoVisualizer available to instantiate — using inline fallback');
                    // Use inline fallback renderer to guarantee a visible scene
                    try {
                        inlineFallbackRender(data);
                    } catch (e) {
                        console.error('inlineFallbackRender failed', e);
                        alert('Visualizer failed to load (check console).');
                    }
                    return;
                }
                visualizer = new CargoVisualizerClass('cargoViewer');

                // First render raw boxes using measurements so user always sees something
                try {
                    visualizer.renderRaw(data.hatches, data.cargo);
                } catch (e) {
                    console.error('renderRaw failed', e);
                }

                // Then try packing and overlay results (non-blocking)
                let results = {
                    packed: [],
                    unpacked: []
                };
                try {
                    results = visualizer.packAndVisualize(data.hatches, data.cargo);
                } catch (e) {
                    console.error('packAndVisualize failed', e);
                }

                // Display statistics
                const statsHtml = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Total Items:</strong> ${data.cargo.length}</p>
                        <p><strong>Packed Items:</strong> <span class="text-success">${results.packed.length}</span></p>
                        <p><strong>Unpacked Items:</strong> <span class="text-danger">${results.unpacked.length}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Packing Efficiency:</strong> ${((results.packed.length / data.cargo.length) * 100).toFixed(1)}%</p>
                        <p><strong>Number of Hatches:</strong> ${data.hatches.length}</p>
                    </div>
                </div>
                ${results.unpacked.length > 0 ? `
                        <div class="alert alert-warning mt-3">
                            <strong>Items Not Packed:</strong>
                            <ul class="mb-0">
                                ${results.unpacked.map(item => `<li>Item #${item.id}: ${item.description}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
            `;
                document.getElementById('visualizationStats').innerHTML = statsHtml;

            } catch (error) {
                console.error('Error loading visualization:', error);
                alert('Failed to load visualization');
            }
        };

        // Drain any queued visualize requests that occurred before module loaded
        if (window.__visualizeQueue && window.__visualizeQueue.length) {
            const q = window.__visualizeQueue.splice(0);
            q.forEach(vId => {
                try {
                    window.visualizeCargoPlacement(vId);
                } catch (e) {
                    console.error('Queued visualize failed', e);
                }
            });
        }

        window.closeVisualization = function() {
            document.getElementById('visualizationContainer').style.display = 'none';
            if (visualizer) {
                visualizer.dispose();
                visualizer = null;
            }
        };

        window.toggleIsolateItem = function(buttonElement) {
            const itemId = buttonElement.getAttribute('data-item-id');
            const itemDescription = buttonElement.getAttribute('data-description');

            console.log('🔘 toggleIsolateItem button clicked');
            console.log('   itemId:', itemId);
            console.log('   itemDescription:', itemDescription);
            console.log('   visualizer:', !!visualizer);

            if (!visualizer) {
                alert('Visualization not loaded. Please select a voyage first.');
                console.error('❌ Visualizer not available');
                return;
            }

            // Call isolateItem which returns true if isolated, false if reset
            const isNowIsolated = visualizer.isolateItem(itemId);

            console.log('✅ toggleIsolateItem() called on visualizer, isIsolated:', isNowIsolated);

            // Update button styling
            if (isNowIsolated) {
                // Item is now isolated - make button red
                buttonElement.classList.remove('btn-info');
                buttonElement.classList.add('btn-danger');
                console.log('🔴 Button colored red (isolated)');

                // Reset all other buttons to blue
                document.querySelectorAll('.isolate-btn').forEach(btn => {
                    if (btn !== buttonElement) {
                        btn.classList.remove('btn-danger');
                        btn.classList.add('btn-info');
                    }
                });
            } else {
                // Item isolation was reset - revert button to blue
                buttonElement.classList.remove('btn-danger');
                buttonElement.classList.add('btn-info');
                console.log('🔵 Button coloring reverted (isolation reset)');
            }
        };
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Shem\Desktop\Capstone\resources\views/authorized/staff/staff_cargoautoplacement.blade.php ENDPATH**/ ?>