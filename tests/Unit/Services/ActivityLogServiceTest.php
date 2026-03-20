<?php

namespace Tests\Unit\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use Tests\TestCase;

class ActivityLogServiceTest extends TestCase
{
    // ─── log() ────────────────────────────────────────────────

    public function test_log_creates_activity_log_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        ActivityLogService::log(
            action: 'create',
            description: 'Test log entry',
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id'     => $user->id,
            'action'      => 'create',
            'description' => 'Test log entry',
        ]);
    }

    public function test_log_does_nothing_when_unauthenticated(): void
    {
        ActivityLogService::log(
            action: 'create',
            description: 'Should not be logged',
        );

        $this->assertDatabaseMissing('activity_logs', [
            'description' => 'Should not be logged',
        ]);
    }

    public function test_log_stores_model_type_and_id(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $model = User::factory()->create();

        ActivityLogService::log(
            action: 'update',
            description: 'Mise à jour utilisateur',
            model: $model,
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id'    => $user->id,
            'model_type' => User::class,
            'model_id'   => $model->id,
        ]);
    }

    public function test_log_stores_old_and_new_values(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        ActivityLogService::log(
            action: 'update',
            description: 'Changement de prix',
            oldValues: ['price' => 100000],
            newValues: ['price' => 120000],
        );

        $log = ActivityLog::where('user_id', $user->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals(['price' => 100000], $log->old_values);
        $this->assertEquals(['price' => 120000], $log->new_values);
    }

    public function test_log_stores_null_model_when_not_provided(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        ActivityLogService::log(
            action: 'login',
            description: 'Connexion',
        );

        $this->assertDatabaseHas('activity_logs', [
            'user_id'    => $user->id,
            'model_type' => null,
            'model_id'   => null,
        ]);
    }

    public function test_log_stores_user_id_of_authenticated_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1);
        ActivityLogService::log(action: 'create', description: 'Log user1');

        $this->actingAs($user2);
        ActivityLogService::log(action: 'create', description: 'Log user2');

        $this->assertDatabaseHas('activity_logs', ['user_id' => $user1->id, 'description' => 'Log user1']);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user2->id, 'description' => 'Log user2']);
    }
}
