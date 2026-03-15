# COT Plan Integration Guide

## How It Works

The COT Plan system uses a **two-file approach** for better organization:

### 1. Index File (`cot_plan_index.json`)

Master registry of all vessels - the entry point for all lookups.

### 2. Individual Vessel Files (`vessel_*.json`)

Specific accommodation details for each vessel.

## Flow Diagram

```
Your Code
    ↓
CotPlanHelper::getVesselPlan(1)
    ↓
Checks cot_plan_index.json
    ↓
Finds vessel_id: 1
    ↓
Gets file reference: "vessel_1.json"
    ↓
Loads storage/cot_plan/vessel_1.json
    ↓
Returns complete vessel data
```

## Quick Start

### Using in Your Code

#### Get Vessel Data with Accommodations

```php
use App\Helpers\CotPlanHelper;

$vesselData = CotPlanHelper::getVesselPlan(1);
// Returns all data from vessel_1.json
```

#### Get Only Accommodations

```php
$accommodations = CotPlanHelper::getAccommodations(1);
// Returns the accommodations array
```

#### Get Specific Accommodation

```php
$suite = CotPlanHelper::getAccommodation(1, 1);
// vessel_id: 1, accommodation_id: 1
```

#### Check All Available Vessels

```php
$vessels = CotPlanHelper::getAllVessels();
// Returns all entries from the index
```

#### Get List of Vessel IDs

```php
$vesselIds = CotPlanHelper::getAllVesselIds();
// Returns [1, 2, 3, ...]
```

## Example Implementation

### In Your Booking Controller

```php
use App\Helpers\CotPlanHelper;

class PassengerBookingController extends Controller
{
    public function selectVessel()
    {
        // Get all available vessels from index
        $vessels = CotPlanHelper::getAllVessels();

        return view('booking.select-vessel', [
            'vessels' => $vessels
        ]);
    }

    public function selectAccommodation($vesselId)
    {
        // Verify vessel exists in index
        $vessel = CotPlanHelper::getVesselInfo($vesselId);

        if (!$vessel) {
            return back()->with('error', 'Vessel not found');
        }

        // Load accommodations from specific vessel file
        $accommodations = CotPlanHelper::getAccommodations($vesselId);

        return view('booking.select-accommodation', [
            'vessel' => $vessel,
            'accommodations' => $accommodations
        ]);
    }

    public function bookAccommodation(Request $request)
    {
        $vesselId = $request->input('vessel_id');
        $accommodationId = $request->input('accommodation_id');

        // Verify both exist
        if (!CotPlanHelper::accommodationExists($vesselId, $accommodationId)) {
            return back()->withErrors('Invalid selection');
        }

        // Get accommodation details for booking
        $accommodation = CotPlanHelper::getAccommodation($vesselId, $accommodationId);

        // ... process booking
    }
}
```

### In Your Blade View

```blade
@php
    use App\Helpers\CotPlanHelper;
    $vessels = CotPlanHelper::getAllVessels();
@endphp

<div class="vessels-grid">
    @foreach($vessels as $vessel)
        <div class="vessel-card">
            <h3>{{ $vessel['vessel_name'] }}</h3>
            <p>Code: {{ $vessel['vessel_code'] }}</p>
            <a href="/booking/{{ $vessel['vessel_id'] }}/accommodations" class="btn btn-primary">
                Book Now
            </a>
        </div>
    @endforeach
</div>
```

## Adding a New Vessel

### Step 1: Update Index

Edit `storage/cot_plan/cot_plan_index.json`:

```json
{
  "vessels": [
    { ... existing vessels ... },
    {
      "vessel_id": 3,
      "vessel_name": "MV NEW SHIP",
      "vessel_code": "MNS-003",
      "file": "vessel_3.json"
    }
  ]
}
```

### Step 2: Create Vessel File

Create `storage/cot_plan/vessel_3.json`:

```json
{
    "vessel_id": 3,
    "vessel_name": "MV NEW SHIP",
    "vessel_code": "MNS-003",
    "accommodations": [
        {
            "accommodation_id": 1,
            "accommodation_name": "Deluxe Suite",
            "capacity_per_unit": 2,
            "total_units": 8,
            "amenities": ["AC", "Private Bath", "TV"],
            "description": "Luxury suite"
        },
        {
            "accommodation_id": 2,
            "accommodation_name": "Standard Room",
            "capacity_per_unit": 1,
            "total_units": 20,
            "amenities": ["AC", "Private Bath"],
            "description": "Standard room"
        }
    ]
}
```

### Step 3: Done!

The system will automatically recognize and load your new vessel.

## Modifying Accommodations

1. Open the specific vessel file (e.g., `vessel_1.json`)
2. Edit, add, or remove accommodations
3. Keep `accommodation_id` unique within that vessel
4. Save - no index changes needed!

Example: Adding a new accommodation to vessel_1.json

```json
{
    "accommodation_id": 4,
    "accommodation_name": "Family Suite",
    "capacity_per_unit": 4,
    "total_units": 3,
    "amenities": ["AC", "Private Bath", "Kitchen", "Living Area"],
    "description": "Perfect for families"
}
```

## API Reference

### Static Methods Available

```php
// Core Methods
CotPlanHelper::getVesselPlan($vesselId)           // Get full data
CotPlanHelper::getAccommodations($vesselId)       // Get accommodations array
CotPlanHelper::getAccommodation($vesselId, $accId) // Get specific accommodation

// Index/Info Methods
CotPlanHelper::getVesselIndex()                   // Get full index
CotPlanHelper::findVesselInIndex($vesselId)       // Find vessel in index
CotPlanHelper::getVesselInfo($vesselId)           // Get vessel basic info
CotPlanHelper::getAllVessels()                    // Get all vessels from index
CotPlanHelper::getAllVesselIds()                  // Get array of vessel IDs

// Utility Methods
CotPlanHelper::accommodationExists($vesselId, $accId) // Check if exists
```

## Advantages

✅ **Index-Based** - Centralized vessel registry  
✅ **Organized** - Each vessel isolated in its own file  
✅ **Scalable** - Easy to add/remove vessels  
✅ **Flexible** - Modify accommodations independently  
✅ **Fast** - Direct file access, no database queries  
✅ **Maintainable** - Clear separation of concerns  
✅ **Testable** - Easy to mock for unit testing

## File Locations

```
storage/
└── cot_plan/
    ├── cot_plan_index.json      (Master index)
    ├── vessel_1.json            (Vessel 1 data)
    ├── vessel_2.json            (Vessel 2 data)
    ├── vessel_3.json            (Vessel 3 data)
    ├── README.md                (Setup guide)
    └── INTEGRATION_GUIDE.md     (This file)
```

## Future Enhancements

Consider adding to your accommodation objects:

- `price_per_night` - Pricing information
- `availability_start` - Availability dates
- `availability_end` - Availability dates
- `max_stay_days` - Booking restrictions
- `min_stay_days` - Booking restrictions
- `special_rules` - Custom rules as needed
