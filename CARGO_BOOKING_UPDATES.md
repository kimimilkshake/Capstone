# Cargo Booking System Updates

## Overview
Integrated cargo_classification table into cargo booking forms (both staff and passenger) and made measurements optional with a Yes/No toggle.

## Changes Made

### 1. Controllers Updated

#### StaffCargoController.php
- **Import Added**: `use App\Models\CargoClassification;`
- **create() method**: Now passes `$cargoClassifications` to the view
  ```php
  $cargoClassifications = CargoClassification::orderBy('cargo_classification_name')->get();
  return view('authorized.staff.cargobooking', compact('voyages', 'cargoItems', 'cargoClassifications'));
  ```
- **store() method**: Updated validation to make measurements optional
  - `cargo_quantity.*`, `cargo_weight.*`, `cargo_length.*`, `cargo_width.*`, `cargo_height.*` are now nullable
  - Added validation for `measurement_unit.*` and `with_measurement.*`

#### PassengerController.php
- **Import Added**: `use App\Models\CargoClassification;`
- **index() method**: Now passes both `$cargoItems` and `$cargoClassifications` to the view
  ```php
  $cargoClassifications = $type === 'cargo' ? CargoClassification::orderBy('cargo_classification_name')->get() : collect();
  ```
- **confirmCargo() method**: Updated validation to make measurements optional
  - Same changes as StaffCargoController

### 2. Views Updated

#### Staff Cargo Booking (authorized/staff/cargobooking.blade.php)
**Key Changes:**
- Classification dropdown now pulls from `cargo_classification` table
- Added "With Measurement Range?" toggle with Yes/No buttons
- Measurement fields hidden by default, shown only when "Yes" is selected
- Quantity and Weight fields always visible (optional)
- Updated JavaScript to handle toggle functionality

**New Form Structure:**
```
Cargo Classification (required) ┐
Cargo Description (required)    ├─ Always visible
With Measurement Range? (Y/N)   ┘
Quantity (optional)
Weight (optional)
└─ Measurements Section (hidden by default)
    ├─ Length
    ├─ Width
    ├─ Height
    ├─ Unit selector
    └─ Auto-calculated CBM
```

#### Passenger Cargo Booking (passenger/cargobooking.blade.php)
**Key Changes:**
- Classification dropdown now pulls from `cargo_classification` table
- Added "With Measurement Range?" toggle with Yes/No buttons
- Photo upload still required for passengers
- Measurement fields hidden by default, shown only when "Yes" is selected
- Same validation structure as staff form

**New Form Structure:**
```
Cargo Classification (required) ┐
Cargo Description (required)    ├─ Always visible
With Measurement Range? (Y/N)   ┘
Photo Upload (required - passenger only)
Quantity (optional)
Weight (optional)
└─ Measurements Section (hidden by default)
    ├─ Length
    ├─ Width
    ├─ Height
    ├─ Unit selector
    └─ Auto-calculated CBM
```

### 3. JavaScript Updates

Both forms now include enhanced JavaScript for:
- **Measurement Toggle**: Click Yes/No buttons to show/hide measurement section
- **Dynamic Button States**: Buttons highlight when selected
- **Auto-clear Values**: When "No" is selected, measurement values are cleared
- **Dynamic Item Creation**: New cargo items inherit the measurement toggle state

### 4. Required vs Optional Fields

#### Always Required:
- Sender First Name, Last Name, Contact, Email
- Consignee First Name, Last Name, Contact
- Cargo Classification
- Cargo Description
- Photo Upload (Passenger only)

#### Now Optional (Previously Required):
- Quantity
- Weight
- Cargo Dimensions (Length, Width, Height)
- Measurement Unit
- CBM (auto-calculated when measurements provided)

### 5. Classification Integration

The classification system now works as follows:
1. **Dropdown Source**: `cargo_classification` table (replacing string literals)
2. **Classification Display**: Shows `cargo_classification_name` from database
3. **Description Filtering**: When a description is selected, classification is automatically populated based on the cargo item's existing classification field
4. **Direction**: Classification → Description (user selects description, classification shows corresponding value)

## Database Relationships

- **cargo_classification table**: 
  - `cargo_classification_id` (Primary Key)
  - `cargo_classification_name` (Display name)

- **cargo_item table**: Links to classification via `cargo_item_classification` field (string match)

## Testing Checklist

- [ ] Staff can create cargo booking with optional measurements
- [ ] Passenger can create cargo booking with optional measurements
- [ ] "Yes" button shows measurement fields
- [ ] "No" button hides and clears measurement fields
- [ ] Photos required for passenger bookings
- [ ] Classification dropdown populated from database
- [ ] Description filtering works correctly
- [ ] CBM calculation works when measurements provided
- [ ] Form validation passes/fails appropriately
- [ ] Multiple cargo items can be added
- [ ] Dynamic cargo items inherit toggle behavior

## Files Modified

1. `app/Http/Controllers/Staff/StaffCargoController.php`
2. `app/Http/Controllers/PassengerController.php`
3. `resources/views/authorized/staff/cargobooking.blade.php`
4. `resources/views/passenger/cargobooking.blade.php`

## Notes

- Measurements are now truly optional - users can submit bookings WITHOUT providing dimensions
- The with_measurement field is stored in the hidden input but not currently persisted to bindings (validate/store should be updated if persistence is needed)
- Classification data is pulled directly from the database table instead of being derived from cargo items
- All measurements retain their optional nature for both staff and passenger bookings
