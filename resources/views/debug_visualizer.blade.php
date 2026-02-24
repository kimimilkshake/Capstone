<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Debug Cargo Visualizer</title>
    <style>
        body,
        html {
            height: 100%;
            margin: 0
        }

        #cargoViewer {
            width: 100%;
            height: 100vh;
            background: #f8f8f8;
            position: relative
        }
    </style>
</head>

<body>
    <div id="cargoViewer"></div>

    <script type="module">
        import * as THREE from 'https://unpkg.com/three@0.128.0/build/three.module.js';
        window.THREE = THREE;

        const data = @json($data ?? []);
        console.log('debug_visualizer data', data);

        const container = document.getElementById('cargoViewer');
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
        container.appendChild(renderer.domElement);

        const ambient = new THREE.AmbientLight(0xffffff, 0.6);
        scene.add(ambient);
        const dir = new THREE.DirectionalLight(0xffffff, 0.8);
        dir.position.set(50, 50, 50);
        scene.add(dir);
        const grid = new THREE.GridHelper(200, 20);
        scene.add(grid);

        (data.hatches || []).forEach((h, i) => {
            const geom = new THREE.BoxGeometry(h.width, h.height, h.depth);
            const mat = new THREE.MeshStandardMaterial({
                color: 0x888888,
                transparent: true,
                opacity: 0.12
            });
            const mesh = new THREE.Mesh(geom, mat);
            mesh.position.set(h.width / 2 + (i * (h.width + 2)), h.height / 2, h.depth / 2);
            scene.add(mesh);
            const helper = new THREE.BoxHelper(mesh, 0x222222);
            scene.add(helper);
        });

        const firstH = (data.hatches && data.hatches[0]) || {
            width: 8,
            depth: 10,
            height: 2.5
        };
        const items = [];
        (data.cargo || []).forEach(c => {
            const qty = c.quantity || 1;
            for (let i = 0; i < qty; i++) items.push(c);
        });

        let cx = 0,
            cz = 0,
            gap = 0.05;
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

        function frame() {
            requestAnimationFrame(frame);
            renderer.render(scene, camera);
        }
        frame();
    </script>
</body>

</html>
