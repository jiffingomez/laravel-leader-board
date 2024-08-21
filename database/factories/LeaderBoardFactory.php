<?php

namespace Database\Factories;

use App\Models\LeaderBoard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaderBoard>
 */
class LeaderBoardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LeaderBoard::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 80),
            'points' => $this->faker->numberBetween(0, 100),
            'address' => $this->faker->address(),
        ];
    }
}
