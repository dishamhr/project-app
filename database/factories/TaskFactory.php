<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'subject' => fake()->words(2, true),
            'description' => fake()->paragraph(),
            'lecturer' => fake()->name(),
            'deadline_date' => now()->addDays(7)->toDateString(),
            'deadline_time' => '10:00',
            'priority' => Task::PRIORITY_MEDIUM,
            'reminder_minutes' => 1440,
            'status' => Task::STATUS_NOT_STARTED,
            'reminded_at' => null,
        ];
    }

    public function done(): static
    {
        return $this->state(fn () => ['status' => Task::STATUS_DONE]);
    }

    public function highPriority(): static
    {
        return $this->state(fn () => ['priority' => Task::PRIORITY_HIGH]);
    }
}
