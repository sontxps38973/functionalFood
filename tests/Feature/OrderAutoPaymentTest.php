<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class OrderAutoPaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Tạo admin user
        $this->admin = Admin::factory()->create([
            'role' => 'admin'
        ]);
        
        // Tạo user thường
        $this->user = User::factory()->create();
        
        // Tạo đơn hàng COD
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => 'pending',
            'total' => 100000
        ]);
    }

    /** @test */
    public function it_auto_completes_payment_when_order_is_delivered()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/v1/admin/orders/{$this->order->id}/status", [
                'status' => 'delivered'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'auto_payment' => true
                ]
            ]);

        $this->order->refresh();
        
        $this->assertEquals('paid', $this->order->payment_status);
        $this->assertNotNull($this->order->paid_at);
        $this->assertStringStartsWith('AUTO_', $this->order->payment_transaction_id);
    }

    /** @test */
    public function it_auto_completes_payment_when_order_is_confirmed()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/v1/admin/orders/{$this->order->id}/status", [
                'status' => 'confirmed'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'auto_payment' => true
                ]
            ]);

        $this->order->refresh();
        
        $this->assertEquals('paid', $this->order->payment_status);
        $this->assertNotNull($this->order->paid_at);
    }

    /** @test */
    public function it_does_not_auto_complete_payment_for_online_payment()
    {
        $this->order->update(['payment_method' => 'online_payment']);

        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/v1/admin/orders/{$this->order->id}/status", [
                'status' => 'delivered'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'auto_payment' => false
                ]
            ]);

        $this->order->refresh();
        
        $this->assertEquals('pending', $this->order->payment_status);
        $this->assertNull($this->order->paid_at);
    }

    /** @test */
    public function it_does_not_auto_complete_payment_for_already_paid_orders()
    {
        $this->order->update(['payment_status' => 'paid']);

        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/v1/admin/orders/{$this->order->id}/status", [
                'status' => 'delivered'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'auto_payment' => false
                ]
            ]);

        $this->order->refresh();
        
        $this->assertEquals('paid', $this->order->payment_status);
    }

    /** @test */
    public function it_updates_tracking_number_when_provided()
    {
        $trackingNumber = 'GHN123456789';
        
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/v1/admin/orders/{$this->order->id}/status", [
                'status' => 'shipped',
                'tracking_number' => $trackingNumber
            ]);

        $response->assertStatus(200);

        $this->order->refresh();
        
        $this->assertEquals($trackingNumber, $this->order->tracking_number);
        $this->assertEquals('shipped', $this->order->status);
        $this->assertNotNull($this->order->shipped_at);
    }

    /** @test */
    public function it_updates_admin_notes_when_provided()
    {
        $note = 'Ghi chú admin cho đơn hàng';
        
        $response = $this->actingAs($this->admin, 'admin')
            ->putJson("/api/v1/admin/orders/{$this->order->id}/status", [
                'status' => 'confirmed',
                'note' => $note
            ]);

        $response->assertStatus(200);

        $this->order->refresh();
        
        $this->assertEquals($note, $this->order->admin_notes);
    }
}
