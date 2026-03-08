# Cargo Auto Placement System

## Overview

The Cargo Auto Placement system displays cargo items and hatch data for manual placement review and visualization across multiple hatches in a vessel for a specific voyage.

## Features

- **Multi-Hatch Support**: View all cargo across available hatches in a vessel
- **Dimension-Based Display**: Shows length, width, height, and weight from cargo bookings
- **Cargo Organization**: Groups cargo items by cargo type and dimensions
- **Hatch Visualization**: Displays hatch specifications and capacity information
- **Manual Placement Planning**: Interactive interface for planning cargo placement

## How It Works

### 1. Voyage Selection

- Admin/Staff selects a voyage from the dropdown
- System loads all cargo receipts associated with that voyage
- System retrieves all hatches for the voyage's vessel

### 2. Data Collection

The system gathers:

- **Hatch Data**: Length, width, height, and weight capacity from `hatch` table
- **Cargo Data**: Dimensions (length, width, height) and weight from `cargo_booking` table
- **Quantity**: Number of items from `cargo_receipt` table

### 3. Data Preparation

- Prepares cargo items for manual placement review
- Calculates cargo volume and weight requirements
- Displays available hatch space and capacity

### 4. Visualization

- Displays all cargo items and hatch information
- Shows dimensions and weight specifications
- Enables staff to review and plan placement manually

## Database Structure

### Required Tables

- `voyage`: Voyage information
- `vessel`: Vessel details
- `hatch`: Hatch specifications (dimensions, capacity)
- `cargo_receipt`: Cargo receipt records with voyage_id
- `cargo_booking`: Cargo dimensions (length, width, height, weight)
- `cargo_item`: Cargo item descriptions

### Key Relationships

```
Voyage → Vessel → Hatches
Voyage → CargoReceipts → CargoBooking (dimensions)
CargoReceipt → CargoItem (description)
```

## Configuration

No external API configuration required. The system uses internal database data for cargo and hatch information.

## Usage

### For Admin Users

1. Navigate to: `Authorized > Admin > Cargo Auto Placement`
2. Select a voyage from the dropdown
3. Review voyage information, hatch specifications, and cargo items
4. Use the provided data to plan cargo placement manually
5. View cargo and hatch information for planning

### For Staff Users

1. Navigate to: `Authorized > Staff > Cargo Auto Placement`
2. Follow same steps as admin users

## Display Information

### Voyage & Vessel Information

- **Voyage Details**: Voyage ID, departure date, route
- **Vessel Information**: Vessel name, total hatch capacity
- **Hatch Specifications**: For each hatch - label, dimensions (L×W×H), weight capacity

### Cargo Items

- **Item Details Table**:
    - Item ID (cargo_receipt_id)
    - Item Name/Description
    - Dimensions (width × height × depth)
    - Weight
    - Quantity

### Placement Planning

- Staff can review all cargo and hatch information together
- Information provided for manual placement decision-making

## Error Handling

### Common Errors

1. **No Hatches Found**: Vessel has no hatch configurations
2. **No Cargo Bookings**: No cargo receipts exist for the voyage
3. **No Valid Dimensions**: Cargo bookings missing length/width/height

### Error Messages

- Displayed as alerts on the page
- Logged to Laravel logs for debugging
- User-friendly messages shown to admin/staff

## Technical Details

### Controller: `CargoAutoPlacementController`

#### Methods

- `show()`: Displays the placement page with voyage selection
- `place()`: Prepares cargo and hatch data for review
- `getVoyagePlacementData()`: Retrieves voyage, hatches, and cargo data
- `getPackingData()`: Returns JSON data for visualization

### Routes

**Admin:**

- GET `/authorized/admin/admin_cargo/placement`
- POST `/authorized/admin/admin_cargo/place`

**Staff:**

- GET `/authorized/staff/staff_cargo/placement`
- POST `/authorized/staff/staff_cargo/place`

## Models Used

- `Voyage`: Voyage model with relationships
- `Vessel`: Vessel model with hatches relationship
- `Hatch`: Hatch specifications
- `CargoReceipt`: Cargo receipt records
- `CargoBooking`: Cargo dimensions and weight
- `CargoItem`: Cargo item descriptions

## Future Enhancements

- 3D visualization of cargo placement
- Export placement plan to PDF
- Weight distribution validation
- Manual adjustment of placements
- Save placement results to database
- Historical placement tracking

## Troubleshooting

### Items Not Packing

- Check cargo dimensions are entered correctly
- Verify hatch dimensions are accurate
- Ensure items aren't larger than hatch capacity
- Check weight limits aren't exceeded

### API Errors

- Verify API credentials are correct
- Check internet connection
- Review Laravel logs for detailed error messages
- Ensure API endpoint is accessible

### Performance Issues

- Large number of items may take longer to process
- Consider pagination for voyages with many cargo items

## Support

For issues or questions:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database connections and hatch configurations
3. Contact system administrator
