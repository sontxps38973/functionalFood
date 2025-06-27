<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = \App\Models\Coupon::class;
    public function definition(): array
    {
    return [
            'code' => strtoupper($this->faker->unique()->lexify('COUPON???')),
            'type' => $this->faker->randomElement(['percent', 'fixed']),
            'value' => $this->faker->numberBetween(5, 30),
            'max_discount' => 50000,
            'min_order_value' => 200000,
            'scope' => 'order',
            'is_active' => true,
            'start_at' => now()->subDays(1),
            'end_at' => now()->addDays(7),
        ];
    }
}
