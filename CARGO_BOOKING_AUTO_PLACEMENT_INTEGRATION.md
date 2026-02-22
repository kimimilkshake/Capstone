# Cargo Booking Auto-Placement Integration

## Overview

This document describes the integration of the auto-placement system into the cargo booking approval workflow. The system now validates that booked cargo can physically fit into the vessel's hatches before allowing staff to accept bookings.

## Changes Implemented

### 1. **Removed CargoSampleDataSeeder** ✅
- **File**: `database/seeders/DatabaseSeeder.php`
- **Change**: Removed the call to `$this->call(CargoSampleDataSeeder::class);`
- **Reason**: Sample cargo items are no longer needed since the system now relies on actual booked cargo items that will be placed in the auto-placement system

### 2. **Created CargoAutoPlacementService** ✅
- **File**: `app/Services/CargoAutoPlacementService.php`
- **Purpose**: Validates if cargo items from a booking can fit in the voyage's hatches
- **Key Methods**:
  - `validateCargoPlacement($voyageId, $cargoBookingIds)` - Main validation method
  - `callBinPackingAPI($username, $apiKey, $hatch, $cargoItems)` - Calls 3DBinPacking API

**Features**:
- Validates cargo dimensions against hatch capacity
- Calls 3DBinPacking API for each hatch sequentially
- Returns detailed status including packed/unpacked items
- Graceful fallback if API is not configured (fail-open policy)

### 3. **Updated StaffCargoController** ✅
- **File**: `app/Http/Controllers/Staff/StaffCargoController.php`
- **Changes**:
  - Added `CargoAutoPlacementService` import
  - Enhanced `approve($id)` method to validate placement before acceptance
  - Added new `validatePlacement(Request $request)` API endpoint

**Flow**:
1. When staff clicks "Accept" button, JavaScript sends AJAX request to validate placement
2. `validatePlacement()` API endpoint calls `CargoAutoPlacementService`
3. If cargo can fit → proceed with normal approval process
4. If cargo cannot fit → show warning modal with details of items that don't fit
5. Staff can still choose to accept (items will need manual placement later)

### 4. **Updated Staff Cargo Review View** ✅
- **File**: `resources/views/authorized/staff/showcargo.blade.php`
- **Changes**:
  - Modified Accept buttons to trigger JavaScript validation instead of direct form submission
  - Added `validateAndAccept(event)` JavaScript function
  - Added `showPlacementWarning(data)` function to display warnings
  - Added placement validation modal
  - Added `proceedWithAcceptance()` function to submit form after validation

**Modal Features**:
- Shows warning when cargo cannot fit
- Lists items that exceed hatch capacity
- Allows staff to proceed anyway (semi-automatic placement)
- Shows estimated weight and space utilization

### 5. **Created API Route** ✅
- **Route**: `POST /authorized/staff/api/cargo/placement/validate`
- **Name**: `cargo.placement.validate`
- **Handler**: `StaffCargoController@validatePlacement`
- **Request Body**:
  ```json
  {
    "voyage_id": 1,
    "cargo_booking_ids": [1, 2, 3]
  }
  ```

## Workflow

### Current Staff Cargo Booking Process

```
Staff Review Page (Pending Cargo)
    ↓
Click "View" → Show Cargo Details Page
    ↓
Staff Review Cargo Items (dimensions, photos, pricing)
    ↓
Click "Accept" Button
    ↓
[NEW] JavaScript validates placement via API
    ↓
Success: Can fit in hatches
    ├→ Create Payment record
    ├→ Move to CargoReceipt
    ├→ Create Bill of Lading
    ├→ Send approval email
    └→ Items available for auto-placement visualization
    ↓
OR
    ↓
Failure: Cannot fit in hatches
    ├→ Show warning modal with details
    ├→ List unpacked items
    ├→ Allow staff to cancel or proceed anyway
    └→ If proceed: Follow success path above
```

## API Response Format

### Success Response
```json
{
  "success": true,
  "message": "All cargo items can fit in available hatches",
  "packedItems": [
    {
      "id": 1,
      "name": "Electronics",
      "binId": 1,
      "x": 0.5,
      "y": 0.2,
      "z": 1.0,
      "fitted": true
    }
  ],
  "unpackedItems": []
}
```

### Failure Response
```json
{
  "success": false,
  "message": "Some cargo items cannot fit in available hatches. 2 items require additional space.",
  "packedItems": [
    {
      "id": 1,
      "name": "Glass Bottles",
      "binId": 1,
      "x": 0.5,
      "y": 0.2,
      "z": 1.0,
      "fitted": true
    }
  ],
  "unpackedItems": [
    {
      "id": 2,
      "name": "Steel Coils"
    },
    {
      "id": 3,
      "name": "Heavy Machinery"
    }
  ]
}
```

## Key Features

### 1. **Real-time Placement Validation**
- Occurs at the point of acceptance
- Uses live 3DBinPacking API
- Provides immediate feedback to staff

### 2. **Intelligent Hatch Filling**
- Processes hatches sequentially
- Fills each hatch to maximum capacity
- Tracks remaining unpacked items

### 3. **Error Handling**
- Graceful degradation if API is unavailable
- Fail-open policy (allows acceptance even if validation fails)
- Detailed error logs for troubleshooting
- User-friendly warning messages

### 4. **User Experience**
- Non-blocking AJAX validation
- Clear warning modals for issues
- Option to proceed despite placement warnings
- No interruption to normal workflow

## Database Considerations

### Tables Involved
- `cargo_booking` - Cargo item dimensions and details
- `booking` - Booking header information
- `voyage` - Voyage information
- `vessel` - Vessel details
- `hatch` - Hatch specifications (dimensions, capacity)
- `cargo_item` - Item descriptions and characteristics
- `cargo_receipt` - Created after approval

### Key Relationships
```
booking
  ├─ cargoBookings[] → cargo_booking
  │   └─ cargoItem → cargo_item
  │       └─ cargoClassification
  ├─ voyage → voyage
  │   ├─ vessel → vessel
  │   │   └─ hatches[] → hatch
  │   └─ routePort → route_port
  └─ sender, consignee
```

## Configuration

### API Credentials
The system requires 3DBinPacking API credentials:

```env
# .env file
3DBIN_USERNAME=your_3dbin_username
3DBIN_API_KEY=your_3dbin_api_key
```

If not configured, the system uses fail-open policy and allows cargo acceptance anyway.

## Testing

### Manual Testing Steps

1. **Create a Voyage**
   - Create a voyage with departure date
   - Ensure vessel has hatches with defined dimensions

2. **Create Cargo Booking**
   - Create cargo items with dimensions and weight
   - Ensure dimensions fit within one hatch combined

3. **Test Acceptance**
   - Navigate to staff cargo review page
   - Click "View" on pending booking
   - Click "Accept" button
   - Observe placement validation modal
   - Verify success or warning message

4. **Test Placement Visualization**
   - After acceptance, navigate to staff cargo auto-placement
   - Select the voyage
   - Verify booked cargo appears in visualization

## Future Enhancements

1. **Manual Placement Adjustment**
   - Allow staff to drag cargo in visualization before confirmation
   - Save placement adjustments to database

2. **Placement Constraints**
   - Add breakable/fragile cargo handling
   - Weight distribution validation
   - Segregation rules for incompatible cargo

3. **Bulk Acceptance**
   - Accept multiple bookings at once
   - Consolidated placement validation
   - Batch confirmation

4. **Placement History**
   - Save successful placements to database
   - Track placement efficiency metrics
   - Generate placement reports

5. **Advanced Visualization**
   - Export placement plan as PDF
   - Print-friendly manifest
   - Real-time 3D preview in booking process

## Troubleshooting

### Issue: Placement validation always returns success
**Solution**: Check if 3DBinPacking credentials are configured in `.env`. Without credentials, the system defaults to success.

### Issue: "API request failed" error
**Possible Causes**:
- Invalid API credentials
- API endpoint unreachable
- Insufficient cargo item dimensions
- Network connectivity issue

**Solution**: Check Laravel logs at `storage/logs/laravel.log`

### Issue: Wrong items appearing as unpacked
**Possible Causes**:
- Incorrect dimension units (should be meters)
- Hatch dimensions not in database
- Item weight exceeding capacity

**Solution**: Verify hatch and cargo dimensions in database

## Code Files Modified

1. `database/seeders/DatabaseSeeder.php` - Removed CargoSampleDataSeeder call
2. `app/Services/CargoAutoPlacementService.php` - NEW: Placement validation service
3. `app/Http/Controllers/Staff/StaffCargoController.php` - Added validation methods
4. `resources/views/authorized/staff/showcargo.blade.php` - Added validation UI/JS
5. `routes/web.php` - Added new API endpoint route

## Success Metrics

After implementation, the following should work:

- ✅ Database migrations complete without errors
- ✅ Staff can review pending cargo bookings
- ✅ Clicking "Accept" validates placement automatically
- ✅ Warnings display clearly if cargo cannot fit
- ✅ Staff can still accept bookings despite warnings
- ✅ Approved cargo appears in auto-placement visualization
- ✅ Booking status changes to "Confirmed"
- ✅ Payment records created
- ✅ Bill of Lading generated
- ✅ Customer receives approval email

## Related Documentation

- [CARGO_AUTO_PLACEMENT.md](CARGO_AUTO_PLACEMENT.md) - Auto-placement system overview
- [CARGO_3D_VISUALIZATION.md](CARGO_3D_VISUALIZATION.md) - 3D visualization details
- [CARGO_BOOKING_UPDATES.md](CARGO_BOOKING_UPDATES.md) - Booking system updates
