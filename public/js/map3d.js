fetch('/maps/PANGASINAN.geojson')
.then(res => res.json())
.then(data => {
    let allPoints = [];

    data.features.forEach(feature => {
        let name = feature.properties.name || "Pangasinan";
        let geometryType = feature.geometry.type;
        let coordinates = feature.geometry.coordinates;

        let polygons = geometryType === "MultiPolygon" ? coordinates : [coordinates];

        polygons.forEach(polygon => {
            polygon.forEach(ring => {
                let shape = new THREE.Shape();

                ring.forEach((point, i) => {
                    allPoints.push(point); // for centering

                    let x = point[0];
                    let y = point[1];

                    if (i === 0) shape.moveTo(x, y);
                    else shape.lineTo(x, y);
                });

                let geometry = new THREE.ExtrudeGeometry(shape, {
                    depth: 50,       // make extrusion visible
                    bevelEnabled: false
                });

                let material = new THREE.MeshStandardMaterial({
                    color: 0x0b5e2c
                });

                let mesh = new THREE.Mesh(geometry, material);
                mesh.userData.name = name;

                scene.add(mesh);
                objects.push(mesh);
            });
        });
    });

    // CENTERING
    let xs = allPoints.map(p => p[0]);
    let ys = allPoints.map(p => p[1]);

    let centerX = (Math.min(...xs) + Math.max(...xs)) / 2;
    let centerY = (Math.min(...ys) + Math.max(...ys)) / 2;

    objects.forEach(mesh => {
        mesh.position.x -= centerX;
        mesh.position.y -= centerY;

        // SCALE UP for visibility
        mesh.scale.set(1000, 1000, 50);
    });

    // CAMERA
    camera.position.set(0, -2000, 1000);
    camera.lookAt(0, 0, 0);
});
