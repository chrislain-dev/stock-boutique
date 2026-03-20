<?php

namespace Tests\Unit\Services;

use App\Enums\PaymentStatus;
use App\Models\ProductModel;
use App\Models\Reseller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\DashboardService;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    private DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService();
    }

    // ─── getAdminStats ─────────────────────────────────────────

    public function test_get_admin_stats_returns_required_keys(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $stats = $this->service->getAdminStats('day');

        $this->assertArrayHasKey('revenue', $stats);
        $this->assertArrayHasKey('profit', $stats);
        $this->assertArrayHasKey('sales_count', $stats);
        $this->assertArrayHasKey('units_sold', $stats);
        $this->assertArrayHasKey('low_stock', $stats);
    }

    public function test_get_admin_stats_counts_only_completed_sales(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Sale::factory()->create(['sale_status' => 'completed', 'created_by' => $user->id, 'paid_amount' => 50000, 'total_amount' => 50000]);
        Sale::factory()->create(['sale_status' => 'cancelled', 'created_by' => $user->id, 'paid_amount' => 50000, 'total_amount' => 50000]);

        $stats = $this->service->getAdminStats('day');

        $this->assertEquals(1, $stats['sales_count']);
    }

    public function test_get_admin_stats_revenue_equals_paid_amounts(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Sale::factory()->create(['sale_status' => 'completed', 'paid_amount' => 100000, 'total_amount' => 100000, 'created_by' => $user->id]);
        Sale::factory()->create(['sale_status' => 'completed', 'paid_amount' => 50000,  'total_amount' => 50000,  'created_by' => $user->id]);

        $stats = $this->service->getAdminStats('day');

        $this->assertEquals(150000, $stats['revenue']);
    }

    public function test_get_admin_stats_with_different_periods(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach (['day', 'week', 'month', 'quarter', 'year'] as $period) {
            $stats = $this->service->getAdminStats($period);
            $this->assertIsArray($stats);
            $this->assertArrayHasKey('revenue', $stats);
        }
    }

    // ─── getVendeurStats ──────────────────────────────────────

    public function test_get_vendeur_stats_returns_only_user_sales(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->actingAs($user1);

        Sale::factory()->create(['sale_status' => 'completed', 'created_by' => $user1->id, 'paid_amount' => 50000, 'total_amount' => 50000]);
        Sale::factory()->create(['sale_status' => 'completed', 'created_by' => $user1->id, 'paid_amount' => 50000, 'total_amount' => 50000]);
        Sale::factory()->create(['sale_status' => 'completed', 'created_by' => $user2->id, 'paid_amount' => 50000, 'total_amount' => 50000]);

        $stats = $this->service->getVendeurStats($user1->id);

        $this->assertEquals(2, $stats['sales_count']);
    }

    public function test_get_vendeur_stats_returns_required_keys(): void
    {
        $user = User::factory()->create();

        $stats = $this->service->getVendeurStats($user->id);

        $this->assertArrayHasKey('sales_count', $stats);
        $this->assertArrayHasKey('units_sold', $stats);
    }

    // ─── getRecentSales ───────────────────────────────────────

    public function test_get_recent_sales_returns_limited_results(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Sale::factory()->count(10)->create(['sale_status' => 'completed', 'created_by' => $user->id, 'paid_amount' => 50000, 'total_amount' => 50000]);

        $recent = $this->service->getRecentSales(5);

        $this->assertCount(5, $recent);
    }

    public function test_get_recent_sales_returns_only_completed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Sale::factory()->create(['sale_status' => 'completed', 'created_by' => $user->id, 'paid_amount' => 50000, 'total_amount' => 50000]);
        Sale::factory()->create(['sale_status' => 'cancelled', 'created_by' => $user->id, 'paid_amount' => 50000, 'total_amount' => 50000]);

        $recent = $this->service->getRecentSales(10);

        $this->assertCount(1, $recent);
    }

    // ─── getLowStockCount ─────────────────────────────────────

    public function test_get_low_stock_count_counts_non_serialized_below_minimum(): void
    {
        ProductModel::factory()->nonSerialized()->lowStock()->create();
        ProductModel::factory()->nonSerialized()->create(['quantity_stock' => 10, 'stock_minimum' => 2]);

        $count = $this->service->getLowStockCount();

        $this->assertEquals(1, $count);
    }

    public function test_get_low_stock_count_excludes_inactive_models(): void
    {
        ProductModel::factory()->nonSerialized()->lowStock()->create(['is_active' => false]);

        $count = $this->service->getLowStockCount();

        $this->assertEquals(0, $count);
    }

    // ─── getResellersWithDebt ─────────────────────────────────

    public function test_get_resellers_with_debt_returns_only_debtors(): void
    {
        Reseller::factory()->withDebt(100000)->create();
        Reseller::factory()->create(['solde_du' => 0]);

        $resellers = $this->service->getResellersWithDebt();

        $this->assertCount(1, $resellers);
        $this->assertEquals(100000, $resellers->first()->solde_du);
    }

    // ─── getSalesChartData ────────────────────────────────────

    public function test_get_sales_chart_data_returns_array(): void
    {
        foreach (['week', 'month', 'year'] as $period) {
            $data = $this->service->getSalesChartData($period);
            $this->assertIsArray($data);
            $this->assertNotEmpty($data);
        }
    }

    public function test_sales_chart_data_has_label_and_amount_keys(): void
    {
        $data = $this->service->getSalesChartData('week');

        foreach ($data as $point) {
            $this->assertArrayHasKey('label', $point);
            $this->assertArrayHasKey('amount', $point);
        }
    }

    public function test_weekly_chart_returns_7_data_points(): void
    {
        $data = $this->service->getSalesChartData('week');
        $this->assertCount(7, $data);
    }

    public function test_yearly_chart_returns_12_data_points(): void
    {
        $data = $this->service->getSalesChartData('year');
        $this->assertCount(12, $data);
    }
}
