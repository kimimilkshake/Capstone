# Cargo Auto Placement System

## Overview
The Cargo Auto Placement system uses the 3DBinPacking API to automatically calculate optimal cargo placement across multiple hatches in a vessel for a specific voyage.

## Features
- **Multi-Hatch Support**: Automatically distributes cargo across all available hatches in a vessel
- **Dimension-Based Calculation**: Uses length, width, height, and weight from cargo bookings
- **Sequential Hatch Filling**: Fills hatches sequentially until all cargo is placed
- **Remaining Items Tracking**: Shows items that couldn't fit in any hatch
- **Real-time Placement Visualization**: Displays 3D coordinates and dimensions for each placed item

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

### 3. API Integration
- Sends cargo items to 3DBinPacking API for each hatch
- API calculates optimal placement using bin packing algorithms
- Returns packed items with 3D coordinates (x, y, z) and unpacked items

### 4. Sequential Processing
- Processes hatches in order (Hatch 1, Hatch 2, etc.)
- Removes successfully packed items from remaining items list
- Continues to next hatch with remaining items
- Stops when all items are placed or all hatches are full

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

### Environment Variables
Add to your `.env` file:
```env
3DBIN_USERNAME=your_username_here
3DBIN_API_KEY=your_api_key_here
```

### Getting API Credentials
1. Visit [3DBinPacking.com](https://www.3dbinpacking.com/)
2. Sign up for an account
3. Obtain your API credentials from the dashboard
4. Add credentials to `.env` file

## Usage

### For Admin Users
1. Navigate to: `Authorized > Admin > Cargo Auto Placement`
2. Select a voyage from the dropdown
3. Review voyage information, hatch specifications, and cargo items
4. Click "Calculate Auto Placement" button
5. View results showing packed items per hatch

### For Staff Users
1. Navigate to: `Authorized > Staff > Cargo Auto Placement`
2. Follow same steps as admin users

## Results Display

### Placement Results
- **Hatch Label**: Name of the hatch (e.g., Hatch 1, Hatch 2)
- **Packed Items Count**: Number of items successfully placed
- **Unpacked Items Count**: Number of items that couldn't fit
- **Item Details Table**:
  - Item ID (cargo_receipt_id)
  - Position (x, y, z coordinates in meters)
  - Dimensions (width × height × depth in meters)

### Warnings
- System displays warning if items couldn't be placed in any hatch
- Shows count of remaining items that need manual placement

## API Response Structure

### Successful Response
```json
{
  "response": {
    "packed_items": [
      {
        "id": "123",
        "x": 0.0,
        "y": 0.0,
        "z": 0.0,
        "w": 1.2,
        "h": 0.8,
        "d": 1.5
      }
    ],
    "unpacked_items": []
  }
}
```

## Error Handling

### Common Errors
1. **No Hatches Found**: Vessel has no hatch configurations
2. **No Cargo Bookings**: No cargo receipts exist for the voyage
3. **No Valid Dimensions**: Cargo bookings missing length/width/height
4. **API Credentials Missing**: 3DBIN credentials not configured
5. **API Connection Failed**: Unable to reach 3DBinPacking API

### Error Messages
- Displayed as alerts on the page
- Logged to Laravel logs for debugging
- User-friendly messages shown to admin/staff

## Technical Details

### Controller: `CargoAutoPlacementController`

#### Methods
- `show()`: Displays the placement page with voyage selection
- `place()`: Processes placement calculation
- `getVoyagePlacementData()`: Retrieves voyage, hatches, and cargo data
- `callBinPackingAPI()`: Makes API call to 3DBinPacking

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
- API timeout is set to 15 seconds per hatch

## Support
For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review API documentation: [3DBinPacking API Docs](https://www.3dbinpacking.com/docs)
3. Contact system administrator
