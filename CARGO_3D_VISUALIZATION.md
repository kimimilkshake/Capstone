# Freight-Packer 3D Visualization Integration

## Summary

Successfully integrated 3D cargo visualization into the staff cargo placement page using the freight-packer library and three.js.

## What's Been Implemented

### 1. **3D Visualization Component**

- **File**: [public/js/cargo-visualizer.js](public/js/cargo-visualizer.js)
- **Features**:
    - Simple bin packing algorithm for placing cargo items into hatches
    - Three.js-based 3D scene rendering
    - Mouse controls: drag to rotate, scroll to zoom
    - Color-coded cargo items for easy identification
    - Real-time packing statistics

### 2 **Backend API Endpoint**

- **Endpoint**: `/api/staff_cargo/packing-data` (GET)
- **Method**: `CargoAutoPlacementController::getPackingData()`
- **Returns**: JSON with:
    - Voyage information
    - Hatch specifications (dimensions)
    - Cargo items with dimensions and weights
- **Usage**: Called by JavaScript to fetch data for visualization

### 3. **Frontend Integration**

- **File**: [resources/views/authorized/staff/staff_cargoautoplacement.blade.php](resources/views/authorized/staff/staff_cargoautoplacement.blade.php)
- **Features**:
    - "3D Visualization" button on voyage selection
    - Hidden visualization container that shows on demand
    - Live statistics showing:
        - Total items
        - Packed items (with success indicator)
        - Unpacked items (with warning if applicable)
        - Packing efficiency percentage
    - List of items that couldn't fit in hatches

### 4. **Sample Data**

- **File**: [database/seeders/CargoSampleDataSeeder.php](database/seeders/CargoSampleDataSeeder.php)
- **Includes**:
    - 8 sample cargo item types (Rice, Steel, Textiles, etc.)
    - 6 sample cargo receipts with dimensions and weights
    - Fixtures automatically added to active voyages
    - Can be run with: `php artisan db:seed --class=CargoSampleDataSeeder`

### 5. **Routes**

- **Staff Route**: `GET /authorized/staff/api/staff_cargo/packing-data`
- **Admin Route**: `GET /authorized/admin/api/admin_cargo/packing-data`
- Both routes configured in [routes/web.php](routes/web.php)

## How to Use

### For Staff/Admin:

1. Navigate to **Authorized > Staff > Cargo Auto Placement** (or Admin)
2. Select a voyage from the dropdown
3. Click the **"3D Visualization"** button
4. The 3D scene will load showing:
    - The hatch container (wireframe outline)
    - Packed cargo items (colored boxes)
    - Placement coordinates and dimensions
5. Interact with the visualization:
    - **Drag** with mouse to rotate view
    - **Scroll** to zoom in/out
6. Review packing statistics below the visualization
7. Click **"Close"** to hide the visualization

## Technical Details

### Packing Algorithm

- **Type**: Sequential bin packing
- **Method**: Places items into hatches in order, moving to the next hatch when current is full
- **Optimization**: Uses corner-point heuristic to find best placement positions
- **Constraints**: Respects hatch dimensions and maximum weight capacity

### 3D Rendering

- **Library**: Three.js (WebGL)
- **Scene Components**:
    - Ambient light + directional light for realistic shadows
    - Wireframe hatches showing container boundaries
    - Colored mesh boxes for cargo items
    - Grid helper for spatial reference
- **Performance**: Smooth rendering with shadow maps enabled

### Dependencies

- `three`: ^r128 (from CDN)
- `freight-packer`: Provided packing algorithm (imported locally)
- Bootstrap 5: For UI components

## Next Steps (Optional Enhancements)

1. **Export Placement Plan**: Add PDF export of placement diagram
2. **Weight Distribution**: Add visualization of weight distribution across hatches
3. **Rotation**: Allow cargo items to be rotated for better packing
4. **Manual Adjustment**: Drag items in 3D view to manually adjust placement
5. **Historical Tracking**: Save successful placements to database
6. **Performance Metrics**: Track packing efficiency over time

## Troubleshooting

### Visualization not appearing:

- Check browser console for JavaScript errors
- Ensure `three` and visualization JS files are loaded
- Verify voyage has cargo items with valid dimensions
- Check that hatches exist for selected voyage

### Items not packing:

- Verify cargo dimensions are entered correctly
- Check that cargo items are smaller than hatch capacity
- Ensure weight limits are respected
- Items larger than hatch will remain unpacked

###API returning errors:

- Check Laravel logs: `storage/logs/laravel.log`
- Verify voyage_id parameter is valid
- Ensure cargo_booking table has dimension data

## Files Modified

- [app/Http/Controllers/CargoAutoPlacementController.php](app/Http/Controllers/CargoAutoPlacementController.php) - Added `getPackingData()` method
- [routes/web.php](routes/web.php) - Added API routes
- [resources/views/authorized/staff/staff_cargoautoplacement.blade.php](resources/views/authorized/staff/staff_cargoautoplacement.blade.php) - Added visualization UI
- [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php) - Added seeder call
- [package.json](package.json) - Added three.js and freight-packer

## Installation Commands

```bash
# Install dependencies
npm install three github:chadiik/freight-packer

# Build assets
npm run build

# Seed sample data
php artisan db:seed --class=CargoSampleDataSeeder

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```
