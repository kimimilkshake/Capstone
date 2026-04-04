/**
 * Improved 3D Bin Packing Algorithm
 * Places items into bins sequentially with collision detection and spacing
 */
class SimpleBinPacker {
    constructor() {
        this.bins = [];
        this.gap = 0.0001; // Minimal gap for collision detection (visual gaps handled by cargoGap in rendering)
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

        // ===== 2-ZONE PACKING BY BOOKING =====
        // Group items by booking_ref
        const bookingGroups = {};
        items.forEach((item) => {
            const bookingRef = item.booking_ref || "unassigned";
            if (!bookingGroups[bookingRef]) bookingGroups[bookingRef] = [];
            bookingGroups[bookingRef].push(item);
        });

        const bookingRefs = Object.keys(bookingGroups);
        console.log(
            `🎯 2-ZONE PACKING BY BOOKING: ${bookingRefs.length} bookings`,
        );

        // Calculate weight for each booking
        const bookingWeights = {};
        bookingRefs.forEach((ref) => {
            bookingWeights[ref] = bookingGroups[ref].reduce(
                (sum, item) => sum + (item.weight || 0),
                0,
            );
        });

        // Assign bookings to zones - check weight balance first, then use round-robin or assign to lighter zone
        const bookingZones = {};
        let leftZoneWeight = 0;
        let rightZoneWeight = 0;

        bookingRefs.forEach((ref, idx) => {
            const bookingWeight = bookingWeights[ref];

            // Check if zones are balanced (equal weight or first booking)
            if (leftZoneWeight === rightZoneWeight) {
                // Zones balanced - use round-robin
                bookingZones[ref] = idx % 2;
            } else {
                // Zones imbalanced - assign to lighter zone
                bookingZones[ref] = leftZoneWeight <= rightZoneWeight ? 0 : 1;
            }

            // Update zone weight
            if (bookingZones[ref] === 0) {
                leftZoneWeight += bookingWeight;
            } else {
                rightZoneWeight += bookingWeight;
            }

            const zoneName = bookingZones[ref] === 0 ? "LEFT" : "RIGHT";
            console.log(
                `   Booking ${ref}: ${zoneName} zone (${bookingGroups[ref].length} items, ${bookingWeights[ref]}kg) [LEFT: ${leftZoneWeight}kg, RIGHT: ${rightZoneWeight}kg]`,
            );
        });

        console.log(`📦 PACKER STATE:`);
        console.log(`   Total Hatches: ${this.bins.length}`);
        this.bins.forEach((bin, idx) => {
            const binVolume = bin.width * bin.height * bin.depth;
            console.log(
                `      Bin ${idx}: id=${bin.id}, size=${bin.width}×${bin.height}×${bin.depth}m, volume=${binVolume.toFixed(2)}m³, maxWeight=${bin.maxWeight}kg`,
            );
        });
        console.log(
            `\n   Items to pack: ${items.length} (in ${bookingRefs.length} bookings)`,
        );
        console.log("");

        // Sort bookings by total weight (heaviest first)
        const sortedBookingRefs = [...bookingRefs].sort((a, b) => {
            const wA = bookingGroups[a].reduce(
                (s, i) => s + (i.weight || 0),
                0,
            );
            const wB = bookingGroups[b].reduce(
                (s, i) => s + (i.weight || 0),
                0,
            );
            return wB - wA;
        });

        // Track sticky bin per booking (first hatch where booking lands)
        const bookingBins = {};

        // Process items booking-by-booking so all items from same booking are consecutive
        const orderedItems = sortedBookingRefs.flatMap((ref) =>
            [...bookingGroups[ref]].sort(
                (a, b) => (b.weight || 0) - (a.weight || 0),
            ),
        );

        for (const item of orderedItems) {
            let placed = false;
            const itemBookingRef = item.booking_ref || "unassigned";
            const assignedZone = bookingZones[itemBookingRef];
            const zoneName = assignedZone === 0 ? "LEFT" : "RIGHT";

            console.log(
                `  📦 "${item.description}" (${item.width}×${item.height}×${item.depth}m, ${item.weight}kg) → ${zoneName} zone [Booking: ${itemBookingRef}]:`,
            );

            const stickyBinId = bookingBins[itemBookingRef];
            // If item has hatch_id, ONLY try that hatch. Otherwise try all bins
            const binsToTry = item.hatch_id
                ? this.bins.filter(
                      (bin) =>
                          bin.id === item.hatch_id && this.canFit(item, bin),
                  )
                : this.bins
                      .filter((bin) => this.canFit(item, bin))
                      .sort((a, b) => {
                          if (stickyBinId !== undefined) {
                              if (a.id === stickyBinId) return -1;
                              if (b.id === stickyBinId) return 1;
                          }
                          const weightDiff = b.usedWeight - a.usedWeight;
                          return weightDiff !== 0 ? weightDiff : a.id - b.id;
                      });

            for (const bin of binsToTry) {
                const rotatedItem = this.autoRotateToFlatten(item);
                let position = this.findPosition(
                    rotatedItem,
                    bin,
                    assignedZone,
                );
                let placedZone = assignedZone;

                // Zone spillover: if assigned zone is full, try the other zone before giving up
                if (!position && assignedZone !== undefined) {
                    const spillZone = assignedZone === 0 ? 1 : 0;
                    position = this.findPosition(rotatedItem, bin, spillZone);
                    if (position) {
                        placedZone = spillZone;
                        console.log(
                            `     ⚠️ Zone spillover: "${item.description}" → ${spillZone === 0 ? "LEFT" : "RIGHT"} zone (assigned zone full)`,
                        );
                    }
                }

                if (position) {
                    this.placeItem(rotatedItem, bin, position);
                    if (bookingBins[itemBookingRef] === undefined) {
                        bookingBins[itemBookingRef] = bin.id;
                    }
                    packed.push({
                        ...rotatedItem,
                        binId: bin.id,
                        zone: placedZone,
                        x: position.x,
                        y: position.y,
                        z: position.z,
                    });
                    const percentUsed = (
                        (bin.usedWeight / bin.maxWeight) *
                        100
                    ).toFixed(1);
                    console.log(
                        `     ✓ Placed in ${bin.id} (${percentUsed}% full)`,
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

            // 2-zone LEFT/RIGHT balance
            const leftWeight = bin.items
                .filter((i) => i.x + i.width / 2 < bin.width / 2)
                .reduce((s, i) => s + (i.weight || 0), 0);
            const rightWeight = bin.items
                .filter((i) => i.x + i.width / 2 >= bin.width / 2)
                .reduce((s, i) => s + (i.weight || 0), 0);
            const leftCount = bin.items.filter(
                (i) => i.x + i.width / 2 < bin.width / 2,
            ).length;
            const rightCount = bin.items.filter(
                (i) => i.x + i.width / 2 >= bin.width / 2,
            ).length;
            console.log(
                `     ⬅️  LEFT:  ${leftWeight.toFixed(1)}kg (${leftCount} items)`,
            );
            console.log(
                `     ➡️  RIGHT: ${rightWeight.toFixed(1)}kg (${rightCount} items)`,
            );
        });
        console.log("🎯 PACKING COMPLETE (2-zone LEFT/RIGHT distribution)\n");

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
        // Don't rotate floor_only items - they must keep their natural orientation
        if (item.floor_only) {
            return item; // Return unchanged
        }

        const dims = [item.width, item.height, item.depth];
        const maxDim = Math.max(...dims);
        const minDim = Math.min(...dims);
        const aspectRatio = maxDim / minDim;

        // Only rotate if aspect ratio is significant (> 1.3)
        if (aspectRatio > 1.3) {
            if (item.height === maxDim) {
                // Tall/upright item — rotate so height becomes depth (lay it flat)
                const rotated = {
                    ...item,
                    width: item.width,
                    height: item.depth,
                    depth: item.height,
                    rotated: true,
                    originalDims: `${item.width}×${item.height}×${item.depth}`,
                    rotatedDims: `${item.width}×${item.depth}×${item.height}`,
                };
                console.log(
                    `      🔄 ROTATING (tall→flat): "${item.description}" (${item.width}×${item.height}×${item.depth}) → (${rotated.width}×${rotated.height}×${rotated.depth})`,
                );
                return rotated;
            }

            if (item.width === maxDim) {
                // Wide item (e.g. flat sheets) — swap width↔depth so the long dimension
                // runs along the hatch's Z axis instead of spanning across its X axis.
                // This prevents the item from exceeding the half-zone boundary.
                const rotated = {
                    ...item,
                    width: item.depth,
                    height: item.height,
                    depth: item.width,
                    rotated: true,
                    originalDims: `${item.width}×${item.height}×${item.depth}`,
                    rotatedDims: `${item.depth}×${item.height}×${item.width}`,
                };
                console.log(
                    `      🔄 ROTATING (wide→long): "${item.description}" (${item.width}×${item.height}×${item.depth}) → (${rotated.width}×${rotated.height}×${rotated.depth})`,
                );
                return rotated;
            }
        }

        return item; // No rotation needed
    }

    // Check if item at position collides with any existing item in the bin
    collidesWith(item, position, bin) {
        const g = this.gap;
        const itemBox = {
            x1: position.x,
            x2: position.x + item.width,
            y1: position.y,
            y2: position.y + item.height,
            z1: position.z,
            z2: position.z + item.depth,
        };

        for (const existing of bin.items) {
            // Expand existing item box by gap on all sides to enforce spacing
            const existBox = {
                x1: existing.x - g,
                x2: existing.x + existing.width + g,
                y1: existing.y - g,
                y2: existing.y + existing.height + g,
                z1: existing.z - g,
                z2: existing.z + existing.depth + g,
            };

            // Check if boxes overlap (with gap)
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

    findPosition(item, bin, zoneConstraint = undefined) {
        // 2-ZONE PLACEMENT: LEFT half (x < width/2) or RIGHT half (x >= width/2)
        const halfW = bin.width / 2;
        const xMin = zoneConstraint === 1 ? halfW : 0;
        const xMax = zoneConstraint === 0 ? halfW : bin.width;
        const zoneName =
            zoneConstraint === 0
                ? "LEFT"
                : zoneConstraint === 1
                  ? "RIGHT"
                  : "FULL";

        // Fine-grained grid scan within the zone's x-range
        const step = 0.05;
        let candidates = [];

        const scanRange = (startX, endX) => {
            for (
                let x = startX;
                x + item.width <= endX + 0.001;
                x = Math.round((x + step) * 1000) / 1000
            ) {
                const posX = Math.min(x, endX - item.width);
                if (posX < startX - 0.001) continue;
                for (
                    let z = 0;
                    z + item.depth <= bin.depth + 0.001;
                    z = Math.round((z + step) * 1000) / 1000
                ) {
                    const posZ = Math.min(z, bin.depth - item.depth);
                    // Stack height at (posX, posZ) — track top item's floor_only, breakable, and weight
                    let stackHeight = 0;
                    let stackedOnFloorOnly = false;
                    let stackedOnBreakable = false; // fragile items — nothing may be placed on top
                    let stackTopWeight = Infinity; // weight of the item at the top of the stack
                    for (const existing of bin.items) {
                        const xOv = !(
                            posX + item.width <= existing.x ||
                            posX >= existing.x + existing.width
                        );
                        const zOv = !(
                            posZ + item.depth <= existing.z ||
                            posZ >= existing.z + existing.depth
                        );
                        if (xOv && zOv) {
                            // Sticky flags: breakable/floor_only block the entire column
                            // regardless of which item happens to be the tallest.
                            // A short breakable item beside a tall regular item must
                            // still prevent anything being placed above the breakable
                            // item's footprint (even if the new item lands at the taller
                            // item's height — it would visually float over the breakable).
                            if (existing.is_breakable)
                                stackedOnBreakable = true;
                            if (existing.floor_only) stackedOnFloorOnly = true;

                            const top = existing.y + existing.height + this.gap;
                            if (top > stackHeight + 0.001) {
                                stackHeight = top;
                                stackTopWeight = existing.weight ?? Infinity;
                            } else if (Math.abs(top - stackHeight) < 0.001) {
                                // Tied top — take the lighter (more restrictive) weight
                                if (
                                    (existing.weight ?? Infinity) <
                                    stackTopWeight
                                )
                                    stackTopWeight =
                                        existing.weight ?? Infinity;
                            }
                        }
                    }
                    // floor_only items cannot have cargo stacked on top of them
                    if (stackHeight > 0.001 && stackedOnFloorOnly) continue;
                    // Breakable items (TV, fridge, eggs…) — nothing may be placed on top,
                    // EXCEPT another breakable flat sheet (e.g. glass on glass).
                    // Glass sheets may only stack up to 0.5 m total to prevent towering.
                    if (stackHeight > 0.001 && stackedOnBreakable) {
                        const incomingIsBreakableFlat =
                            item.is_breakable &&
                            !item.floor_only &&
                            item.height / Math.min(item.width, item.depth) <
                                0.15;
                        if (!incomingIsBreakableFlat) continue;
                        // at least one sheet must be present as a base
                        if (stackHeight < item.height - 0.001) continue;
                        // cap: glass stacks must not exceed 0.5 m total height
                        if (stackHeight >= 0.5) continue;
                    }
                    // floor_only items must sit on the actual deck — cannot be elevated onto other cargo
                    if (item.floor_only && stackHeight > 0.001) continue;
                    // Heavier items cannot be stacked on top of lighter ones
                    if (stackHeight > 0.001 && item.weight > stackTopWeight)
                        continue;
                    // Support area check: at least 75% of the item's footprint must be
                    // covered by items whose top surface IS the stack height level.
                    // Prevents large items from being placed over a small base.
                    if (stackHeight > 0.001) {
                        const itemArea = item.width * item.depth;
                        let supportedArea = 0;
                        for (const ex of bin.items) {
                            if (
                                Math.abs(
                                    ex.y + ex.height + this.gap - stackHeight,
                                ) > 0.002
                            )
                                continue;
                            const ox =
                                Math.min(posX + item.width, ex.x + ex.width) -
                                Math.max(posX, ex.x);
                            const oz =
                                Math.min(posZ + item.depth, ex.z + ex.depth) -
                                Math.max(posZ, ex.z);
                            if (ox > 0 && oz > 0) supportedArea += ox * oz;
                        }
                        if (supportedArea < itemArea * 0.75) continue;
                    }
                    // 0.001m (1mm) tolerance absorbs floating-point accumulation from
                    // repeated gap additions — prevents the last item in a tall stack
                    // from being wrongly rejected and landing on the floor instead.
                    if (stackHeight + item.height > bin.height + 0.001)
                        continue;
                    const pos = { x: posX, y: stackHeight, z: posZ };
                    if (!this.collidesWith(item, pos, bin))
                        candidates.push(pos);
                }
            }
        };

        scanRange(xMin, xMax);

        // If item is wider than the zone half, fall back to full width
        if (candidates.length === 0 && item.width > xMax - xMin) {
            scanRange(0, bin.width);
        }

        if (candidates.length === 0) {
            console.log(
                `    >>> NO VALID POSITION for ${item.width}×${item.height}×${item.depth} in ${zoneName} zone`,
            );
            return null;
        }

        // Flat stackable items (height < 15% of smallest footprint dimension) prefer
        // stacking first for efficiency — e.g. corrugated sheets, boards, glass sheets.
        // Breakable flat items (glass) are included: they stack like sheets.
        // All other items (engines, boxes, barrels) prefer floor first (real-world behavior).
        const isFlat =
            !item.floor_only &&
            item.height / Math.min(item.width, item.depth) < 0.15;

        candidates.sort((a, b) => {
            const aFloor = a.y < 0.001 ? 0 : 1;
            const bFloor = b.y < 0.001 ? 0 : 1;
            // flat items: stacked preferred; all others: floor preferred
            const aScore = isFlat ? 1 - aFloor : aFloor;
            const bScore = isFlat ? 1 - bFloor : bFloor;
            if (aScore !== bScore) return aScore - bScore;
            // Within the same tier (both floor or both stacked), prefer the LOWEST
            // existing stack height — spreads items across multiple columns instead
            // of piling everything into a single tower.
            if (Math.abs(a.y - b.y) > 0.001) return a.y - b.y;
            if (Math.abs(a.z - b.z) > 0.001) return a.z - b.z;
            return zoneConstraint === 0 ? b.x - a.x : a.x - b.x;
        });

        const chosen = candidates[0];
        console.log(
            `    >>> findPosition: ${item.width}×${item.height}×${item.depth} → (${chosen.x.toFixed(2)}, ${chosen.y.toFixed(2)}, ${chosen.z.toFixed(2)}) in ${zoneName} zone ${chosen.y > 0 ? "📚 STACKED" : "🟢 FLOOR"} ✓`,
        );
        return chosen;
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
class CargoVisualizer {
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
        this.cargoGap = 0.001; // 1mm shrink per side so stacked items have visible separation and edges show clearly

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

        this.renderer.shadowMap.enabled = false;
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
        this.scene.add(directionalLight);

        // Grid (hidden to show container clearly)
        // const gridHelper = new THREE.GridHelper(200, 20);
        // this.scene.add(gridHelper);

        // Basic orbit controls - simple version
        this.setupControls();

        // Add window resize listener for responsive behavior
        this.onWindowResize = () => {
            if (!this.container || !this.camera || !this.renderer) return;

            const width = this.container.clientWidth;
            const height = this.container.clientHeight;

            if (width > 0 && height > 0) {
                // Update camera aspect ratio
                this.camera.aspect = width / height;
                this.camera.updateProjectionMatrix();

                // Update renderer size
                this.renderer.setSize(width, height, false);

                console.log(`CargoVisualizer: resized to ${width}×${height}`);
            }
        };

        window.addEventListener("resize", this.onWindowResize);

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
                target.y -= deltaY * panSpeed;
                this.camera.position.addScaledVector(right, -deltaX * panSpeed);
                this.camera.position.y -= deltaY * panSpeed;
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

            // Zoom speed: multiply deltaY by a small factor then scale by
            // current distance so zooming feels consistent at any zoom level.
            const zoomFactor = 1 + e.deltaY * 0.001;
            const newRadius = Math.max(1, Math.min(200, radius * zoomFactor));

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

        // Position hatches FRONT-TO-BACK using cumulative Z calculation
        // This ensures hatches touch each other with minimal gap
        const gapBetweenHatches = 0.2; // 20cm gap between hatches
        let cumulativeZ = hatch.depth / 2; // Start with half depth of current hatch

        // Add full depth of all previously added hatches to position this hatch after them
        for (let i = 0; i < this.hatchMeshes.length; i++) {
            const prevHatch = this.hatchMeshes[i].hatch;
            cumulativeZ += prevHatch.depth + gapBetweenHatches;
        }

        const worldX = hatch.width / 2; // Center X for both
        const worldY = hatch.height / 2; // Center Y for both (same level)
        const worldZ = cumulativeZ;

        mesh.position.set(worldX, worldY, worldZ);

        console.log(`📦 Hatch ${hatch.label} (ID: ${hatch.id}):`);
        console.log(
            `   World position: (${worldX.toFixed(1)}, ${worldY.toFixed(1)}, ${worldZ.toFixed(1)})`,
        );
        console.log(
            `   Dimensions: ${hatch.width}×${hatch.height}×${hatch.depth}m`,
        );
        console.log(
            `   maxWeight: ${hatch.maxWeight}kg (from hatch_capacity_per_hold * 1000)`,
        );
        console.log(
            `   World bounds: X[${(worldX - hatch.width / 2).toFixed(1)}-${(worldX + hatch.width / 2).toFixed(1)}] Y[${(worldY - hatch.height / 2).toFixed(1)}-${(worldY + hatch.height / 2).toFixed(1)}] Z[${(worldZ - hatch.depth / 2).toFixed(1)}-${(worldZ + hatch.depth / 2).toFixed(1)}]`,
        );

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

        // Add to packer with maxWeight already in KG (API provides hatch_capacity_per_hold * 1000)
        const hatchForPacker = {
            id: hatch.id,
            width: hatch.width,
            height: hatch.height,
            depth: hatch.depth,
            maxWeight: hatch.maxWeight || 0, // Already in kg
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
            // Color coding based on item type
            let color;
            if (item.floor_only) {
                color = 0xff8c00; // Orange — floor only
            } else if (item.is_breakable) {
                color = 0xffd700; // Yellow — breakable/fragile
            } else {
                color = 0x4caf50; // Green — regular stackable
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
            const material = new THREE.MeshLambertMaterial({
                color: color,
                transparent: false,
                opacity: 1.0,
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

                // Track weight per hatch (usedWeight in kg, same as maxWeight)
                hatchMeshInfo.usedWeight += item.weight || 0;
            } else {
                console.warn(`  ✗ Hatch ${item.binId} not found!`);
                this.sceneRoot.add(mesh);
            }

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
            const maxWeightKg = hatchInfo.hatch.maxWeight || 0;
            const usedWeightKg = hatchInfo.usedWeight;
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
        // If we already saved the default view, just restore it exactly (no movement)
        if (this._defaultCameraPos) {
            this.camera.position.copy(this._defaultCameraPos);
            if (this.controlsTarget) {
                this.controlsTarget.copy(this._defaultTarget);
            }
            this.camera.lookAt(this._defaultTarget);
            return;
        }

        // First call — compute and store the default view
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

            // Save this position so reset always returns to the exact same spot
            this._defaultCameraPos = this.camera.position.clone();
            this._defaultTarget = center.clone();
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
            const material = new THREE.MeshLambertMaterial({
                color: color,
                transparent: false,
                opacity: 1.0,
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
        this.hatchMeshes = [];
        this.hatchLabels = [];
        while (this.scene.children.length) {
            this.scene.remove(this.scene.children[0]);
        }

        // Recreate and add root group
        this.sceneRoot = new THREE.Group();
        this.scene.add(this.sceneRoot);

        // Re-add basic lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(100, 100, 100);
        this.scene.add(directionalLight);

        // Add hatches
        hatches.forEach((hatch) => this.addHatch(hatch));

        // Normalize all cargo items: ensure booking_ref is a string
        cargo.forEach((item) => {
            if (item.booking_ref) {
                item.booking_ref = String(item.booking_ref);
            } else {
                item.booking_ref = "unassigned";
            }
        });

        // Separate items by booking reference
        const itemsByBooking = {};
        cargo.forEach((item) => {
            const bookingRef = item.booking_ref;
            if (!itemsByBooking[bookingRef]) itemsByBooking[bookingRef] = [];
            itemsByBooking[bookingRef].push(item);
        });

        const bookingRefs = Object.keys(itemsByBooking);
        console.log("Booking refs:", bookingRefs.join(", "));

        // Collect results across all hatches
        const allPacked = [];
        const allUnpacked = [];

        // ── FREE-ASSIGNMENT MODE ──────────────────────────────────────────
        // When no items have a pre-assigned hatch_id (fresh packing run),
        // use ONE global packer with ALL hatches as bins so the algorithm
        // can freely distribute items across all hatches for best fit.
        const freeAssignment = cargo.every((c) => c.hatch_id == null);
        if (freeAssignment) {
            const globalPacker = new SimpleBinPacker();
            const CATWALK_W = 0.6;
            hatches.forEach((hatch) => {
                globalPacker.addBin({
                    id: hatch.id,
                    width: hatch.width,
                    height: hatch.height,
                    depth: hatch.depth,
                    maxWeight: hatch.maxWeight,
                });
                // Pre-register crew catwalks in each bin
                const hBin = globalPacker.bins[globalPacker.bins.length - 1];
                const catwalks = [
                    {
                        x: 0,
                        y: 0,
                        z: 0,
                        width: CATWALK_W,
                        height: hatch.height,
                        depth: hatch.depth,
                    },
                    {
                        x: hatch.width - CATWALK_W,
                        y: 0,
                        z: 0,
                        width: CATWALK_W,
                        height: hatch.height,
                        depth: hatch.depth,
                    },
                    {
                        x: 0,
                        y: 0,
                        z: 0,
                        width: hatch.width,
                        height: hatch.height,
                        depth: CATWALK_W,
                    },
                    {
                        x: 0,
                        y: 0,
                        z: hatch.depth - CATWALK_W,
                        width: hatch.width,
                        height: hatch.height,
                        depth: CATWALK_W,
                    },
                ];
                catwalks.forEach((cw) => {
                    hBin.items.push({
                        ...cw,
                        weight: 0,
                        catwalk: true,
                        description: "Crew Catwalk",
                    });
                    hBin.usedVolume += cw.width * cw.height * cw.depth;
                });
            });

            // Prepare items — normalise dimensions + pass all flags
            const itemsTopack = cargo.map((c) => ({
                id: `${c.id}`,
                width: parseFloat(c.width) || 0.1,
                height: parseFloat(c.height) || 0.1,
                depth: parseFloat(c.depth) || 0.1,
                weight: parseFloat(c.weight) || 0,
                description: c.description || c.desc || "",
                booking_ref: String(c.booking_ref),
                receipt_id: c.receipt_id,
                is_breakable: c.is_breakable === true || c.is_breakable === 1,
                floor_only: c.floor_only === true || c.floor_only === 1,
                hatch_id: null, // free to go to any hatch
            }));

            const results = globalPacker.pack(itemsTopack);
            this.visualizeItems(results.packed);
            if (results.unpacked.length > 0)
                this.visualizeUnpacked(results.unpacked);
            allPacked.push(...results.packed);
            allUnpacked.push(...results.unpacked);

            this.frameScene();
            console.log(
                `📊 packAndVisualize (free-assign) complete: ${allPacked.length} packed, ${allUnpacked.length} unpacked`,
            );
            return { packed: allPacked, unpacked: allUnpacked };
        }

        // ── PRE-ASSIGNED MODE ─────────────────────────────────────────────
        // Items already have hatch_id from a prior save — route each item to
        // its designated hatch and pack within that hatch only.
        // For each hatch, pack all items in that hatch in booking order
        hatches.forEach((hatch) => {
            // Get all items for this hatch
            const hatchItems = cargo.filter((c) => c.hatch_id === hatch.id);

            if (hatchItems.length === 0) {
                console.log(`Hatch ${hatch.id}: no items`);
                return;
            }

            console.log(
                `Hatch ${hatch.id}: packing ${hatchItems.length} items`,
            );

            // Create packer for this hatch
            const hatchPacker = new SimpleBinPacker();
            hatchPacker.addBin({
                id: hatch.id,
                width: hatch.width,
                height: hatch.height,
                depth: hatch.depth,
                maxWeight: hatch.maxWeight,
            });

            // ── Pre-register crew catwalks as phantom blocked regions ──────
            // 0.6 m walkways run along all 4 walls at full height/length.
            // Registering them as items means collidesWith() will never let
            // real cargo land inside a walkway.
            const CATWALK_W = 0.6;
            const hBin = hatchPacker.bins[0];
            const catwalks = [
                // Left wall strip
                {
                    x: 0,
                    y: 0,
                    z: 0,
                    width: CATWALK_W,
                    height: hatch.height,
                    depth: hatch.depth,
                },
                // Right wall strip
                {
                    x: hatch.width - CATWALK_W,
                    y: 0,
                    z: 0,
                    width: CATWALK_W,
                    height: hatch.height,
                    depth: hatch.depth,
                },
                // Front wall strip (full width so corners are covered)
                {
                    x: 0,
                    y: 0,
                    z: 0,
                    width: hatch.width,
                    height: hatch.height,
                    depth: CATWALK_W,
                },
                // Back wall strip
                {
                    x: 0,
                    y: 0,
                    z: hatch.depth - CATWALK_W,
                    width: hatch.width,
                    height: hatch.height,
                    depth: CATWALK_W,
                },
            ];
            catwalks.forEach((cw) => {
                hBin.items.push({
                    ...cw,
                    weight: 0,
                    catwalk: true,
                    description: "Crew Catwalk",
                });
                hBin.usedVolume += cw.width * cw.height * cw.depth;
            });

            // Prepare items in booking order — pass all flags through
            const itemsInOrder = [];
            bookingRefs.forEach((bookingRef) => {
                const bookingItems = hatchItems.filter(
                    (c) => String(c.booking_ref) === bookingRef,
                );
                bookingItems.forEach((c) => {
                    itemsInOrder.push({
                        id: `${c.id}`,
                        width: parseFloat(c.width) || 0.1,
                        height: parseFloat(c.height) || 0.1,
                        depth: parseFloat(c.depth) || 0.1,
                        weight: parseFloat(c.weight) || 0,
                        description: c.description || c.desc || "",
                        booking_ref: String(c.booking_ref),
                        receipt_id: c.receipt_id,
                        is_breakable:
                            c.is_breakable === true || c.is_breakable === 1,
                        floor_only: c.floor_only === true || c.floor_only === 1,
                        hatch_id: hatch.id,
                    });
                });
            });

            console.log(
                `Hatch ${hatch.id}: itemsInOrder has ${itemsInOrder.length} items before packing`,
            );

            // Pack using 2-zone algorithm
            const results = hatchPacker.pack(itemsInOrder);
            console.log(
                `Hatch ${hatch.id}: packed ${results.packed.length}, unpacked ${results.unpacked.length}`,
            );

            // Visualize packed; render unpacked as warning meshes outside hatch
            this.visualizeItems(results.packed);
            if (results.unpacked.length > 0)
                this.visualizeUnpacked(results.unpacked);

            allPacked.push(...results.packed);
            allUnpacked.push(...results.unpacked);
        });

        // Frame camera
        this.frameScene();

        console.log(
            `📊 packAndVisualize complete: ${allPacked.length} packed, ${allUnpacked.length} unpacked`,
        );
        return { packed: allPacked, unpacked: allUnpacked };
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
        // Reset scene but preserve sceneRoot
        this.packer = new SimpleBinPacker();
        this.hatchMeshes = []; // Clear hatch tracking for proper indexing

        // Clear only sceneRoot children, not the entire scene
        while (this.sceneRoot.children.length) {
            this.sceneRoot.remove(this.sceneRoot.children[0]);
        }

        // Ensure sceneRoot is in the scene
        if (!this.scene.children.includes(this.sceneRoot)) {
            this.scene.add(this.sceneRoot);
        }

        // Clear lights (but not sceneRoot itself)
        const lightsToRemove = [];
        this.scene.children.forEach((child) => {
            if (child instanceof THREE.Light) {
                lightsToRemove.push(child);
            }
        });
        lightsToRemove.forEach((light) => this.scene.remove(light));

        // Re-add lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        this.scene.add(ambientLight);
        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(100, 100, 100);
        this.scene.add(directionalLight);

        // Add hatches to visualization and to packer
        hatches.forEach((hatch) => {
            this.addHatch(hatch);
            // Add hatch to packer for intelligent placement
            this.packer.addBin({
                id: hatch.id,
                label: hatch.label,
                width: hatch.width,
                height: hatch.height,
                depth: hatch.depth,
                maxWeight: hatch.maxWeight || 1000,
            });
        });

        if (!cargo || cargo.length === 0) return;

        // Normalize cargo data for packing
        const expandedCargo = (cargo || []).map((c) => ({
            id: `${c.id}`,
            width: parseFloat(c.width) || 0.1,
            height: parseFloat(c.height) || 0.1,
            depth: parseFloat(c.depth) || 0.1,
            weight: parseFloat(c.weight) || 0,
            description: c.description || "Cargo Item",
            is_breakable: c.is_breakable === true || c.is_breakable === 1,
            floor_only: c.floor_only === true || c.floor_only === 1,
            booking_ref: c.booking_ref || c.bookingRef || null,
        }));

        // Use packing algorithm to place items intelligently
        const { packed, unpacked } = this.packer.pack(expandedCargo);

        console.log(
            `📊 Packing Results: ${packed.length} packed, ${unpacked.length} unpacked`,
        );

        // Store packing results for stats display
        this.lastPackingResults = { packed, unpacked, hatches };

        // Render PACKED items (using calculated positions)
        packed.forEach((packedItem) => {
            // Find the hatch this item belongs to
            const hatchIndex = hatches.findIndex(
                (h) => h.id === packedItem.binId,
            );
            if (hatchIndex === -1) {
                console.warn(
                    `Hatch ${packedItem.binId} not found for item ${packedItem.id}`,
                );
                return;
            }

            const hatch = hatches[hatchIndex];

            // Convert LOCAL position (relative to hatch origin 0,0,0) to WORLD position
            // The hatch's local origin aligns with world coordinate:
            // X: 0 (hatch starts at X=0 in world)
            // Y: 0 (hatch starts at Y=0 in world)
            // Z: hatchIndex * (hatch.depth + 1.2) (hatch starts at this Z in world)

            // Packer returns item position (corner), we need to convert to center position
            const itemWorldX = packedItem.x + packedItem.width / 2;
            const itemWorldY = packedItem.y + packedItem.height / 2;
            const itemWorldZ =
                hatchIndex * (hatch.depth + 1.2) +
                packedItem.z +
                packedItem.depth / 2;

            // Create colored mesh based on item type:
            // Orange  = floor_only (motorcycle, livestock, cadaver)
            // Yellow  = is_breakable (TV, fridge, glass, eggs)
            // Green   = regular stackable
            let color;
            if (packedItem.floor_only) {
                color = 0xff8c00; // Orange — floor only
            } else if (packedItem.is_breakable) {
                color = 0xffd700; // Yellow — breakable/fragile
            } else {
                color = 0x4caf50; // Green — regular stackable
            }
            const mesh = new THREE.Mesh(
                new THREE.BoxGeometry(
                    packedItem.width,
                    packedItem.height,
                    packedItem.depth,
                ),
                new THREE.MeshLambertMaterial({
                    color: color,
                    transparent: false,
                    opacity: 1.0,
                }),
            );

            mesh.position.set(itemWorldX, itemWorldY, itemWorldZ);
            mesh.userData = {
                itemId: packedItem.id,
                description: packedItem.description,
                bookingRef: packedItem.booking_ref,
                isBreakable: packedItem.is_breakable,
                hatchId: packedItem.binId,
            };
            this.sceneRoot.add(mesh);

            console.log(
                `✅ PACKED: ${packedItem.description} → Hatch ${hatch.label}`,
                `Local: (${packedItem.x.toFixed(2)}, ${packedItem.y.toFixed(2)}, ${packedItem.z.toFixed(2)})`,
                `World: (${itemWorldX.toFixed(2)}, ${itemWorldY.toFixed(2)}, ${itemWorldZ.toFixed(2)})`,
            );
        });

        // Render UNPACKED items (items that couldn't fit) in a separate area with warning color
        unpacked.forEach((unpackedItem, idx) => {
            // Place unpacked items outside hatches as visual indicator
            const mesh = new THREE.Mesh(
                new THREE.BoxGeometry(
                    unpackedItem.width,
                    unpackedItem.height,
                    unpackedItem.depth,
                ),
                new THREE.MeshLambertMaterial({
                    color: 0xff9999, // Light red for warning
                    transparent: false,
                    opacity: 1.0,
                }),
            );

            // Position unpacked items above hatches as visual warning
            const spacing = 15;
            const baseZ = hatches.length * (hatches[0]?.depth || 10 + 1);
            mesh.position.set(
                5 + idx * (unpackedItem.width + 0.5),
                10 + unpackedItem.height / 2,
                baseZ + 5,
            );

            mesh.userData = {
                itemId: unpackedItem.id,
                description: unpackedItem.description,
                bookingRef: unpackedItem.booking_ref,
                isUnpacked: true,
            };
            this.sceneRoot.add(mesh);

            const helper = new THREE.BoxHelper(mesh, 0xffaa00);
            this.sceneRoot.add(helper);

            console.log(`❌ UNPACKED (NO SPACE): ${unpackedItem.description}`);
        });

        // Frame camera to show all content
        this.frameScene();

        // Force a render immediately
        console.log(
            "CargoVisualizer: forcing render, scene children:",
            this.scene.children.length,
        );
        this.renderer.render(this.scene, this.camera);
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

            // Remove active class from all buttons
            document.querySelectorAll(".isolate-btn").forEach((btn) => {
                btn.classList.remove("active");
                btn.style.backgroundColor = "";
                btn.style.borderColor = "";
                btn.style.color = "";
            });

            this.resetIsolation();
            this.isolatedItemId = null;
            return false;
        }

        // Reset any previous isolation first
        if (this.isolatedItemId !== null) {
            console.log(
                `🔄 Switching from item ${this.isolatedItemId} to ${itemIdStr}`,
            );

            // Remove active class from previously isolated button
            const prevBtn = document.querySelector(
                `.isolate-btn[data-receipt-id="${this.isolatedItemId}"]`,
            );
            if (prevBtn) {
                prevBtn.classList.remove("active");
                prevBtn.style.backgroundColor = "";
                prevBtn.style.borderColor = "";
                prevBtn.style.color = "";
            }

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
                    // This is the item to highlight - swap to flat Lambert (no specular, GPU-cheap)
                    foundItem = true;
                    if (child.material) {
                        // Save original material so we can restore it on reset
                        if (!child.userData.originalMaterial) {
                            child.userData.originalMaterial = child.material;
                        }
                        child.material = new THREE.MeshLambertMaterial({
                            color: child.userData.originalMaterial.color,
                            transparent: false,
                            opacity: 1.0,
                        });
                    }
                    child.visible = true; // Make sure mesh is visible
                    console.log(
                        `   ✅ HIGHLIGHTED: "${child.userData.description}" (ID: ${childItemId})`,
                    );
                } else {
                    // All other items - hide completely
                    if (child.material) {
                        child.material.transparent = true;
                        child.material.opacity = 0; // Completely hide
                        child.material.needsUpdate = true; // Force material update
                    }
                    child.visible = false; // Hide the mesh and any helpers
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

            // Add active class to the button and apply inline styles
            const btn = document.querySelector(
                `.isolate-btn[data-receipt-id="${itemIdStr}"]`,
            );
            if (btn) {
                btn.classList.add("active");
                btn.style.backgroundColor = "#dc3545";
                btn.style.borderColor = "#c82333";
                btn.style.color = "white";
            }
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
                child.visible = true;
                // Restore original MeshStandardMaterial if it was swapped
                if (child.userData.originalMaterial) {
                    child.material = child.userData.originalMaterial;
                    child.userData.originalMaterial = null;
                } else if (child.material) {
                    child.material.transparent = false;
                    child.material.opacity = 1.0;
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
