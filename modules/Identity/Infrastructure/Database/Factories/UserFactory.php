<?php

namespace Modules\Identity\Infrastructure\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Modules\Identity\Infrastructure\Persistence\UserModel;

/**
 * @extends Factory<UserModel>
 */
class UserFactory extends Factory
{
    protected $model = UserModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => Hash::make('password'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'is_admin' => false,
            'deactivated' => false,
        ];
    }

    public function deactivated(): static
    {
        return $this->state(fn (): array => ['deactivated' => true]);
    }
}
