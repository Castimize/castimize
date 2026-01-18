<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\DTO\Order\OrderDTO;
use App\Jobs\CreateOrderFromDTO;
use Codexshaper\WooCommerce\Facades\Order;
use Illuminate\Support\Facades\Bus;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrdersApiControllerTest extends TestCase
{
    #[Test]
    public function it_dispatches_create_order_from_dto_when_storing_wp_order(): void
    {
        Bus::fake();
        $this->withoutMiddleware();

        Mockery::mock('alias:App\\Services\\Admin\\LogRequestService')
            ->shouldReceive('addResponse')
            ->andReturnNull();

        $wpId = 1234;
        $logRequestId = 567;
        $fakeWpOrder = $this->makeFakeWpOrder(wpId: $wpId, orderNumber: 4321);

        Order::shouldReceive('find')
            ->with($wpId)
            ->andReturn($fakeWpOrder);

        $this->postJson(route('api.api.orders.store-order-wp'), [
            'id' => $wpId,
            'log_request_id' => $logRequestId,
        ])->assertCreated();

        Bus::assertDispatched(CreateOrderFromDTO::class, function (CreateOrderFromDTO $job) use ($wpId, $logRequestId): bool {
            return $job->logRequestId === $logRequestId
                && $job->orderDto instanceof OrderDTO
                && $job->orderDto->wpId === $wpId
                && $job->orderDto->source === 'wp';
        });
    }

    private function makeFakeWpOrder(int $wpId, int $orderNumber): object
    {
        $wpOrderArray = [
            'id' => $wpId,
            'customer_id' => 1,
            'number' => $orderNumber,
            'order_key' => 'wc_order_test',
            'status' => 'processing',
            'currency' => 'USD',
            'payment_method_title' => 'Test payment',
            'payment_method' => 'test',
            'customer_ip_address' => '127.0.0.1',
            'customer_user_agent' => 'PHPUnit',
            'customer_note' => null,
            'date_paid' => '2025-01-01T00:00:00',
            'date_created_gmt' => '2025-01-01T00:00:00',
            'date_modified_gmt' => '2025-01-01T00:00:00',
            'shipping_total' => '0.00',
            'shipping_tax' => '0.00',
            'discount_total' => '0.00',
            'discount_tax' => '0.00',
            'total' => '10.00',
            'total_tax' => '0.00',
            'tax_lines' => [],
            'meta_data' => [
                (object) [
                    'key' => '_payment_intent_id',
                    'value' => 'pi_test',
                ],
                (object) [
                    'key' => '_shipping_email',
                    'value' => 'shipping@example.com',
                ],
            ],
            'billing' => (object) [
                'first_name' => 'Piet',
                'last_name' => 'Tester',
                'company' => 'Castimize',
                'phone' => '+31612345678',
                'email' => 'billing@example.com',
                'address_1' => 'Teststraat 1',
                'address_2' => '',
                'postcode' => '1111AA',
                'city' => 'Amsterdam',
                'state' => 'NH',
                'country' => 'NL',
            ],
            'shipping' => (object) [
                'first_name' => 'Piet',
                'last_name' => 'Tester',
                'company' => 'Castimize',
                'phone' => '+31612345678',
                'address_1' => 'Teststraat 1',
                'address_2' => '',
                'postcode' => '1111AA',
                'city' => 'Amsterdam',
                'state' => 'NH',
                'country' => 'NL',
            ],
            'line_items' => [],
        ];

        return new class($wpOrderArray) implements \ArrayAccess, \JsonSerializable
        {
            public function __construct(private array $data) {}

            public function offsetExists(mixed $offset): bool
            {
                return array_key_exists($offset, $this->data);
            }

            public function offsetGet(mixed $offset): mixed
            {
                return $this->data[$offset] ?? null;
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                if ($offset === null) {
                    $this->data[] = $value;

                    return;
                }

                $this->data[$offset] = $value;
            }

            public function offsetUnset(mixed $offset): void
            {
                unset($this->data[$offset]);
            }

            public function toArray(): array
            {
                return $this->data;
            }

            public function jsonSerialize(): mixed
            {
                return $this->data;
            }
        };
    }
}
