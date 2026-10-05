<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => 'DS-'.now()->format('ymd').'-'.strtoupper(Str::random(4)),
            'user_id' => null,
            'customer_name' => fake()->name(),
            'customer_phone' => '09'.fake()->numerify('#########'),
            'customer_address' => 'تهران، خیابان آزادی، پلاک '.fake()->numberBetween(1, 200),
            'note' => null,
            'total' => fake()->numberBetween(100000, 5000000),
            'status' => Order::STATUS_PENDING,
            'payment_method' => Order::METHOD_COD,
            'payment_status' => Order::PAYMENT_PENDING,
        ];
    }

    /** سفارش متعلق به کاربر مشخص */
    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    /** سفارش با روش پرداخت آنلاین */
    public function online(): static
    {
        return $this->state(fn () => ['payment_method' => Order::METHOD_ZARINPAL]);
    }

    /** سفارش پرداخت‌شده */
    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => Order::PAYMENT_PAID,
            'ref_id' => fake()->numberBetween(1000000000, 9999999999),
            'paid_at' => now(),
        ]);
    }
}
