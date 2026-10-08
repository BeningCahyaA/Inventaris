<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['mobile', 'semi-mobile', 'fixed'];
        $category = fake()->randomElement($categories);
        
        // Generate asset tag berdasarkan kategori
        $prefix = match($category) {
            'mobile' => 'M',
            'semi-mobile' => 'SM',
            'fixed' => 'F',
        };
        $year = date('y');
        $sequence = fake()->unique()->numberBetween(1, 999);
        $assetTag = sprintf("%s-%s-%03d", $prefix, $year, $sequence);

        $statuses = ['in_use', 'maintenance', 'broken', 'available'];
        $conditions = ['baik', 'rusak', 'rusak_total'];

        return [
            'asset_tag' => $assetTag,
            'name' => fake()->randomElement([
                'Laptop Dell Latitude',
                'Laptop HP EliteBook',
                'Mouse Logitech',
                'Keyboard Mechanical',
                'Monitor Samsung 24"',
                'Printer Canon',
                'Scanner Epson',
                'Projector BenQ',
                'Webcam Logitech',
                'Headset Audio-Technica'
            ]) . ' ' . fake()->numberBetween(1, 100),
            'person_in_charge' => fake()->name(),
            'location' => fake()->randomElement(['Gudang A', 'Gudang B', 'Kantor Pusat', 'Site Project']),
            'vendor' => fake()->company(),
            'serial_number' => strtoupper(fake()->bothify('SN-####-??##')),
            'purchase_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'warranty_expiry_date' => fake()->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'category' => $category,
            'status' => fake()->randomElement($statuses),
            'condition' => fake()->randomElement($conditions),
            'description' => fake()->sentence(10),
        ];
    }

    /**
     * State: Asset berkategori mobile
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'mobile',
            'asset_tag' => sprintf("M-%s-%03d", date('y'), fake()->unique()->numberBetween(1, 999)),
        ]);
    }

    /**
     * State: Asset berkategori fixed
     */
    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'fixed',
            'asset_tag' => sprintf("F-%s-%03d", date('y'), fake()->unique()->numberBetween(1, 999)),
        ]);
    }

    /**
     * State: Asset berkategori semi-mobile
     */
    public function semiMobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'semi-mobile',
            'asset_tag' => sprintf("SM-%s-%03d", date('y'), fake()->unique()->numberBetween(1, 999)),
        ]);
    }

    /**
     * State: Asset dengan status in use
     */
    public function inUse(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_use',
        ]);
    }

    /**
     * State: Asset dengan status maintenance
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
        ]);
    }

    /**
     * State: Asset dengan status broken
     */
    public function broken(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'broken',
            'condition' => 'rusak_total',
        ]);
    }
}
