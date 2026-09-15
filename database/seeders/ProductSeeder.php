<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Populates the products table with realistic hardware/construction
     * inventory across 6 categories, with deliberately varied quantities
     * so low-stock and out-of-stock scenarios are visible on the dashboard.
     */
    public function run(): void
    {
        $products = [
            // Lumber
            ['name' => '2x4 Pine Stud (8ft)', 'sku' => 'LUM-001', 'description' => 'Standard kiln-dried pine stud, ideal for framing.', 'category' => 'Lumber', 'quantity' => 240, 'reorder_level' => 50, 'unit_price' => 4.25, 'supplier' => 'Pacific Timber Co.'],
            ['name' => 'Plywood Sheet 4x8 (1/2")', 'sku' => 'LUM-002', 'description' => 'Sanded plywood, suitable for subflooring and cabinetry.', 'category' => 'Lumber', 'quantity' => 65, 'reorder_level' => 20, 'unit_price' => 38.99, 'supplier' => 'Pacific Timber Co.'],
            ['name' => 'Pressure-Treated 4x4 Post (10ft)', 'sku' => 'LUM-003', 'description' => 'Ground-contact rated post for decks and fences.', 'category' => 'Lumber', 'quantity' => 8, 'reorder_level' => 15, 'unit_price' => 24.50, 'supplier' => 'Pacific Timber Co.'],

            // Hardware
            ['name' => 'Wood Screws #8 x 2" (Box of 100)', 'sku' => 'HDW-001', 'description' => 'Phillips-head, coarse thread, zinc coated.', 'category' => 'Hardware', 'quantity' => 180, 'reorder_level' => 30, 'unit_price' => 9.99, 'supplier' => 'FastenPro Supply'],
            ['name' => 'Galvanized Hinges 3.5" (Pair)', 'sku' => 'HDW-002', 'description' => 'Heavy-duty door hinges, rust resistant.', 'category' => 'Hardware', 'quantity' => 42, 'reorder_level' => 15, 'unit_price' => 6.75, 'supplier' => 'FastenPro Supply'],
            ['name' => 'Padlock Set with Keys', 'sku' => 'HDW-003', 'description' => 'Weatherproof brass padlock, 2 keys included.', 'category' => 'Hardware', 'quantity' => 0, 'reorder_level' => 10, 'unit_price' => 14.99, 'supplier' => 'SecureIt Locks'],
            ['name' => 'Heavy Duty Chain 1/4" (per ft)', 'sku' => 'HDW-004', 'description' => 'Zinc-plated steel chain, sold by the foot.', 'category' => 'Hardware', 'quantity' => 320, 'reorder_level' => 50, 'unit_price' => 1.85, 'supplier' => 'SecureIt Locks'],

            // Tools
            ['name' => 'Claw Hammer 16oz', 'sku' => 'TLS-001', 'description' => 'Fiberglass handle, forged steel head.', 'category' => 'Tools', 'quantity' => 34, 'reorder_level' => 10, 'unit_price' => 19.99, 'supplier' => 'ToughGrip Tools'],
            ['name' => 'Cordless Drill 20V', 'sku' => 'TLS-002', 'description' => 'Includes battery, charger, and carrying case.', 'category' => 'Tools', 'quantity' => 12, 'reorder_level' => 8, 'unit_price' => 89.99, 'supplier' => 'ToughGrip Tools'],
            ['name' => 'Tape Measure 25ft', 'sku' => 'TLS-003', 'description' => 'Locking blade, magnetic hook.', 'category' => 'Tools', 'quantity' => 56, 'reorder_level' => 15, 'unit_price' => 12.49, 'supplier' => 'ToughGrip Tools'],
            ['name' => 'Circular Saw 7-1/4"', 'sku' => 'TLS-004', 'description' => 'Corded, includes carbide-tipped blade.', 'category' => 'Tools', 'quantity' => 4, 'reorder_level' => 6, 'unit_price' => 64.00, 'supplier' => 'ToughGrip Tools'],
            ['name' => 'Adjustable Wrench Set (3-piece)', 'sku' => 'TLS-005', 'description' => '6", 8", and 10" wrenches with chrome finish.', 'category' => 'Tools', 'quantity' => 27, 'reorder_level' => 10, 'unit_price' => 22.99, 'supplier' => 'ToughGrip Tools'],

            // Electrical
            ['name' => '12-Gauge Electrical Wire (per 100ft)', 'sku' => 'ELC-001', 'description' => 'THHN copper wire, suitable for residential wiring.', 'category' => 'Electrical', 'quantity' => 18, 'reorder_level' => 10, 'unit_price' => 45.50, 'supplier' => 'VoltWorks Electrical'],
            ['name' => 'Duplex Wall Outlet', 'sku' => 'ELC-002', 'description' => 'Standard 15A grounded outlet, white.', 'category' => 'Electrical', 'quantity' => 95, 'reorder_level' => 20, 'unit_price' => 3.25, 'supplier' => 'VoltWorks Electrical'],
            ['name' => 'Circuit Breaker 20A', 'sku' => 'ELC-003', 'description' => 'Single-pole breaker, standard panel fit.', 'category' => 'Electrical', 'quantity' => 6, 'reorder_level' => 12, 'unit_price' => 11.99, 'supplier' => 'VoltWorks Electrical'],
            ['name' => 'LED Work Light 4000 Lumens', 'sku' => 'ELC-004', 'description' => 'Portable jobsite light, adjustable stand.', 'category' => 'Electrical', 'quantity' => 0, 'reorder_level' => 8, 'unit_price' => 39.99, 'supplier' => 'VoltWorks Electrical'],

            // Plumbing
            ['name' => 'PVC Pipe 1/2" (10ft)', 'sku' => 'PLB-001', 'description' => 'Schedule 40 PVC, for cold water lines.', 'category' => 'Plumbing', 'quantity' => 72, 'reorder_level' => 20, 'unit_price' => 5.75, 'supplier' => 'FlowRight Plumbing Supply'],
            ['name' => 'Ball Valve 3/4"', 'sku' => 'PLB-002', 'description' => 'Brass shut-off valve, lead-free.', 'category' => 'Plumbing', 'quantity' => 14, 'reorder_level' => 15, 'unit_price' => 8.49, 'supplier' => 'FlowRight Plumbing Supply'],
            ['name' => 'Pipe Wrench 14"', 'sku' => 'PLB-003', 'description' => 'Cast iron jaw, self-adjusting.', 'category' => 'Plumbing', 'quantity' => 21, 'reorder_level' => 8, 'unit_price' => 27.99, 'supplier' => 'FlowRight Plumbing Supply'],

            // Paint
            ['name' => 'Interior Latex Paint (1 Gallon, White)', 'sku' => 'PNT-001', 'description' => 'Eggshell finish, low-VOC formula.', 'category' => 'Paint', 'quantity' => 48, 'reorder_level' => 15, 'unit_price' => 32.99, 'supplier' => 'ColorCraft Paints'],
            ['name' => 'Paint Roller Kit', 'sku' => 'PNT-002', 'description' => 'Includes roller frame, 2 covers, and tray.', 'category' => 'Paint', 'quantity' => 3, 'reorder_level' => 10, 'unit_price' => 15.99, 'supplier' => 'ColorCraft Paints'],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}