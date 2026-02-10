/**
 * Improved 3D Bin Packing Algorithm
 * Places items into bins sequentially with collision detection and spacing
 */
class SimpleBinPacker {
    constructor() {
        this.bins = [];
        this.gap = 0; // No gap - items pack tightly at corners
    }

    addBin(bin) {
        this.bins.push({
            id: bin.id,
            width: bin.width,
            height: bin.height,
            depth: bin.depth,
            maxWeight: bin.maxWeight || Infinity,
            items: [],
            usedVolume: 0,
            usedWeight: 0,
            // 4 corners at the actual container corners (absolute positions)
            // Items will be placed AT these corners and may extend into the bin
            corners: [
                { x: 0, y: 0, z: 0 }, // Front-Left corner (0, 0, 0)
                { x: bin.width, y: 0, z: 0 }, // Front-Right corner (8, 0, 0) - will be adjusted per item
                { x: 0, y: 0, z: bin.depth }, // Back-Left corner (0, 0, 10) - will be adjusted per item
                { x: bin.width, y: 0, z: bin.depth }, // Back-Right corner (8, 0, 10) - will be adjusted per item
            ],
        });
    }

    pack(items) {
        const packed = [];
        const unpacked = [];

        // Sort items by weight (heaviest first) for better weight distribution
        const sortedItems = [...items].sort(
            (a, b) => (b.weight || 0) - (a.weight || 0),
        );

        console.log(
            "🎯 SEQUENTIAL HATCH PACKING (100% FULL CAPACITY WITH WEIGHT DISTRIBUTION)",
        );
        console.log(`📦 PACKER STATE:`);
        console.log(`   Bins: ${this.bins.length}`);
        this.bins.forEach((bin, idx) => {
            const binVolume = bin.width * bin.height * bin.depth;
            console.log(
                `      Bin ${idx}: id=${bin.id}, size=${bin.width}×${bin.height}×${bin.depth}m, volume=${binVolume.toFixed(2)}m³, maxWeight=${bin.maxWeight}kg`,
            );
        });
        console.log(`   Items to pack: ${sortedItems.length}`);
        let totalItemVolume = 0;
        sortedItems.forEach((item) => {
            const itemVolume = item.width * item.height * item.depth;
            totalItemVolume += itemVolume;
            const fragility = item.is_breakable ? "🔴 BREAKABLE" : "🟢 ROBUST";
            console.log(
                `      ${fragility} ${item.description} (${item.weight}kg): ${item.width}×${item.height}×${item.depth}m, vol=${itemVolume.toFixed(3)}m³`,
            );
        });
        console.log(`   Total item volume: ${totalItemVolume.toFixed(2)}m³`);
        console.log("");

        for (const item of sortedItems) {
            let placed = false;

            // Find available hatches: fill sequentially to FULL capacity
            // Pack sequentially into hatches (fill one completely before using next)
            const availableBins = this.bins
                .filter((bin) => {
                    const percentUsed = (bin.usedWeight / bin.maxWeight) * 100;
                    const canFit = this.canFit(item, bin);

                    return canFit; // No threshold - fill to 100%
                })
                .sort((a, b) => {
                    // SEQUENTIAL FILL: Prefer hatch with MOST weight already (fill up one hatch first)
                    // Tie-breaker: if same weight, prefer lower ID (prefer Hatch 1 over Hatch 2)
                    const weightDiff = b.usedWeight - a.usedWeight;
                    return weightDiff !== 0 ? weightDiff : a.id - b.id;
                });

            // Debug: Check which bins passed the canFit filter
            const allBinsStatus = this.bins.map((bin) => {
                const volumeOk =
                    this.getVolume(item) + bin.usedVolume <=
                    this.getVolume(bin);
                const weightOk =
                    (item.weight || 0) + bin.usedWeight <= bin.maxWeight;
                const usedVol = bin.usedVolume.toFixed(2);
                const maxVol = this.getVolume(bin).toFixed(2);
                const volPercent = (
                    (bin.usedVolume / this.getVolume(bin)) *
                    100
                ).toFixed(1);
                const weightPercent = (
                    (bin.usedWeight / bin.maxWeight) *
                    100
                ).toFixed(1);
                return {
                    id: bin.id,
                    volumeOk,
                    weightOk,
                    usedVol,
                    maxVol,
                    volPercent,
                    weightPercent,
                };
            });

            console.log(
                `  📊 "${item.description}" (${item.width}×${item.height}×${item.depth}m, ${item.weight}kg):`,
            );
            allBinsStatus.forEach((status) => {
                const volStatus = status.volumeOk ? "✓ VOL" : "✗ VOL";
                const wgtStatus = status.weightOk ? "✓ WGT" : "✗ WGT";
                console.log(
                    `     ${status.id}: ${volStatus} (${status.volPercent}% used: ${status.usedVol}/${status.maxVol}m³) ${wgtStatus} (${status.weightPercent}% used)`,
                );
            });

            for (const bin of availableBins) {
                // Auto-rotate tree-like items to lay flat (horizontal placement)
                const rotatedItem = this.autoRotateToFlatten(item);

                const position = this.findPosition(rotatedItem, bin);
                if (position) {
                    this.placeItem(rotatedItem, bin, position);
                    packed.push({
                        ...rotatedItem,
                        binId: bin.id,
                        x: position.x,
                        y: position.y,
                        z: position.z,
                    });
                    const percentUsed = (
                        (bin.usedWeight / bin.maxWeight) *
                        100
                    ).toFixed(1);
                    const strategy = rotatedItem.is_breakable
                        ? "📚 STACKED"
                        : "📦 SPREAD";
                    console.log(
                        `  ✓ "${rotatedItem.description}" (${rotatedItem.weight}kg) → ${bin.id} (${percentUsed}% full) ${strategy}`,
                    );
                    placed = true;
                    break;
                }
            }

            if (!placed) {
                unpacked.push(item);
                console.log(
                    `  ✗ "${item.description}" (${item.weight}kg) → UNPACKED (no valid placement found)`,
                );
            }
        }

        console.log(
            "\n🎯 HATCH UTILIZATION SUMMARY (SEQUENTIAL FILL TO 100%):",
        );
        this.bins.forEach((bin) => {
            const percentUsed = (
                (bin.usedWeight / bin.maxWeight) *
                100
            ).toFixed(1);
            console.log(
                `  ${bin.id}: ${bin.usedWeight.toFixed(0)}kg / ${bin.maxWeight.toFixed(0)}kg (${percentUsed}%) - Items: ${bin.items.length}`,
            );

            // Calculate weight distribution per 4-zone in this bin
            const zones = {
                frontLeft: { weight: 0, count: 0 },
                frontRight: { weight: 0, count: 0 },
                backLeft: { weight: 0, count: 0 },
                backRight: { weight: 0, count: 0 },
            };

            bin.items.forEach((item) => {
                const centerX = item.x + item.width / 2;
                const centerZ = item.z + item.depth / 2;
                const weight = item.weight || 0;

                let zone = null;
                if (centerX < bin.width / 2 && centerZ < bin.depth / 2)
                    zone = zones.frontLeft;
                else if (centerX >= bin.width / 2 && centerZ < bin.depth / 2)
                    zone = zones.frontRight;
                else if (centerX < bin.width / 2 && centerZ >= bin.depth / 2)
                    zone = zones.backLeft;
                else zone = zones.backRight;

                if (zone) {
                    zone.weight += weight;
                    zone.count++;
                }
            });

            console.log(
                `     🔵 Front-Left: ${zones.frontLeft.weight}kg (${zones.frontLeft.count} items)`,
            );
            console.log(
                `     🔴 Front-Right: ${zones.frontRight.weight}kg (${zones.frontRight.count} items)`,
            );
            console.log(
                `     ⚫ Back-Left: ${zones.backLeft.weight}kg (${zones.backLeft.count} items)`,
            );
            console.log(
                `     🟡 Back-Right: ${zones.backRight.weight}kg (${zones.backRight.count} items)`,
            );
        });
        console.log(
            "🎯 PACKING COMPLETE (4-zone distribution across all corners)\n",
        );

        return { packed, unpacked };
    }

    canFit(item, bin) {
        const volumeOk =
            this.getVolume(item) + bin.usedVolume <= this.getVolume(bin);
        const weightOk = (item.weight || 0) + bin.usedWeight <= bin.maxWeight;
        return volumeOk && weightOk;
    }

    /**
     * Auto-rotate tree-like items to lay flat (horizontal placement)
     * If height >> other dimensions, rotate so height becomes width or depth
     * This makes tall/long items lay down instead of standing up
     */
    autoRotateToFlatten(item) {
        // Don't rotate breakable items - they should stack for safety
        if (item.is_breakable) {
            return item; // Return unchanged
        }

        const dims = [item.width, item.height, item.depth];
        const maxDim = Math.max(...dims);
        const minDim = Math.min(...dims);
        const aspectRatio = maxDim / minDim;

        // Only rotate if it's significantly "tree-like" (aspect ratio > 1.3)
        // AND height is the problematic dimension
        if (aspectRatio > 1.3 && item.height === maxDim) {
            // Rotate so height becomes depth (preferred for hatch length)
            const rotated = {
                ...item,
                width: item.width, // Keep width same
                height: item.depth, // New height = old depth (smallest)
                depth: item.height, // New depth = old height (largest, now horizontal)
                rotated: true,
                originalDims: `${item.width}×${item.height}×${item.depth}`,
                rotatedDims: `${item.width}×${item.depth}×${item.height}`,
            };
            console.log(
                `      🔄 ROTATING: "${item.description}" (${item.width}×${item.height}×${item.depth}) → (${rotated.width}×${rotated.height}×${rotated.depth}) to lay flat`,
            );
            return rotated;
        }

        return item; // No rotation needed
    }

    // Check if item at position collides with any existing item in the bin
    collidesWith(item, position, bin) {
        const itemBox = {
            x1: position.x,
            x2: position.x + item.width,
            y1: position.y,
            y2: position.y + item.height,
            z1: position.z,
            z2: position.z + item.depth,
        };

        for (const existing of bin.items) {
            const existBox = {
                x1: existing.x,
                x2: existing.x + existing.width,
                y1: existing.y,
                y2: existing.y + existing.height,
                z1: existing.z,
                z2: existing.z + existing.depth,
            };

            // Check if boxes overlap (strict, no gap in collision check)
            if (
                !(
                    itemBox.x2 <= existBox.x1 ||
                    itemBox.x1 >= existBox.x2 ||
                    itemBox.y2 <= existBox.y1 ||
                    itemBox.y1 >= existBox.y2 ||
                    itemBox.z2 <= existBox.z1 ||
                    itemBox.z1 >= existBox.z2
                )
            ) {
                return true; // collision detected
            }
        }
        return false; // no collision
    }

    findPosition(item, bin) {
        const gap = this.gap;

        // 4-ZONE WEIGHT DISTRIBUTION: Divide hatch into 4 corners
        // Zones: Front-Left (x<w/2, z<d/2), Front-Right (x>=w/2, z<d/2)
        //        Back-Left (x<w/2, z>=d/2), Back-Right (x>=w/2, z>=d/2)
        const zones = {
            frontLeft: {
                weight: 0,
                count: 0,
                minX: 0,
                maxX: bin.width / 2,
                minZ: 0,
                maxZ: bin.depth / 2,
            },
            frontRight: {
                weight: 0,
                count: 0,
                minX: bin.width / 2,
                maxX: bin.width,
                minZ: 0,
                maxZ: bin.depth / 2,
            },
            backLeft: {
                weight: 0,
                count: 0,
                minX: 0,
                maxX: bin.width / 2,
                minZ: bin.depth / 2,
                maxZ: bin.depth,
            },
            backRight: {
                weight: 0,
                count: 0,
                minX: bin.width / 2,
                maxX: bin.width,
                minZ: bin.depth / 2,
                maxZ: bin.depth,
            },
        };

        // Classify existing items into zones
        bin.items.forEach((existing) => {
            const centerX = existing.x + existing.width / 2;
            const centerZ = existing.z + existing.depth / 2;
            const weight = existing.weight || 0;

            let zone = null;
            if (centerX < bin.width / 2 && centerZ < bin.depth / 2)
                zone = zones.frontLeft;
            else if (centerX >= bin.width / 2 && centerZ < bin.depth / 2)
                zone = zones.frontRight;
            else if (centerX < bin.width / 2 && centerZ >= bin.depth / 2)
                zone = zones.backLeft;
            else zone = zones.backRight;

            if (zone) {
                zone.weight += weight;
                zone.count++;
            }
        });

        // Find zone with least weight (prefer balancing)
        // PRIORITY: Right zones (FR, BR) first if empty, then left zones by weight
        let preferredZone = zones.frontLeft;
        let minWeight = Infinity;

        // Score zones: empty right zones get highest priority (lowest score)
        const zoneScores = {
            frontRight:
                zones.frontRight.weight === 0 ? -1000 : zones.frontRight.weight,
            backRight:
                zones.backRight.weight === 0 ? -500 : zones.backRight.weight,
            frontLeft: zones.frontLeft.weight,
            backLeft: zones.backLeft.weight,
        };

        // Find zone with lowest score (empty right zones first)
        for (const [zoneName, score] of Object.entries(zoneScores)) {
            if (score < minWeight) {
                minWeight = score;
                preferredZone = zones[zoneName];
            }
        }

        const zoneName = Object.keys(zones).find(
            (k) => zones[k] === preferredZone,
        );

        // Map each zone to its designated corner
        // NOTE: Corners are at the actual bin edges (0, width, depth)
        // Items will be adjusted to fit within bounds when placed
        const zoneCornerInfo = {
            frontLeft: {
                baseX: 0,
                baseZ: 0,
                alignRight: false,
                alignBack: false,
            },
            frontRight: {
                baseX: bin.width,
                baseZ: 0,
                alignRight: true,
                alignBack: false,
            },
            backLeft: {
                baseX: 0,
                baseZ: bin.depth,
                alignRight: false,
                alignBack: true,
            },
            backRight: {
                baseX: bin.width,
                baseZ: bin.depth,
                alignRight: true,
                alignBack: true,
            },
        };
        const zoneCorner = zoneCornerInfo[zoneName];

        console.log(
            `    4-Zone balance: FL=${zones.frontLeft.weight}kg, FR=${zones.frontRight.weight}kg, BL=${zones.backLeft.weight}kg, BR=${zones.backRight.weight}kg → Placing ${item.weight}kg in ${zoneName} at bin corner (${zoneCorner.baseX}, 0, ${zoneCorner.baseZ})`,
        );

        // Filter corners strictly to preferred zone only
        let validCorners = bin.corners
            .filter((corner) => {
                // Calculate item position based on zone alignment
                // Right-aligned zones: item ENDS at bin edge, so x = bin.width - item.width
                // Back-aligned zones: item ENDS at bin edge, so z = bin.depth - item.depth
                let itemX = zoneCorner.alignRight
                    ? Math.max(0, bin.width - item.width)
                    : corner.x;
                let itemZ = zoneCorner.alignBack
                    ? Math.max(0, bin.depth - item.depth)
                    : corner.z;
                let itemY = corner.y;

                // Check boundaries - items must fit completely within bin
                const fitsX = itemX >= 0 && itemX + item.width <= bin.width;
                const fitsY = itemY >= 0 && itemY + item.height <= bin.height;
                const fitsZ = itemZ >= 0 && itemZ + item.depth <= bin.depth;

                if (!fitsX || !fitsY || !fitsZ) return false;

                // STRICT zone check: item center must be in preferred zone
                const itemCenterX = itemX + item.width / 2;
                const itemCenterZ = itemZ + item.depth / 2;

                const inZone =
                    itemCenterX >= preferredZone.minX &&
                    itemCenterX < preferredZone.maxX &&
                    itemCenterZ >= preferredZone.minZ &&
                    itemCenterZ < preferredZone.maxZ;

                if (!inZone) return false;

                // Check for collisions with existing items
                const noCollision = !this.collidesWith(
                    item,
                    { x: itemX, y: itemY, z: itemZ },
                    bin,
                );
                if (!noCollision) return false;

                // Return the adjusted position as part of the corner object
                return true;
            })
            // CALCULATE STACK HEIGHT: For each valid position, calculate the Y height where this item should sit
            .map((corner) => {
                let posX = zoneCorner.alignRight
                    ? Math.max(0, bin.width - item.width)
                    : corner.x;
                let posZ = zoneCorner.alignBack
                    ? Math.max(0, bin.depth - item.depth)
                    : corner.z;

                // Calculate stack height at this X-Z position
                // Find the maximum Y of any existing item that overlaps with this item's XZ footprint
                let stackHeight = 0;
                let supportedOnAllSides = false;

                for (const existing of bin.items) {
                    // Check if existing item overlaps with the proposed position in X-Z plane
                    const xOverlap = !(
                        posX + item.width <= existing.x ||
                        posX >= existing.x + existing.width
                    );
                    const zOverlap = !(
                        posZ + item.depth <= existing.z ||
                        posZ >= existing.z + existing.depth
                    );

                    if (xOverlap && zOverlap) {
                        // This existing item occupies space directly below/above, so stack on top
                        const topOfExisting = existing.y + existing.height;
                        stackHeight = Math.max(stackHeight, topOfExisting);
                    }
                }

                // Check if item is fully supported on all sides at stackHeight
                // For an item at stackHeight to be safe, check if all 4 edges have support
                if (stackHeight > 0) {
                    let leftSupported = false,
                        rightSupported = false,
                        frontSupported = false,
                        backSupported = false;

                    for (const existing of bin.items) {
                        // Check if existing item supports the left edge
                        if (
                            Math.abs(posX - (existing.x + existing.width)) <
                                0.05 &&
                            !(
                                posZ + item.depth <= existing.z ||
                                posZ >= existing.z + existing.depth
                            )
                        ) {
                            leftSupported = true;
                        }
                        // Check if existing item supports the right edge
                        if (
                            Math.abs(posX + item.width - existing.x) < 0.05 &&
                            !(
                                posZ + item.depth <= existing.z ||
                                posZ >= existing.z + existing.depth
                            )
                        ) {
                            rightSupported = true;
                        }
                        // Check if existing item supports the front edge
                        if (
                            Math.abs(posZ - (existing.z + existing.depth)) <
                                0.05 &&
                            !(
                                posX + item.width <= existing.x ||
                                posX >= existing.x + existing.width
                            )
                        ) {
                            frontSupported = true;
                        }
                        // Check if existing item supports the back edge
                        if (
                            Math.abs(posZ + item.depth - existing.z) < 0.05 &&
                            !(
                                posX + item.width <= existing.x ||
                                posX >= existing.x + existing.width
                            )
                        ) {
                            backSupported = true;
                        }
                    }

                    // Item is only safely stacked if it has support on at least 2 sides or is fully enclosed
                    supportedOnAllSides =
                        (leftSupported || posX <= 0.05) &&
                        (rightSupported ||
                            posX + item.width >= bin.width - 0.05) &&
                        (frontSupported || posZ <= 0.05) &&
                        (backSupported ||
                            posZ + item.depth >= bin.depth - 0.05);
                }

                // If stacked but not supported on all sides, don't allow this position
                if (stackHeight > 0 && !supportedOnAllSides) {
                    return null;
                }

                // Ensure stacked item doesn't exceed bin height
                if (stackHeight + item.height > bin.height) {
                    return null; // Can't stack this high
                }

                return { ...corner, x: posX, y: stackHeight, z: posZ };
            })
            .filter((c) => c !== null) // Remove positions that would exceed height
            .sort((a, b) => {
                // STACK OPTIMIZATION: Prefer positions with existing stacks (use space efficiently)
                // Secondary sort: distance to zone corner
                const aHasStack = a.y > 0 ? 1 : 0;
                const bHasStack = b.y > 0 ? 1 : 0;

                if (aHasStack !== bHasStack) {
                    return bHasStack - aHasStack; // Prefer stacking (higher Y first)
                }

                // Tie-breaker: Distance from corner to the zone's starting corner
                const distA =
                    Math.abs(a.x - zoneCorner.baseX) +
                    Math.abs(a.z - zoneCorner.baseZ);
                const distB =
                    Math.abs(b.x - zoneCorner.baseX) +
                    Math.abs(b.z - zoneCorner.baseZ);

                return distA - distB; // Closer to zone corner = higher priority
            });

        // If preferred zone has no spots, try other zones in order of weight (least weight first)
        if (validCorners.length === 0) {
            // Get zones sorted by weight
            const zonesByWeight = Object.entries(zones)
                .sort((a, b) => a[1].weight - b[1].weight)
                .map(([name]) => zones[name]);

            // Try each zone in order
            for (const zone of zonesByWeight) {
                const fallbackZoneInfo =
                    Object.entries(zoneCornerInfo).find(
                        ([name]) => zones[name] === zone,
                    )?.[1] || zoneCornerInfo.frontLeft;

                validCorners = bin.corners
                    .filter((corner) => {
                        let itemX = fallbackZoneInfo.alignRight
                            ? Math.max(0, bin.width - item.width)
                            : corner.x;
                        let itemZ = fallbackZoneInfo.alignBack
                            ? Math.max(0, bin.depth - item.depth)
                            : corner.z;
                        let itemY = corner.y;

                        const fitsX =
                            itemX >= 0 && itemX + item.width <= bin.width;
                        const fitsY =
                            itemY >= 0 && itemY + item.height <= bin.height;
                        const fitsZ =
                            itemZ >= 0 && itemZ + item.depth <= bin.depth;

                        if (!fitsX || !fitsY || !fitsZ) return false;

                        const itemCenterX = itemX + item.width / 2;
                        const itemCenterZ = itemZ + item.depth / 2;

                        const inZone =
                            itemCenterX >= zone.minX &&
                            itemCenterX < zone.maxX &&
                            itemCenterZ >= zone.minZ &&
                            itemCenterZ < zone.maxZ;

                        if (!inZone) return false;
                        return !this.collidesWith(
                            item,
                            { x: itemX, y: itemY, z: itemZ },
                            bin,
                        );
                    })
                    .map((corner) => {
                        let posX = fallbackZoneInfo.alignRight
                            ? Math.max(0, bin.width - item.width)
                            : corner.x;
                        let posZ = fallbackZoneInfo.alignBack
                            ? Math.max(0, bin.depth - item.depth)
                            : corner.z;

                        // Calculate stack height at this X-Z position
                        let stackHeight = 0;
                        let supportedOnAllSides = false;

                        for (const existing of bin.items) {
                            const xOverlap = !(
                                posX + item.width <= existing.x ||
                                posX >= existing.x + existing.width
                            );
                            const zOverlap = !(
                                posZ + item.depth <= existing.z ||
                                posZ >= existing.z + existing.depth
                            );

                            if (xOverlap && zOverlap) {
                                const topOfExisting =
                                    existing.y + existing.height;
                                stackHeight = Math.max(
                                    stackHeight,
                                    topOfExisting,
                                );
                            }
                        }

                        // Check if item is fully supported on all sides at stackHeight
                        if (stackHeight > 0) {
                            let leftSupported = false,
                                rightSupported = false,
                                frontSupported = false,
                                backSupported = false;

                            for (const existing of bin.items) {
                                // Check if existing item supports the left edge
                                if (
                                    Math.abs(
                                        posX - (existing.x + existing.width),
                                    ) < 0.05 &&
                                    !(
                                        posZ + item.depth <= existing.z ||
                                        posZ >= existing.z + existing.depth
                                    )
                                ) {
                                    leftSupported = true;
                                }
                                // Check if existing item supports the right edge
                                if (
                                    Math.abs(posX + item.width - existing.x) <
                                        0.05 &&
                                    !(
                                        posZ + item.depth <= existing.z ||
                                        posZ >= existing.z + existing.depth
                                    )
                                ) {
                                    rightSupported = true;
                                }
                                // Check if existing item supports the front edge
                                if (
                                    Math.abs(
                                        posZ - (existing.z + existing.depth),
                                    ) < 0.05 &&
                                    !(
                                        posX + item.width <= existing.x ||
                                        posX >= existing.x + existing.width
                                    )
                                ) {
                                    frontSupported = true;
                                }
                                // Check if existing item supports the back edge
                                if (
                                    Math.abs(posZ + item.depth - existing.z) <
                                        0.05 &&
                                    !(
                                        posX + item.width <= existing.x ||
                                        posX >= existing.x + existing.width
                                    )
                                ) {
                                    backSupported = true;
                                }
                            }

                            supportedOnAllSides =
                                (leftSupported || posX <= 0.05) &&
                                (rightSupported ||
                                    posX + item.width >= bin.width - 0.05) &&
                                (frontSupported || posZ <= 0.05) &&
                                (backSupported ||
                                    posZ + item.depth >= bin.depth - 0.05);
                        }

                        if (stackHeight > 0 && !supportedOnAllSides) {
                            return null;
                        }

                        if (stackHeight + item.height > bin.height) {
                            return null;
                        }

                        return { ...corner, x: posX, y: stackHeight, z: posZ };
                    })
                    .filter((c) => c !== null)
                    .sort((a, b) => {
                        // Prefer existing stacks
                        const aHasStack = a.y > 0 ? 1 : 0;
                        const bHasStack = b.y > 0 ? 1 : 0;

                        if (aHasStack !== bHasStack) {
                            return bHasStack - aHasStack;
                        }

                        return a.x + a.y + a.z - (b.x + b.y + b.z);
                    });

                if (validCorners.length > 0) break; // Found a zone with space
            }
        }

        if (validCorners.length > 0) {
            const chosen = validCorners[0];

            // STRICT VALIDATION: Verify chosen position actually fits
            if (
                chosen.x + item.width > bin.width ||
                chosen.y + item.height > bin.height ||
                chosen.z + item.depth > bin.depth
            ) {
                console.error(
                    `🚨 VALIDATION FAILED: Position violates bounds!`,
                );
                console.error(
                    `   Position: (${chosen.x}, ${chosen.y}, ${chosen.z})`,
                );
                console.error(
                    `   Item size: ${item.width}×${item.height}×${item.depth}`,
                );
                console.error(
                    `   Bin size: ${bin.width}×${bin.height}×${bin.depth}`,
                );
                return null;
            }

            const itemCenterX = chosen.x + item.width / 2;
            const itemCenterZ = chosen.z + item.depth / 2;
            let placedZone = "unknown";
            if (itemCenterX < bin.width / 2 && itemCenterZ < bin.depth / 2)
                placedZone = "frontLeft";
            else if (
                itemCenterX >= bin.width / 2 &&
                itemCenterZ < bin.depth / 2
            )
                placedZone = "frontRight";
            else if (
                itemCenterX < bin.width / 2 &&
                itemCenterZ >= bin.depth / 2
            )
                placedZone = "backLeft";
            else placedZone = "backRight";

            console.log(
                `    >>> findPosition: ${item.width}×${item.height}×${item.depth} → (${chosen.x.toFixed(2)}, ${chosen.y.toFixed(2)}, ${chosen.z.toFixed(2)}) in ${placedZone} ${chosen.y > 0 ? "📚 STACKED" : "🟢 FLOOR"} ✓ VALID`,
            );
            return chosen;
        }

        // FALLBACK: Try greedy bottom-left placement as last resort
        // This is more flexible than zone-based packing
        console.log(
            `    >>> Zone-based placement failed. Trying greedy bottom-left fallback...`,
        );

        // Build a 2D ground profile to find stacking opportunities
        // Sample stack heights at different X-Z coordinates
        const samplePoints = [];
        const step = Math.max(item.width, item.depth) * 0.5; // Use item size as step size

        for (let x = 0; x + item.width <= bin.width; x += step) {
            for (let z = 0; z + item.depth <= bin.depth; z += step) {
                // Find the maximum Y (stack height) at this location
                let maxY = 0;
                for (const existing of bin.items) {
                    // Check if item would overlap in X-Z plane at this position
                    const testBox = {
                        x1: x,
                        x2: x + item.width,
                        z1: z,
                        z2: z + item.depth,
                    };
                    const existBox = {
                        x1: existing.x,
                        x2: existing.x + existing.width,
                        z1: existing.z,
                        z2: existing.z + existing.depth,
                    };

                    // Check XZ overlap
                    if (
                        !(
                            testBox.x2 <= existBox.x1 ||
                            testBox.x1 >= existBox.x2 ||
                            testBox.z2 <= existBox.z1 ||
                            testBox.z1 >= existBox.z2
                        )
                    ) {
                        // Check if the item is properly supported (footprint fits within support item)
                        const tolerance = 0.02;
                        const fullySupported =
                            x >= existing.x - tolerance &&
                            x + item.width <=
                                existing.x + existing.width + tolerance &&
                            z >= existing.z - tolerance &&
                            z + item.depth <=
                                existing.z + existing.depth + tolerance;

                        if (fullySupported) {
                            // Overlaps in XZ plane and is properly supported, so we can stack on top
                            const topOfExisting = existing.y + existing.height;
                            maxY = Math.max(maxY, topOfExisting);
                        }
                        // If not fully supported, don't stack on this item
                    }
                }

                // Try placing item at this height
                if (maxY + item.height <= bin.height) {
                    const testPos = { x, y: maxY, z };
                    if (!this.collidesWith(item, testPos, bin)) {
                        console.log(
                            `    >>> Greedy fallback: Found position at (${x.toFixed(2)}, ${maxY.toFixed(2)}, ${z.toFixed(2)}) ✓`,
                        );
                        return testPos;
                    }
                }

                samplePoints.push({ x, z, maxY });
            }
        }

        console.log(
            `    >>> findPosition: NO VALID POSITION for ${item.width}×${item.height}×${item.depth}`,
        );
        console.log(
            `        Preferred zone had ${validCorners.length} valid corners before fallback`,
        );
        console.log(
            `        Current bin items: ${bin.items.length}, volume used: ${((bin.usedVolume / this.getVolume(bin)) * 100).toFixed(1)}%`,
        );
        console.log(`        Tested ${samplePoints.length} fallback positions`);
        return null;
    }

    placeItem(item, bin, position) {
        const gap = this.gap;

        // SAFETY CHECK: Ensure item actually fits within bin bounds
        if (
            position.x + item.width > bin.width ||
            position.y + item.height > bin.height ||
            position.z + item.depth > bin.depth
        ) {
            console.error(`🚨 SAFETY VIOLATION: Item placed outside bin!`);
            console.error(
                `   Item: (${position.x}, ${position.y}, ${position.z}) + (${item.width}, ${item.height}, ${item.depth})`,
            );
            console.error(
                `   Bin: (${bin.width}, ${bin.height}, ${bin.depth})`,
            );
            throw new Error(
                "Item placed outside bin bounds - packer algorithm error!",
            );
        }

        bin.items.push({
            ...item,
            x: position.x,
            y: position.y,
            z: position.z,
        });
        bin.usedVolume += this.getVolume(item);
        bin.usedWeight += item.weight || 0;

        // Intelligent corner generation based on item shape and available space
        const newCorners = [];

        // Detect "tree-like" items: one dimension >> other dimensions
        const dims = [item.width, item.height, item.depth];
        const maxDim = Math.max(...dims);
        const minDim = Math.min(...dims);
        const isTreeLike = maxDim > 1.3 * minDim;

        // Calculate horizontal floor space utilization
        const floorArea = bin.width * bin.depth;
        let usedFloorArea = 0;
        for (const existing of bin.items) {
            usedFloorArea += (existing.width || 0) * (existing.depth || 0);
        }
        const floorUtilization = usedFloorArea / floorArea;
        const floorSpaceRemaining = (floorArea - usedFloorArea) / floorArea;

        // STRATEGY SELECTION:
        // 1. Breakable items → ALWAYS stack vertically (safety first)
        // 2. Tree-like robust items → Prefer horizontal if floor space available
        // 3. Compact robust items → Spread horizontally
        // 4. Running out of floor space → Switch to vertical stacking

        let useVerticalStacking = false;
        let strategy = "unknown";

        if (item.is_breakable) {
            // Breakable items ALWAYS stack vertically (safety priority)
            useVerticalStacking = true;
            strategy = "🔴 FRAGILE → STACK (safety)";
        } else if (isTreeLike) {
            // Robust tree-like item (tall or long)
            if (floorSpaceRemaining < 0.2) {
                // Floor is >80% full, switch to vertical stacking
                useVerticalStacking = true;
                strategy = "🌳 TREE → STACK (floor 80%+ full)";
            } else {
                // Plenty of floor space, lay it flat
                useVerticalStacking = false;
                strategy = "🌳 TREE → SPREAD (floor has space)";
            }
        } else {
            // Compact robust item → spread horizontally
            useVerticalStacking = false;
            strategy = "🟢 ROBUST → SPREAD";
        }

        console.log(
            `      Placement strategy: ${strategy} (floor ${(floorUtilization * 100).toFixed(0)}% used, item ${maxDim.toFixed(2)}m max)`,
        );

        if (useVerticalStacking) {
            // VERTICAL STACKING: Only allow stacking on top (Y axis)
            newCorners.push(
                // Upper corners (for stacking)
                {
                    x: position.x,
                    y: position.y + item.height + gap,
                    z: position.z,
                },
                {
                    x: position.x + item.width + gap,
                    y: position.y + item.height + gap,
                    z: position.z,
                },
                {
                    x: position.x,
                    y: position.y + item.height + gap,
                    z: position.z + item.depth + gap,
                },
                {
                    x: position.x + item.width + gap,
                    y: position.y + item.height + gap,
                    z: position.z + item.depth + gap,
                },
            );
        } else {
            // HORIZONTAL SPREADING: Spread along X and Z axes
            newCorners.push(
                // Right-side corners (X axis spreading)
                {
                    x: position.x + item.width + gap,
                    y: position.y,
                    z: position.z,
                },
                {
                    x: position.x + item.width + gap,
                    y: position.y,
                    z: position.z + item.depth + gap,
                },
                // Depth corners (Z axis spreading)
                {
                    x: position.x,
                    y: position.y,
                    z: position.z + item.depth + gap,
                },
                {
                    x: position.x + item.width + gap,
                    y: position.y,
                    z: position.z + item.depth + gap,
                },
            );
        }

        // Filter out corners that exceed bin bounds
        const validNewCorners = newCorners.filter(
            (corner) =>
                corner.x <= bin.width &&
                corner.y <= bin.height &&
                corner.z <= bin.depth,
        );

        bin.corners.push(...validNewCorners);

        // Remove duplicate corners
        bin.corners = this.removeDuplicateCorners(bin.corners);
    }

    removeDuplicateCorners(corners) {
        return corners.filter(
            (corner, index, self) =>
                index ===
                self.findIndex(
                    (c) =>
                        c.x === corner.x &&
                        c.y === corner.y &&
                        c.z === corner.z,
                ),
        );
    }

    getVolume(item) {
        return (item.width || 0) * (item.height || 0) * (item.depth || 0);
    }
}

/**
 * 3D Cargo Visualizer
 * Renders packed cargo in a 3D scene
 */
export class CargoVisualizer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        console.log(
            "CargoVisualizer: constructor containerId=",
            containerId,
            "element=",
            this.container,
        );
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
        this.packer = new SimpleBinPacker();
        this.hatchMeshes = [];
        this.hatchLabels = []; // Track hatch labels for billboard effect
        this.controlsTarget = null; // Will be set in setupControls
        this.isolatedItemId = null; // Track which item is currently isolated
        this.expectedWidth = 0; // Store expected width to prevent scroll-induced resizing
        this.expectedHeight = 0; // Store expected height to prevent scroll-induced resizing
        this.isVisible = true; // Track if canvas is visible in viewport
        this.cargoGap = 0.05; // 5cm gap on each side of cargo items for visual spacing

        this.initScene();
    }

    initScene() {
        console.log("CargoVisualizer: initScene start");
        // Scene
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(0xf0f0f0);
        // root group to help compute bounding boxes and move everything together
        this.sceneRoot = new THREE.Group();
        this.scene.add(this.sceneRoot);

        // Camera
        const width = this.container ? this.container.clientWidth : 800;
        const height = this.container ? this.container.clientHeight : 600;
        console.log("CargoVisualizer: container size", { width, height });
        // fallback if container is not laid out yet
        const safeWidth = Math.max(1, width || 800);
        const safeHeight = Math.max(1, height || 600);

        // Store these as the expected dimensions to use throughout the lifetime
        // This prevents scroll-induced browser reflow from changing canvas size
        this.expectedWidth = safeWidth;
        this.expectedHeight = safeHeight;
        this.camera = new THREE.PerspectiveCamera(
            60,
            safeWidth / safeHeight,
            0.1,
            10000,
        );
        this.camera.position.set(50, 40, 50);
        this.camera.lookAt(0, 0, 0);

        // Renderer
        this.renderer = new THREE.WebGLRenderer({
            antialias: true,
            preserveDrawingBuffer: false,
        });
        this.renderer.sortObjects = true; // Enable renderOrder sorting

        // Set proper pixel ratio for sharp rendering
        const pixelRatio = window.devicePixelRatio || 1;
        this.renderer.setPixelRatio(pixelRatio);
        this.renderer.setSize(safeWidth, safeHeight, false); // false = don't update canvas style

        // Manually control canvas styles to prevent conflicts
        this.renderer.domElement.style.display = "block";
        this.renderer.domElement.style.width = "100%";
        this.renderer.domElement.style.height = "100%";
        this.renderer.domElement.style.margin = "0";
        this.renderer.domElement.style.padding = "0";
        this.renderer.domElement.style.border = "none";

        this.renderer.shadowMap.enabled = true;
        if (this.container) {
            // Ensure the container properly clips the canvas
            // Note: DO NOT override height/width that's set in HTML
            // Just ensure proper clipping is enabled
            if (this.container.style.overflow !== "hidden") {
                this.container.style.overflow = "hidden";
            }
            this.container.appendChild(this.renderer.domElement);
            console.log("CargoVisualizer: appended renderer.domElement");
        } else {
            console.error(
                "CargoVisualizer: container missing, cannot append renderer",
            );
        }

        // Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(100, 100, 100);
        directionalLight.castShadow = true;
        directionalLight.shadow.mapSize.width = 2048;
        directionalLight.shadow.mapSize.height = 2048;
        this.scene.add(directionalLight);

        // Grid (hidden to show container clearly)
        // const gridHelper = new THREE.GridHelper(200, 20);
        // this.scene.add(gridHelper);

        // Basic orbit controls - simple version
        this.setupControls();

        // Start animation loop
        this.animate();

        // Add Intersection Observer to handle canvas visibility and pause rendering when clipped
        if ("IntersectionObserver" in window && this.container) {
            const intersectionObserver = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        // Only render when canvas is significantly visible (more than 25% in viewport)
                        this.isVisible = entry.intersectionRatio > 0.25;
                    });
                },
                { threshold: [0, 0.25, 0.5, 0.75, 1] },
            );

            intersectionObserver.observe(this.container);
        }
    }

    setupControls() {
        let isRotating = false;
        let isPanning = false;
        let previousMousePosition = { x: 0, y: 0 };
        let target = new THREE.Vector3(4, 1.25, 10.5); // Look-at target (center of hatches)
        let lastTouchDistance = 0; // For pinch zoom

        // Store target reference for frameScene to update it
        this.controlsTarget = target;

        this.container.addEventListener("mousedown", (e) => {
            previousMousePosition = { x: e.clientX, y: e.clientY };
            if (e.button === 0) {
                // Left mouse button - rotate
                isRotating = true;
            } else if (e.button === 2) {
                // Right mouse button - pan
                isPanning = true;
            }
        });

        this.container.addEventListener("mousemove", (e) => {
            const deltaX = e.clientX - previousMousePosition.x;
            const deltaY = e.clientY - previousMousePosition.y;

            if (isRotating) {
                // Rotate around target
                const radius = this.camera.position
                    .clone()
                    .sub(target)
                    .length();
                const theta = Math.atan2(
                    this.camera.position.z - target.z,
                    this.camera.position.x - target.x,
                );
                const phi = Math.acos(
                    (this.camera.position.y - target.y) / radius,
                );

                let newTheta = theta + deltaX * 0.008;
                let newPhi = phi - deltaY * 0.008;
                newPhi = Math.max(0.1, Math.min(Math.PI - 0.1, newPhi));

                this.camera.position.x =
                    target.x + radius * Math.sin(newPhi) * Math.cos(newTheta);
                this.camera.position.y = target.y + radius * Math.cos(newPhi);
                this.camera.position.z =
                    target.z + radius * Math.sin(newPhi) * Math.sin(newTheta);
            } else if (isPanning) {
                // Pan (translate) the camera and target
                const panSpeed = 0.1;
                const right = new THREE.Vector3();
                const up = new THREE.Vector3(0, 1, 0);

                right
                    .crossVectors(this.camera.position.clone().sub(target), up)
                    .normalize();

                target.addScaledVector(right, -deltaX * panSpeed);
                target.y += deltaY * panSpeed;
                this.camera.position.addScaledVector(right, -deltaX * panSpeed);
                this.camera.position.y += deltaY * panSpeed;
            }

            this.camera.lookAt(target);
            previousMousePosition = { x: e.clientX, y: e.clientY };
        });

        this.container.addEventListener("mouseup", () => {
            isRotating = false;
            isPanning = false;
        });

        // Prevent context menu on right-click
        this.container.addEventListener("contextmenu", (e) =>
            e.preventDefault(),
        );

        this.container.addEventListener("wheel", (e) => {
            e.preventDefault();
            const direction = this.camera.position
                .clone()
                .sub(target)
                .normalize();
            const radius = this.camera.position.clone().sub(target).length();

            // Smoother zoom with wider range (1 to 30 meters)
            const newRadius = Math.max(
                1,
                Math.min(30, radius + e.deltaY * 0.15),
            );

            this.camera.position
                .copy(target)
                .addScaledVector(direction, newRadius);
            this.camera.lookAt(target);
        });

        // Touch events for trackpad/touchscreen
        this.container.addEventListener("touchstart", (e) => {
            if (e.touches.length === 1) {
                previousMousePosition = {
                    x: e.touches[0].clientX,
                    y: e.touches[0].clientY,
                };
                isRotating = true;
            } else if (e.touches.length === 2) {
                // Two-finger touch for pan/pinch
                isRotating = false;
                isPanning = true;
                const dx = e.touches[0].clientX - e.touches[1].clientX;
                const dy = e.touches[0].clientY - e.touches[1].clientY;
                lastTouchDistance = Math.sqrt(dx * dx + dy * dy);
            }
        });

        this.container.addEventListener("touchmove", (e) => {
            e.preventDefault();

            if (e.touches.length === 1 && isRotating) {
                // Single finger - rotate
                const deltaX = e.touches[0].clientX - previousMousePosition.x;
                const deltaY = e.touches[0].clientY - previousMousePosition.y;

                const radius = this.camera.position
                    .clone()
                    .sub(target)
                    .length();
                const theta = Math.atan2(
                    this.camera.position.z - target.z,
                    this.camera.position.x - target.x,
                );
                const phi = Math.acos(
                    (this.camera.position.y - target.y) / radius,
                );

                let newTheta = theta + deltaX * 0.008;
                let newPhi = phi - deltaY * 0.008;
                newPhi = Math.max(0.1, Math.min(Math.PI - 0.1, newPhi));

                this.camera.position.x =
                    target.x + radius * Math.sin(newPhi) * Math.cos(newTheta);
                this.camera.position.y = target.y + radius * Math.cos(newPhi);
                this.camera.position.z =
                    target.z + radius * Math.sin(newPhi) * Math.sin(newTheta);

                this.camera.lookAt(target);
                previousMousePosition = {
                    x: e.touches[0].clientX,
                    y: e.touches[0].clientY,
                };
            } else if (e.touches.length === 2) {
                // Two-finger touch - pan or pinch zoom
                const touch1 = e.touches[0];
                const touch2 = e.touches[1];

                // Calculate pinch distance and center
                const dx = touch1.clientX - touch2.clientX;
                const dy = touch1.clientY - touch2.clientY;
                const currentDistance = Math.sqrt(dx * dx + dy * dy);
                const centerX = (touch1.clientX + touch2.clientX) / 2;
                const centerY = (touch1.clientY + touch2.clientY) / 2;

                // Calculate center movement
                const centerDeltaX = centerX - previousMousePosition.x;
                const centerDeltaY = centerY - previousMousePosition.y;
                const centerMovement = Math.sqrt(
                    centerDeltaX * centerDeltaX + centerDeltaY * centerDeltaY,
                );

                // Determine if it's pinch or pan based on relative change
                const distanceChange = Math.abs(
                    currentDistance - lastTouchDistance,
                );
                const isPinchGesture =
                    distanceChange > 10 && distanceChange > centerMovement;

                if (isPinchGesture && lastTouchDistance > 0) {
                    // Pinch zoom
                    const scale = lastTouchDistance / currentDistance;
                    const direction = this.camera.position
                        .clone()
                        .sub(target)
                        .normalize();
                    const radius = this.camera.position
                        .clone()
                        .sub(target)
                        .length();
                    const newRadius = Math.max(1, Math.min(30, radius / scale));

                    this.camera.position
                        .copy(target)
                        .addScaledVector(direction, newRadius);
                } else if (centerMovement > 2) {
                    // Pan - use center of two touches
                    const panSpeed = 0.1;
                    const right = new THREE.Vector3();
                    const up = new THREE.Vector3(0, 1, 0);

                    right
                        .crossVectors(
                            this.camera.position.clone().sub(target),
                            up,
                        )
                        .normalize();

                    target.addScaledVector(right, -centerDeltaX * panSpeed);
                    target.y += centerDeltaY * panSpeed;
                    this.camera.position.addScaledVector(
                        right,
                        -centerDeltaX * panSpeed,
                    );
                    this.camera.position.y += centerDeltaY * panSpeed;
                }

                this.camera.lookAt(target);
                previousMousePosition = { x: centerX, y: centerY };
                lastTouchDistance = currentDistance;
            }
        });

        this.container.addEventListener("touchend", (e) => {
            isRotating = false;
            isPanning = false;
            lastTouchDistance = 0;
        });
    }

    /**
     * Create a canvas texture with text for hatch labels (transparent background)
     */
    createTextTexture(text, size = 1024) {
        const canvas = document.createElement("canvas");
        canvas.width = size;
        canvas.height = size;

        const context = canvas.getContext("2d");
        // Transparent background
        context.clearRect(0, 0, size, size);

        // Draw text
        context.fillStyle = "#000000";
        context.font = `bold ${size * 0.2}px Arial`;
        context.textAlign = "center";
        context.textBaseline = "middle";
        context.fillText(text, size / 2, size / 2);

        const texture = new THREE.CanvasTexture(canvas);
        texture.colorSpace = THREE.SRGBColorSpace;
        return texture;
    }

    addHatch(hatch) {
        // Create hatch container mesh - wireframe only to avoid transparency depth-sorting issues
        const geometry = new THREE.BoxGeometry(
            hatch.width,
            hatch.height,
            hatch.depth,
        );
        const material = new THREE.LineBasicMaterial({
            color: 0x888888,
            linewidth: 2,
        });

        // Use EdgesGeometry to show only the edges/wireframe
        const edges = new THREE.EdgesGeometry(geometry);
        const mesh = new THREE.LineSegments(edges, material);

        // Position hatches FRONT-TO-BACK (same level, different Z positions)
        // Hatch 1 at Z = 5 (0 to 10)
        // Hatch 2 at Z = 16 (11 to 21, with 1m gap)
        const hatchIndex = this.hatchMeshes.length;
        const worldX = hatch.width / 2; // Center X for both
        const worldY = hatch.height / 2; // Center Y for both (same level)
        const worldZ = hatchIndex * (hatch.depth + 1) + hatch.depth / 2; // Stack front-to-back

        mesh.position.set(worldX, worldY, worldZ);

        console.log(`📦 Hatch ${hatch.label} (ID: ${hatch.id}):`);
        console.log(
            `   World position: (${worldX.toFixed(1)}, ${worldY.toFixed(1)}, ${worldZ.toFixed(1)})`,
        );
        console.log(
            `   Dimensions: ${hatch.width}×${hatch.height}×${hatch.depth}m`,
        );
        console.log(
            `   maxWeight: ${hatch.maxWeight}t = ${hatch.maxWeight * 1000}kg`,
        );
        console.log(
            `   World bounds: X[${(worldX - hatch.width / 2).toFixed(1)}-${(worldX + hatch.width / 2).toFixed(1)}] Y[${(worldY - hatch.height / 2).toFixed(1)}-${(worldY + hatch.height / 2).toFixed(1)}] Z[${(worldZ - hatch.depth / 2).toFixed(1)}-${(worldZ + hatch.depth / 2).toFixed(1)}]`,
        );

        mesh.castShadow = true;
        mesh.receiveShadow = true;
        this.sceneRoot.add(mesh);

        // Add a box helper to show hatch edges
        const hatchHelper = new THREE.BoxHelper(mesh, 0x222222);
        this.sceneRoot.add(hatchHelper);

        // Add hatch label above the hatch
        const labelTexture = this.createTextTexture(`Hatch ${hatch.label}`);
        const labelMaterial = new THREE.MeshBasicMaterial({
            map: labelTexture,
            transparent: true,
            depthTest: false,
            depthWrite: false,
            side: THREE.DoubleSide,
        });
        const labelGeometry = new THREE.PlaneGeometry(
            hatch.width * 1.5,
            hatch.width * 0.5,
        );
        const labelMesh = new THREE.Mesh(labelGeometry, labelMaterial);

        // Position label high above the hatch
        labelMesh.position.set(worldX, worldY + hatch.height / 2 + 2.5, worldZ);
        labelMesh.renderOrder = 100; // Render on top

        this.sceneRoot.add(labelMesh);
        this.hatchLabels.push(labelMesh); // Store for billboard effect

        // Add to packer with maxWeight converted to KG (API sends TONS)
        const hatchForPacker = {
            id: hatch.id,
            width: hatch.width,
            height: hatch.height,
            depth: hatch.depth,
            maxWeight: (hatch.maxWeight || 0) * 1000, // Convert TONS to KG
        };

        // Store hatch info for weight tracking
        this.hatchMeshes.push({
            hatch,
            mesh,
            helper: hatchHelper,
            usedWeight: 0,
        });
        this.packer.addBin(hatchForPacker);
    }

    visualizeItems(packedItems) {
        console.log(`\n📦 VISUALIZING ${packedItems.length} ITEMS:`);

        packedItems.forEach((item, index) => {
            // Color coding based on weight
            let color;
            if (item.weight > 100) {
                color = 0xff4444;
            } else if (item.weight > 50) {
                color = 0xff8800;
            } else {
                color = 0x4488ff;
            }

            // Create geometry with gap/allowance on each side (subtract 2*gap from each dimension)
            // This creates visual spacing around each cargo item so they don't look packed
            const gapWidth = Math.max(0, item.width - 2 * this.cargoGap);
            const gapHeight = Math.max(0, item.height - 2 * this.cargoGap);
            const gapDepth = Math.max(0, item.depth - 2 * this.cargoGap);

            const geometry = new THREE.BoxGeometry(
                gapWidth,
                gapHeight,
                gapDepth,
            );
            const material = new THREE.MeshStandardMaterial({
                color: color,
                metalness: 0.3,
                roughness: 0.4,
                emissive: 0x000000,
                transparent: true,
                opacity: 0.75,
                side: THREE.DoubleSide,
            });

            const mesh = new THREE.Mesh(geometry, material);

            // Find the hatch this item belongs to
            const hatchMeshInfo = this.hatchMeshes.find(
                (h) => h.hatch.id === item.binId,
            );

            if (hatchMeshInfo) {
                const hatchMesh = hatchMeshInfo.mesh;
                const hatchW = hatchMeshInfo.hatch.width;
                const hatchH = hatchMeshInfo.hatch.height;
                const hatchD = hatchMeshInfo.hatch.depth;

                // Hatch center in world space
                const hatchCenterX = hatchMesh.position.x;
                const hatchCenterY = hatchMesh.position.y;
                const hatchCenterZ = hatchMesh.position.z;

                // Hatch minimum corner in world space
                const hatchMinX = hatchCenterX - hatchW / 2;
                const hatchMinY = hatchCenterY - hatchH / 2;
                const hatchMinZ = hatchCenterZ - hatchD / 2;

                // Convert item from bin coords (0 to width) to world coords
                // item.x, item.y, item.z = bin-local position of item's minimum corner
                // item's center = bin position + item size/2
                const worldX = hatchMinX + item.x + item.width / 2;
                const worldY = hatchMinY + item.y + item.height / 2;
                const worldZ = hatchMinZ + item.z + item.depth / 2;

                mesh.position.set(worldX, worldY, worldZ);

                // Verify bounds
                const itemMinX = worldX - item.width / 2;
                const itemMaxX = worldX + item.width / 2;
                const itemMinY = worldY - item.height / 2;
                const itemMaxY = worldY + item.height / 2;
                const itemMinZ = worldZ - item.depth / 2;
                const itemMaxZ = worldZ + item.depth / 2;

                const hatchMaxX = hatchCenterX + hatchW / 2;
                const hatchMaxY = hatchCenterY + hatchH / 2;
                const hatchMaxZ = hatchCenterZ + hatchD / 2;

                const withinX =
                    itemMinX >= hatchMinX - 0.01 &&
                    itemMaxX <= hatchMaxX + 0.01;
                const withinY =
                    itemMinY >= hatchMinY - 0.01 &&
                    itemMaxY <= hatchMaxY + 0.01;
                const withinZ =
                    itemMinZ >= hatchMinZ - 0.01 &&
                    itemMaxZ <= hatchMaxZ + 0.01;
                const inBounds = withinX && withinY && withinZ;

                const status = inBounds ? "✓ INSIDE" : "✗ OUTSIDE";
                console.log(
                    `  ${status} "${item.description}" (${item.weight}kg) → Hatch ${item.binId}`,
                );
                console.log(
                    `      Bin: (${item.x.toFixed(2)}, ${item.y.toFixed(2)}, ${item.z.toFixed(2)}), ${item.width}×${item.height}×${item.depth}m`,
                );
                console.log(
                    `      World center: (${worldX.toFixed(2)}, ${worldY.toFixed(2)}, ${worldZ.toFixed(2)})`,
                );
                console.log(
                    `      Hatch world: X[${hatchMinX.toFixed(1)}-${hatchMaxX.toFixed(1)}] Y[${hatchMinY.toFixed(1)}-${hatchMaxY.toFixed(1)}] Z[${hatchMinZ.toFixed(1)}-${hatchMaxZ.toFixed(1)}]`,
                );
                console.log(
                    `      Item world:  X[${itemMinX.toFixed(2)}-${itemMaxX.toFixed(2)}] Y[${itemMinY.toFixed(2)}-${itemMaxY.toFixed(2)}] Z[${itemMinZ.toFixed(2)}-${itemMaxZ.toFixed(2)}]`,
                );
                if (!inBounds) {
                    console.log(
                        `      ⚠️  X:${withinX ? "✓" : "✗"}  Y:${withinY ? "✓" : "✗"}  Z:${withinZ ? "✓" : "✗"}`,
                    );
                }

                this.sceneRoot.add(mesh);

                // Track weight per hatch
                hatchMeshInfo.usedWeight += (item.weight || 0) / 1000;
            } else {
                console.warn(`  ✗ Hatch ${item.binId} not found!`);
                this.sceneRoot.add(mesh);
            }

            mesh.castShadow = true;
            mesh.receiveShadow = true;
            mesh.userData = {
                itemId: item.id,
                description: item.description,
                weight: item.weight,
            };

            const helper = new THREE.BoxHelper(mesh, 0x000000);
            this.sceneRoot.add(helper);
        });

        // Log hatch weight utilization
        console.log("=====================================");
        console.log(
            "📦 HATCH CAPACITY STATUS (60% threshold for sequential fill):",
        );
        this.hatchMeshes.forEach((hatchInfo) => {
            const maxWeightKg = (hatchInfo.hatch.maxWeight || 0) * 1000;
            const usedWeightKg = hatchInfo.usedWeight * 1000;
            const weightPercent =
                maxWeightKg > 0
                    ? ((usedWeightKg / maxWeightKg) * 100).toFixed(1)
                    : 0;
            const status = weightPercent >= 60 ? "🔴 FULL" : "🟢 OPEN";
            console.log(
                `  ${hatchInfo.hatch.label}: ${usedWeightKg.toFixed(0)}kg / ${maxWeightKg.toFixed(0)}kg (${weightPercent}%) ${status}`,
            );
        });

        // Log item placement by hatch
        console.log("\n📦 ITEMS BY HATCH (Sequential Fill):");
        const itemsByHatch = {};
        packedItems.forEach((item) => {
            if (!itemsByHatch[item.binId]) itemsByHatch[item.binId] = [];
            itemsByHatch[item.binId].push(item);
        });

        Object.entries(itemsByHatch).forEach(([hatchId, items]) => {
            const totalWeight = items.reduce(
                (sum, item) => sum + item.weight,
                0,
            );
            console.log(`\n  ${hatchId} (Total: ${totalWeight}kg):`);
            items.forEach((item) => {
                const weightCategory =
                    item.weight > 100
                        ? "HEAVY 🔴"
                        : item.weight > 50
                          ? "MEDIUM 🟠"
                          : "LIGHT 🔵";
                console.log(
                    `    → ${item.description} (${item.weight}kg) - ${weightCategory}`,
                );
            });
        });
        console.log("=====================================");
    }

    frameScene() {
        // compute bounding box of sceneRoot and position camera
        try {
            const box = new THREE.Box3().setFromObject(this.sceneRoot);
            const size = new THREE.Vector3();
            box.getSize(size);
            const center = new THREE.Vector3();
            box.getCenter(center);
            console.log(
                "CargoVisualizer: frameScene size, center",
                size,
                center,
            );

            // Update controls target to match the actual scene center
            if (this.controlsTarget) {
                this.controlsTarget.copy(center);
            }

            const maxDim = Math.max(size.x, size.y, size.z, 1);
            const fov = this.camera.fov * (Math.PI / 180);
            let cameraZ = Math.abs(maxDim / 2 / Math.tan(fov / 2));
            cameraZ *= 0.75; // 50% closer to hatches

            // place camera along a diagonal for a better view
            this.camera.position.set(
                center.x + cameraZ,
                center.y + cameraZ * 0.6,
                center.z + cameraZ,
            );
            this.camera.lookAt(center);
        } catch (e) {
            console.warn("frameScene failed", e);
        }
    }

    visualizeUnpacked(unpackedItems) {
        console.warn("Visualizing unpacked items:", unpackedItems.length);
        if (!unpackedItems || unpackedItems.length === 0) return;

        // determine offset to place unpacked stack to the side of hatches
        let maxWidth = 0;
        this.hatchMeshes.forEach((h) => {
            if (h.hatch.width > maxWidth) maxWidth = h.hatch.width;
        });
        const offsetX = maxWidth + 5; // 5 meters to the right

        unpackedItems.forEach((item, idx) => {
            const color = 0xff0000;
            const geometry = new THREE.BoxGeometry(
                item.width,
                item.height,
                item.depth,
            );
            const material = new THREE.MeshStandardMaterial({
                color: color,
                metalness: 0.2,
                roughness: 0.6,
                transparent: true,
                opacity: 0.75,
                side: THREE.DoubleSide,
            });
            const mesh = new THREE.Mesh(geometry, material);

            // stack them
            const gap = 0.5;
            mesh.position.set(
                offsetX + (idx % 5) * (item.width + gap),
                (Math.floor(idx / 5) + 0.5) * (item.height + gap),
                Math.floor(idx / 25) * (item.depth + gap),
            );
            this.sceneRoot.add(mesh);
            const helper = new THREE.BoxHelper(mesh, 0xff0000);
            this.sceneRoot.add(helper);
            console.log(
                "Unpacked item visualized",
                item.id,
                "pos",
                mesh.position,
                "size",
                item.width,
                item.height,
                item.depth,
            );
        });
    }

    packAndVisualize(hatches, cargo) {
        console.log(
            "CargoVisualizer: packAndVisualize called, hatches=",
            hatches.length,
            "cargo=",
            cargo.length,
        );
        // Reset packer and clear scene
        this.packer = new SimpleBinPacker();
        this.hatchMeshes = []; // CRITICAL: Clear hatchMeshes so hatchIndex is calculated correctly
        this.hatchLabels = []; // Clear labels array
        while (this.scene.children.length) {
            this.scene.remove(this.scene.children[0]);
        }

        // Recreate and add root group so subsequent add* methods attach to a visible group
        this.sceneRoot = new THREE.Group();
        this.scene.add(this.sceneRoot);

        // Re-add basic lighting (no grid)
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(100, 100, 100);
        directionalLight.castShadow = true;
        this.scene.add(directionalLight);
        // const gridHelper = new THREE.GridHelper(200, 20);
        // this.scene.add(gridHelper);

        // Add hatches
        hatches.forEach((hatch) => this.addHatch(hatch));

        // Treat each cargo entry as a single booked cargo (do not expand by quantity)
        const expanded = cargo.map((c, idx) => ({
            id: `${c.id}`,
            width: c.width,
            height: c.height,
            depth: c.depth,
            weight: c.weight || 0,
            description: c.description || c.desc || "",
            // preserve original quantity as metadata for labels/tooltips
            originalQuantity: c.quantity || c.qty || 1,
            booking_ref: c.booking_ref || c.bookingRef || null,
        }));

        // Pack items
        const results = this.packer.pack(expanded);
        console.log("Packing results", results);

        // Visualize packed items
        this.visualizeItems(results.packed);

        // Frame camera after adding visuals
        this.frameScene();

        // Visualize unpacked items to the side so user can see what's failing
        if (results.unpacked && results.unpacked.length > 0) {
            this.visualizeUnpacked(results.unpacked);
        }

        return results;
    }

    /**
     * Render hatches and cargo directly using provided measurements.
     * This is a fallback to ensure visualization always shows something.
     */
    renderRaw(hatches, cargo) {
        console.log(
            "CargoVisualizer: renderRaw called, hatches=",
            hatches.length,
            "cargo=",
            cargo.length,
        );
        // Reset scene
        this.packer = new SimpleBinPacker();
        while (this.scene.children.length) {
            this.scene.remove(this.scene.children[0]);
        }

        // Re-add lighting (no grid)
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(100, 100, 100);
        this.scene.add(directionalLight);
        // const gridHelper = new THREE.GridHelper(200, 20);
        // this.scene.add(gridHelper);

        // Add hatches
        hatches.forEach((hatch) => this.addHatch(hatch));

        if (!cargo || cargo.length === 0) return;

        // Treat each cargo entry as a single booked cargo for raw rendering
        const expanded = (cargo || []).map((c) => ({
            id: `${c.id}`,
            width: c.width || 0.1,
            height: c.height || 0.1,
            depth: c.depth || 0.1,
            weight: c.weight || 0,
            description: c.description || "",
            originalQuantity: c.quantity || c.qty || 1,
            booking_ref: c.booking_ref || c.bookingRef || null,
        }));

        // Place items into first hatch in simple grid (no collision checks)
        const firstHatch = hatches[0];
        const gap = 0.1;
        let cursorX = 0;
        let cursorZ = 0;
        let rowHeight = 0;
        expanded.forEach((item, idx) => {
            if (cursorX + item.width > firstHatch.width) {
                cursorX = 0;
                cursorZ += rowHeight + gap;
                rowHeight = 0;
            }

            if (cursorZ + item.depth > firstHatch.depth) {
                // move up a layer
                cursorZ = 0;
                // for simplicity stack up by increasing Y
            }

            const mesh = new THREE.Mesh(
                new THREE.BoxGeometry(item.width, item.height, item.depth),
                new THREE.MeshStandardMaterial({
                    color: 0x77a1ff,
                    metalness: 0.3,
                    roughness: 0.5,
                    transparent: true,
                    opacity: 0.75,
                    side: THREE.DoubleSide,
                }),
            );
            mesh.position.set(
                cursorX + item.width / 2,
                item.height / 2,
                cursorZ + item.depth / 2,
            );
            this.sceneRoot.add(mesh);
            const helper = new THREE.BoxHelper(mesh, 0xff0000);
            this.sceneRoot.add(helper);
            this.addItemLabel(mesh, `U#${item.id}`);
            console.log(
                "Unpacked item visualized",
                item.id,
                "pos",
                mesh.position,
                "size",
                item.width,
                item.height,
                item.depth,
            );

            cursorX += item.width + gap;
            rowHeight = Math.max(rowHeight, item.depth);
        });

        // Frame camera to show hatches and raw items
        this.frameScene();
    }

    /**
     * Toggle item isolation: isolate on first click, reset on second click
     * @param {string|number} itemId - The ID of the item to isolate
     * @returns {boolean} - True if item is now isolated, false if isolation was reset
     */
    isolateItem(itemId) {
        const itemIdStr = String(itemId);

        // Check if user is clicking the same item that's already isolated
        if (this.isolatedItemId === itemIdStr) {
            // Toggle off - reset the view
            console.log("🔄 Toggling OFF - resetting view");
            this.resetIsolation();
            this.isolatedItemId = null;
            return false;
        }

        // Reset any previous isolation first
        if (this.isolatedItemId !== null) {
            console.log(
                `🔄 Switching from item ${this.isolatedItemId} to ${itemIdStr}`,
            );
            this.resetIsolation();
        }

        // Now isolate the new item
        console.log("🔍 isolateItem called for:", itemIdStr);

        let foundItem = false;
        let itemCount = 0;

        // Traverse all meshes in sceneRoot and update their materials
        this.sceneRoot.traverse((child) => {
            if (
                child instanceof THREE.Mesh &&
                child.userData &&
                child.userData.itemId
            ) {
                itemCount++;
                const childItemId = String(child.userData.itemId);

                console.log(
                    `   Checking mesh #${itemCount}: id="${childItemId}", desc="${child.userData.description}"`,
                );

                // Match either exact ID or ID with suffix (e.g., "1" matches "1_0", "1_1", etc.)
                // This handles items created from grouped bookings with quantities
                const isMatch =
                    childItemId === itemIdStr ||
                    childItemId.startsWith(itemIdStr + "_");

                if (isMatch) {
                    // This is the item to highlight - make it opaque and solid
                    foundItem = true;
                    if (child.material) {
                        child.material.transparent = false;
                        child.material.opacity = 1.0;
                        child.material.needsUpdate = true; // Force material update
                    }
                    console.log(
                        `   ✅ HIGHLIGHTED: "${child.userData.description}" (ID: ${childItemId})`,
                    );
                } else {
                    // All other items - hide or make very transparent
                    if (child.material) {
                        child.material.transparent = true;
                        child.material.opacity = 0.05;
                        child.material.needsUpdate = true; // Force material update
                    }
                }
            }
        });

        if (!foundItem) {
            console.warn(
                `⚠️ Item with ID "${itemIdStr}" not found in visualization (scanned ${itemCount} items)`,
            );
            return false;
        } else {
            // Count how many items matched (for grouped bookings)
            let matchCount = 0;
            this.sceneRoot.traverse((child) => {
                if (
                    child instanceof THREE.Mesh &&
                    child.userData &&
                    child.userData.itemId
                ) {
                    const childItemId = String(child.userData.itemId);
                    const isMatch =
                        childItemId === itemIdStr ||
                        childItemId.startsWith(itemIdStr + "_");
                    if (isMatch) matchCount++;
                }
            });

            console.log(
                `✅ Isolation complete: Highlighted ${matchCount} item(s) with ID "${itemIdStr}", hidden ${itemCount - matchCount} items`,
            );
            this.isolatedItemId = itemIdStr; // Track that this item is now isolated
            return true;
        }
    }

    /**
     * Reset isolation - show all items with normal transparency
     */
    resetIsolation() {
        console.log("resetIsolation called");

        this.sceneRoot.traverse((child) => {
            if (
                child instanceof THREE.Mesh &&
                child.userData &&
                child.userData.itemId
            ) {
                if (child.material) {
                    child.material.transparent = true;
                    child.material.opacity = 0.75;
                }
            }
        });

        console.log("✓ All items restored to normal visibility");
    }

    animate = () => {
        requestAnimationFrame(this.animate);

        // Update hatch labels to always face camera (billboard effect)
        this.hatchLabels.forEach((label) => {
            // Make label always face the camera
            label.lookAt(this.camera.position);
        });

        this.renderer.render(this.scene, this.camera);
    };

    onWindowResize = () => {
        if (!this.container) return;

        const currentWidth = this.container.clientWidth;
        const currentHeight = this.container.clientHeight;

        const widthChanged = Math.abs(currentWidth - this.expectedWidth) > 10;
        const heightChanged =
            Math.abs(currentHeight - this.expectedHeight) > 10;

        if (!widthChanged && !heightChanged) {
            return;
        }

        if (currentWidth > 0 && currentHeight > 0) {
            const pixelRatio = window.devicePixelRatio || 1;

            this.expectedWidth = currentWidth;
            this.expectedHeight = currentHeight;

            this.renderer.setPixelRatio(pixelRatio);
            this.renderer.setSize(currentWidth, currentHeight, false);

            this.camera.aspect = currentWidth / currentHeight;
            this.camera.updateProjectionMatrix();
        }
    };

    dispose() {
        this.renderer.dispose();
        this.container.removeChild(this.renderer.domElement);
    }
}

// Export for use in blade templates
window.CargoVisualizer = CargoVisualizer;
