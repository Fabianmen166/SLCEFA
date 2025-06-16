<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Quote;
use App\Models\User;
use App\Models\Customer;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\QuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear datos de prueba necesarios
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user'
        ]);

        $this->customer = Customer::factory()->create([
            'solicitante' => 'Test Company',
            'contacto' => 'John Doe',
            'nit' => '123456789',
            'correo' => 'company@example.com'
        ]);

        $this->service = Service::factory()->create([
            'descripcion' => 'Test Service',
            'precio' => 100.00,
            'acreditado' => true
        ]);

        $this->servicePackage = ServicePackage::factory()->create([
            'nombre' => 'Test Package',
            'precio' => 500.00,
            'acreditado' => true,
            'included_services' => json_encode([$this->service->services_id])
        ]);
    }

    /** @test */
    public function it_can_create_a_quote()
    {
        $quote = Quote::create([
            'quote_id' => 'Q-001',
            'user_id' => $this->user->user_id,
            'customers_id' => $this->customer->customers_id,
            'total' => 0
        ]);

        $this->assertInstanceOf(Quote::class, $quote);
        $this->assertEquals('Q-001', $quote->quote_id);
        $this->assertEquals($this->user->user_id, $quote->user_id);
        $this->assertEquals($this->customer->customers_id, $quote->customers_id);
    }

    /** @test */
    public function it_can_add_services_to_quote()
    {
        $quote = Quote::create([
            'quote_id' => 'Q-002',
            'user_id' => $this->user->user_id,
            'customers_id' => $this->customer->customers_id,
            'total' => 0
        ]);

        $quoteService = QuoteService::create([
            'quote_id' => $quote->quote_id,
            'services_id' => $this->service->services_id,
            'cantidad' => 2,
            'subtotal' => 200.00,
            'unit_index' => 1
        ]);

        $this->assertCount(1, $quote->quoteServices);
        $this->assertEquals($this->service->services_id, $quote->quoteServices->first()->services_id);
        $this->assertEquals(2, $quote->quoteServices->first()->cantidad);
    }

    /** @test */
    public function it_can_add_service_packages_to_quote()
    {
        $quote = Quote::create([
            'quote_id' => 'Q-003',
            'user_id' => $this->user->user_id,
            'customers_id' => $this->customer->customers_id,
            'total' => 0
        ]);

        $quoteService = QuoteService::create([
            'quote_id' => $quote->quote_id,
            'service_packages_id' => $this->servicePackage->service_packages_id,
            'cantidad' => 1,
            'subtotal' => 500.00,
            'unit_index' => 1
        ]);

        $this->assertCount(1, $quote->quoteServices);
        $this->assertEquals($this->servicePackage->service_packages_id, $quote->quoteServices->first()->service_packages_id);
        $this->assertEquals(1, $quote->quoteServices->first()->cantidad);
    }

    /** @test */
    public function it_can_calculate_total_with_services_and_packages()
    {
        $quote = Quote::create([
            'quote_id' => 'Q-004',
            'user_id' => $this->user->user_id,
            'customers_id' => $this->customer->customers_id,
            'total' => 0
        ]);

        // Agregar un servicio
        QuoteService::create([
            'quote_id' => $quote->quote_id,
            'services_id' => $this->service->services_id,
            'cantidad' => 2,
            'subtotal' => 200.00,
            'unit_index' => 1
        ]);

        // Agregar un paquete
        QuoteService::create([
            'quote_id' => $quote->quote_id,
            'service_packages_id' => $this->servicePackage->service_packages_id,
            'cantidad' => 1,
            'subtotal' => 500.00,
            'unit_index' => 2
        ]);

        $total = $quote->quoteServices->sum('subtotal');
        $quote->total = $total;
        $quote->save();

        $this->assertEquals(700.00, $quote->total);
    }

    /** @test */
    public function it_can_retrieve_related_data()
    {
        $quote = Quote::create([
            'quote_id' => 'Q-005',
            'user_id' => $this->user->user_id,
            'customers_id' => $this->customer->customers_id,
            'total' => 0
        ]);

        $this->assertInstanceOf(User::class, $quote->user);
        $this->assertInstanceOf(Customer::class, $quote->customer);
        $this->assertEquals($this->user->name, $quote->user->name);
        $this->assertEquals($this->customer->solicitante, $quote->customer->solicitante);
    }
} 