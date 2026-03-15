# COT Plan - Vessel Configuration Files

## Overview

This directory contains vessel configuration files with a centralized index system. The system uses:

- **cot_plan_index.json** - Master index of all vessels
- **vessel\_\*.json** - Individual vessel files with accommodation details

## System Flow

```
CotPlanHelper getVesselPlan(1)
        ↓
Check cot_plan_index.json
        ↓
Find vessel ID in index
        ↓
Get specific file path from index
        ↓
Load vessel_1.json
        ↓
Return data
```

## File Structure

### Index File: cot_plan_index.json

Contains a registry of all available vessels:

```json
{
    "vessels": [
        {
            "vessel_id": 1,
            "vessel_name": "MV ROSALIA 3",
            "vessel_code": "MVR3-001",
            "file": "vessel_1.json"
        },
        {
            "vessel_id": 2,
            "vessel_name": "MV CEBU PRINCESS",
            "vessel_code": "MCP-002",
            "file": "vessel_2.json"
        }
    ]
}
```

### Individual Vessel Files: vessel\_\*.json

Each vessel has its own file with detailed accommodation information:

- `vessel_id`: Unique identifier
- `vessel_name`: Name of the vessel
- `vessel_code`: Code/reference
- `accommodations`: Array of available accommodation types

### Accommodation Object Structure

Each accommodation contains:

- `accommodation_id`: Unique identifier within the vessel
- `accommodation_name`: Name/type of accommodation (e.g., "Suite", "Standard Room")
- `capacity_per_unit`: Number of passengers per unit
- `total_units`: Total number of units available
- `amenities`: Array of amenities provided
- `description`: Optional detailed description

## Example Vessel File (vessel_1.json)

```json
{
    "vessel_id": 1,
    "vessel_name": "MV ROSALIA 3",
    "vessel_code": "MVR3-001",
    "accommodations": [
        {
            "accommodation_id": 1,
            "accommodation_name": "Suite",
            "capacity_per_unit": 2,
            "total_units": 5,
            "amenities": [
                "Air Conditioning",
                "Private Bathroom",
                "TV",
                "Mini Bar"
            ],
            "description": "Luxury suite with ocean view"
        }
    ]
}
```

## How to Add a New Vessel

### Step 1: Add to Index

Edit `cot_plan_index.json` and add a new entry to the vessels array:

```json
{
    "vessel_id": 3,
    "vessel_name": "MV NEW SHIP",
    "vessel_code": "MNS-003",
    "file": "vessel_3.json"
}
```

### Step 2: Create Vessel File

Create `vessel_3.json` with the complete vessel data and accommodations.

### Step 3: System Recognizes It

The `CotPlanHelper` will automatically find and load your new vessel!

## How to Edit Accommodations

1. Open the vessel file (e.g., `vessel_1.json`)
2. Find the `accommodations` array
3. Add a new accommodation object OR modify existing ones
4. Ensure valid JSON syntax
5. Save the file

Example of adding a new accommodation:

```json
{
    "accommodation_id": 4,
    "accommodation_name": "Penthouse",
    "capacity_per_unit": 4,
    "total_units": 2,
    "amenities": ["Luxury Amenities", "Private Pool", "Dedicated Service"],
    "description": "Ultra-premium accommodation"
}
```

## Important Notes

- **Index must be valid JSON** - The system depends on reading the index first
- **File references must match** - The "file" value in index must match the actual filename
- **Vessel IDs must be unique** - No duplicate vessel IDs in index or vessel files
- **Accommodation IDs are per-vessel** - Each vessel independently manages accommodation IDs
- **Test JSON syntax** - Use online JSON validators if unsure

## Best Practices

✅ Always update the index when adding/removing vessels
✅ Keep file names simple: `vessel_1.json`, `vessel_2.json`, etc.
✅ Use descriptive accommodation names
✅ Validate JSON before saving
✅ Backup files before major changes
✅ Document any custom fields you add

## Advantages of This System

1. **Centralized Management** - Index provides quick overview of all vessels
2. **Scalable** - Add new vessels by just adding an index entry and vessel file
3. **Organized** - Each vessel data is isolated in its own file
4. **Easy Migration** - Can be easily migrated to database later if needed
5. **Version Control Friendly** - Changes are tracked per vessel
6. **No Breaking Changes** - Adding vessels doesn't affect existing code
