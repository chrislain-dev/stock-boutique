<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\User;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    /**
     * Test that creating brand logs activity.
     */
    public function test_creating_brand_logs_activity(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->actingAs($admin);

        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
        ]);

        $log = ActivityLog::where('action', 'created')
            ->where('model_name', Brand::class)
            ->where('record_id', $brand->id)
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('created', $log->action);
        $this->assertEquals($admin->id, $log->user_id);
    }

    /**
     * Test that updating brand logs activity.
     */
    public function test_updating_brand_logs_activity(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->actingAs($admin);

        $brand = Brand::factory()->create();

        $brand->update([
            'name' => 'Updated Brand Name',
        ]);

        $log = ActivityLog::where('action', 'updated')
            ->where('model_name', Brand::class)
            ->where('record_id', $brand->id)
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('updated', $log->action);
        $this->assertEquals($admin->id, $log->user_id);

        // Verify old values are logged
        if ($log->old_values) {
            $this->assertArrayHasKey('name', json_decode($log->old_values, true));
        }
    }

    /**
     * Test that deleting brand logs activity.
     */
    public function test_deleting_brand_logs_activity(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $this->actingAs($admin);

        $brand = Brand::factory()->create();
        $brandId = $brand->id;

        $brand->delete();

        $log = ActivityLog::where('action', 'deleted')
            ->where('model_name', Brand::class)
            ->where('record_id', $brandId)
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('deleted', $log->action);
        $this->assertEquals($admin->id, $log->user_id);
    }

    /**
     * Test that user_id is logged correctly on activities.
     */
    public function test_user_id_is_captured_correctly(): void
    {
        /** @var User $user1 */
        $user1 = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        /** @var User $user2 */
        $user2 = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        // Create brand by user1
        $this->actingAs($user1);
        $brand = Brand::factory()->create();

        // Verify user1 is in log
        $log = ActivityLog::where('record_id', $brand->id)
            ->where('action', 'created')
            ->first();

        $this->assertEquals($user1->id, $log->user_id);

        // Update by user2
        $this->actingAs($user2);
        $brand->update(['name' => 'Updated By User2']);

        // Verify user2 is in new log
        $log = ActivityLog::where('record_id', $brand->id)
            ->where('action', 'updated')
            ->latest()
            ->first();

        $this->assertEquals($user2->id, $log->user_id);
    }
}
