<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require_once '../config/db.php';

/**
 * Generate 5000+ medicines dataset
 * Combines different combinations of Indian medicine names, companies, strengths, and forms
 */

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_data'])) {
    // List of common Indian medicine names and generic names
    $medicine_names = [
        'Aspirin', 'Ibuprofen', 'Paracetamol', 'Amoxicillin', 'Azithromycin', 'Ciprofloxacin',
        'Doxycycline', 'Metformin', 'Insulin Glargine', 'Amlodipine', 'Lisinopril', 'Atenolol',
        'Omeprazole', 'Ranitidine', 'Metoclopramide', 'Ondansetron', 'Acyclovir', 'Loratadine',
        'Cetirizine', 'Diphenhydramine', 'Clotrimazole', 'Terbinafine', 'Fluconazole', 'Amitriptyline',
        'Sertraline', 'Paroxetine', 'Alprazolam', 'Diazepam', 'Lorazepam', 'Lithium Carbonate',
        'Carbamazepine', 'Phenytoin', 'Valproic Acid', 'Levetiracetam', 'Tramadol', 'Morphine',
        'Codeine', 'Mefenamic Acid', 'Meloxicam', 'Indomethacin', 'Naproxen', 'Ketoprofen',
        'Piroxicam', 'Tenoxicam', 'Diclofenac', 'Isoniazid', 'Rifampicin', 'Pyrazinamide',
        'Ethambutol', 'Tetracycline', 'Chlortetracycline', 'Minocycline', 'Cephalexin', 'Cefixime',
        'Ceftriaxone', 'Ampicillin', 'Penicillin G', 'Erythromycin', 'Clarithromycin', 'Roxithromycin',
        'Norfloxacin', 'Ofloxacin', 'Levofloxacin', 'Moxifloxacin', 'Gatifloxacin', 'Gentamicin',
        'Neomycin', 'Streptomycin', 'Amikacin', 'Tobramycin', 'Vancomycin', 'Clindamycin',
        'Linezolid', 'Metronidazole', 'Tinidazole', 'Gliclazide', 'Glibenclamide', 'Glipizide',
        'Tolbutamide', 'Pioglitazone', 'Rosiglitazone', 'Sitagliptin', 'Atorvastatin', 'Lovastatin',
        'Pravastatin', 'Simvastatin', 'Rosuvastatin', 'Fenofibrate', 'Gemfibrozil', 'Bezafibrate',
        'Clopidogrel', 'Ticlopidine', 'Dipyridamole', 'Cilostazol', 'Warfarin', 'Heparin',
        'Enoxaparin', 'Dalteparin', 'Tinzaparin', 'Dabigatran', 'Rivaroxaban', 'Apixaban',
        'Edoxaban', 'Vitamin A', 'Vitamin D', 'Vitamin E', 'Vitamin K', 'Vitamin B1',
        'Vitamin B2', 'Vitamin B3', 'Vitamin B5', 'Vitamin B6', 'Vitamin B12', 'Vitamin C',
        'Biotin', 'Folic Acid', 'Calcium Carbonate', 'Calcium Citrate', 'Iron Fumarate',
        'Ferrous Sulfate', 'Zinc Sulfate', 'Magnesium Oxide', 'Copper Sulfate', 'Selenium',
        'Chromium', 'Potassium Chloride', 'Sodium Chloride', 'L-Arginine', 'L-Glutamine',
        'L-Lysine', 'L-Proline', 'L-Glycine', 'L-Ornithine', 'Taurine', 'CoQ10',
        'Alpha Lipoic Acid', 'Ginseng', 'Ginkgo Biloba', 'St Johns Wort', 'Echinacea',
        'Milk Thistle', 'Turmeric', 'Garlic Extract', 'Saw Palmetto', 'Evening Primrose',
        'Fish Oil', 'Flaxseed Oil', 'Probiotics', 'Glucosamine', 'Chondroitin',
        'Hyaluronic Acid', 'Boswellia', 'Collagen', 'Whey Protein', 'Beta Lactamase Inhibitor',
        'Alovera', 'Neem', 'Tulsi', 'Brahmi', 'Shankhpushpi', 'Ashwagandha',
        'Rhodiola', 'Holy Basil', 'Maca Root', 'Cordyceps', 'Moringa', 'Spirulina',
        'Melatonin', 'Valerian Root', 'Passionflower', 'Chamomile', 'Lavender'
    ];
    
    // List of Indian pharmaceutical companies
    $companies = [
        'Cipla', 'Ranbaxy', 'Dr Reddy\'s', 'Novartis', 'Lupin', 'Sun Pharma',
        'Aurobindo', 'Intas', 'Torrent', 'Glenmark', 'Alkem', 'Macleods',
        'Cadila', 'Abbott', 'Pfizer', 'Eli Lilly', 'Merck', 'GSK',
        'AstraZeneca', 'Bristol Myers Squibb', 'Boehringer Ingelheim', 'Janssen',
        'Roche', 'Wyeth', 'Novo Nordisk', 'Sanofi', 'Bayer', 'Generic',
        'Concept', 'Bio Pharma', 'UCB', 'Calpol', 'Combiflam', 'Forte',
        'Voltaren', 'Suprax', 'Septra', 'Avelox', 'Injectafer', 'Leo Pharma',
        'Grifols', 'Amgen', 'Takeda', 'Cipla', 'Humalog', 'Mobicox'
    ];
    
    $strengths = [
        '5mg', '10mg', '25mg', '50mg', '100mg', '200mg', '250mg', '300mg', '400mg', '500mg',
        '600mg', '750mg', '1000mg', '1mg', '2mg', '5mg', '10mg', '20mg', '30mg', '40mg',
        '0.5mg', '1.5mg', '2.5mg', '0.25mg', '0.75mg', '150mg', '80mg', '120mg', '160mg',
        '180mg', '220mg', '1%', '2%', '5%', '10%', '20%', '50mg/5mL', '100mg/5mL',
        '250mg/5mL', '500mg/5mL', '100 IU/mL', '1000 IU', '5000 IU', '10000 IU',
        '15mg', '35mg', '42mg', '45mg', '52mg', '55mg', '65mg', '75mg', '85mg', '95mg'
    ];
    
    $forms = [
        'Tablet', 'Capsule', 'Injection', 'Syrup', 'Suspension', 'Cream', 'Ointment',
        'Gel', 'Oil', 'Powder', 'Solution', 'Drops', 'Spray', 'Patch', 'Pessary',
        'Suppository', 'Enema', 'Inhalation', 'Lotion', 'Emulsion'
    ];
    
    $count = intval($_POST['count'] ?? 100);
    $count = min($count, 5000); // Limit to 5000 per generation
    
    // Generate CSV
    $csv_content = "medicine_name,company,strength,form,price,description,stock\n";
    
    for ($i = 0; $i < $count; $i++) {
        $medicine = $medicine_names[array_rand($medicine_names)];
        $company = $companies[array_rand($companies)];
        $strength = $strengths[array_rand($strengths)];
        $form = $forms[array_rand($forms)];
        $price = number_format((rand(10, 1000) + rand(0, 99) / 100), 2);
        $stock = rand(50, 500);
        $description = "High quality " . $form . " of " . $medicine . " - " . $strength;
        
        $csv_content .= "\"$medicine ($i)\",\"$company\",\"$strength\",\"$form\",$price,\"$description\",$stock\n";
    }
    
    // Save to file
    $filename = 'medicines_' . time() . '.csv';
    $filepath = '../uploads/' . $filename;
    
    if (file_put_contents($filepath, $csv_content)) {
        $success = "✓ Generated $count medicines CSV file successfully!<br>";
        $success .= "📁 File: <strong>$filename</strong> (Size: " . round(filesize($filepath)/1024) . " KB)<br>";
        $success .= "📥 Ready to import above!";
    } else {
        $error = "Failed to generate CSV file.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Medicines - Kundu Pharmacy Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .generator-info {
            background: #eff6ff;
            border-left: 4px solid #0284c7;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.375rem;
        }
        .option {
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
        }
        .option input[type="radio"] {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="admin_dashboard.php" class="brand">💊 Kundu Pharmacy — Admin</a>
    <ul class="nav-links">
        <li><a href="view_orders.php">Orders</a></li>
        <li><a href="add_village.php">Villages</a></li>
        <li><a href="add_medicine.php">Medicines</a></li>
        <li><a href="bulk_import_medicines.php">Bulk Import</a></li>
        <li><a href="../logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container-sm">
    <div class="card">
        <h2>🤖 Generate Medicines CSV</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="generator-info">
            <strong>ℹ️ Automatic Generation:</strong><br>
            This tool generates a CSV file with random combinations of real medicines, companies, strengths, and forms.
            You can then import it using the Bulk Import feature.<br><br>
            <strong>Features:</strong>
            <ul style="margin: 0.5rem 0; padding-left: 1.5rem;">
                <li>5000+ combinations possible</li>
                <li>Real Indian pharmaceutical companies</li>
                <li>Realistic prices and stock levels</li>
                <li>Ready for bulk import</li>
            </ul>
        </div>

        <form method="POST" style="margin-bottom: 2rem;">
            <div class="form-group">
                <label><strong>Number of Medicines to Generate</strong></label>
            </div>

            <div class="option">
                <input type="radio" name="count" value="500" checked> <strong>500 medicines</strong> (Small test)
            </div>
            <div class="option">
                <input type="radio" name="count" value="1000"> <strong>1,000 medicines</strong> (Medium)
            </div>
            <div class="option">
                <input type="radio" name="count" value="2500"> <strong>2,500 medicines</strong> (Large)
            </div>
            <div class="option">
                <input type="radio" name="count" value="5000"> <strong>5,000 medicines</strong> (Very large)
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Generate CSV</button>
        </form>
    </div>

    <div class="card">
        <h3>📋 Next Steps:</h3>
        <ol>
            <li>Select number of medicines above</li>
            <li>Click <strong>Generate CSV</strong></li>
            <li>Go to <a href="bulk_import_medicines.php"><strong>Bulk Import</strong></a></li>
            <li>Upload the generated CSV file</li>
            <li>Review preview and confirm import</li>
        </ol>
    </div>
</div>

</body>
</html>
