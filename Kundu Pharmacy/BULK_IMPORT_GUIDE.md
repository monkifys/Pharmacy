# 📥 Bulk Medicine Import Guide

## Quick Start (3 Steps)

### Step 1: Update Database Schema
1. Go to **Admin Panel** → Navigate to **Migrate DB Schema** (or go to `admin/migrate_medicines_schema.php`)
2. Click **Run Migration** button
3. Schema will be updated with fields: company, strength, form, price, description, stock

### Step 2: Generate or Prepare Medicine Data
You have two options:

**Option A: Auto-Generate 5000+ Medicines (Easiest)**
1. Go to **Admin Panel** → **Generate CSV Data** (or `admin/generate_medicines_data.php`)
2. Select number of medicines (500, 1000, 2500, or 5000)
3. Click "Generate CSV"
4. CSV file is ready to import

**Option B: Use Your Own Data**
1. Prepare a CSV file with these columns:
   ```
   medicine_name,company,strength,form,price,description,stock
   ```
2. Example:
   ```
   Aspirin,Cipla,500mg,Tablet,15,Pain reliever,500
   Amoxicillin,Ranbaxy,250mg,Capsule,45,Antibiotic,300
   ```

### Step 3: Import Bulk Medicines
1. Go to **Admin Panel** → **Bulk Import** (or `admin/bulk_import_medicines.php`)
2. Select your CSV file
3. Click **Upload & Preview**
4. Review the preview (first 10 rows)
5. Click **Confirm Import**
6. Done! All medicines are now in your database

## CSV File Format

**Required Columns:**
- `medicine_name` - Name of the medicine (string)
- `company` - Pharmaceutical company (string)
- `strength` - Medicine strength (string, e.g., "500mg", "250mg", "5ml")
- `form` - Dosage form (Tablet, Capsule, Injection, Syrup, etc.)
- `price` - Price in rupees (decimal, e.g., 45.50)

**Optional Columns:**
- `description` - Description text (string)
- `stock` - Stock quantity (integer, default: 100)

## Sample CSV Data

Download the included `sample_medicines_500.csv` or create your own.

```csv
medicine_name,company,strength,form,price,description,stock
Aspirin,Bayer,500mg,Tablet,15,Analgesic and antipyretic,500
Ibuprofen,Cipla,200mg,Tablet,20,Anti-inflammatory,450
Paracetamol,Crocin,500mg,Tablet,12,Pain reliever,600
Amoxicillin,Novartis,250mg,Capsule,45,Antibiotic,300
```

## Features

✅ **Bulk Import** - Import 5000+ medicines at once  
✅ **Auto-Generation** - Generate realistic data instantly  
✅ **Preview** - See first 10 rows before importing  
✅ **Error Handling** - Validates all medicines before import  
✅ **Search & Filter** - Find medicines by name, company, or price  
✅ **Stock Management** - Track inventory for each medicine  
✅ **Easy Updates** - Add individual medicines or bulk update

## Common Issues

**Q: Where do I find the migration page?**
A: After admin login, go to `admin/migrate_medicines_schema.php`

**Q: How many medicines can I import at once?**
A: Technically unlimited, but recommended: 1000-5000 per import

**Q: What if my CSV has errors?**
A: The system will show which rows failed and why. Fix them and re-import.

**Q: Can I download a CSV template?**
A: Yes! Use the "Generate CSV Data" tool to create a template, then modify it.

## File Locations

- **Bulk Import:** `/admin/bulk_import_medicines.php`
- **Generate Data:** `/admin/generate_medicines_data.php`
- **Migration:** `/admin/migrate_medicines_schema.php`
- **Manage Medicines:** `/admin/add_medicine.php`
- **Sample CSV:** `/downloads/sample_medicines_500.csv`

## Like Netmeds/1mg

Your pharmacy system now has features similar to Netmeds and 1mg:

✅ Thousands of medicines in database  
✅ Easy search and filtering  
✅ Price and stock management  
✅ Company and form classification  
✅ Strength variants  
✅ Fast bulk operations  

Happy importing! 💊
