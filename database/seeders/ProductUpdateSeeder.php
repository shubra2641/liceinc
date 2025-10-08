<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductUpdate;
use Illuminate\Database\Seeder;

class ProductUpdateSeeder extends Seeder
{
    /**   * Run the database seeds. */
    public function run(): void
    {
        $product = Product::first();

        if (! $product) {
            $this->command->info('No products found. Please create a product first.');

            return;
        }

        // Create sample product updates
        ProductUpdate::create([
            'product_id' => $product->id,
            'version' => '1.0.1',
            'title' => 'Bug Fixes and Improvements',
            'description' => 'Fixed several bugs and improved performance',
            'changelog' => [
                'Fixed login issue',
                'Improved database performance',
                'Updated UI components',
            ],
            'is_major' => false,
            'is_required' => false,
            'is_active' => true,
            'released_at' => now()->subDays(5),
        ]);

        ProductUpdate::create([
            'product_id' => $product->id,
            'version' => '1.1.0',
            'title' => 'New Features Release',
            'description' => 'Added new features and enhanced functionality',
            'changelog' => [
                'Added dark mode support',
                'New dashboard widgets',
                'Enhanced security features',
                'Improved mobile responsiveness',
            ],
            'is_major' => true,
            'is_required' => false,
            'is_active' => true,
            'released_at' => now()->subDays(2),
        ]);

        ProductUpdate::create([
            'product_id' => $product->id,
            'version' => '1.0.2',
            'title' => 'Security Update',
            'description' => 'Critical security patches and fixes',
            'changelog' => [
                'Fixed XSS vulnerability',
                'Updated authentication system',
                'Enhanced data encryption',
            ],
            'is_major' => false,
            'is_required' => true,
            'is_active' => true,
            'released_at' => now()->subDay(),
        ]);

        $this->command->info('Sample product updates created successfully!');
    }
}
