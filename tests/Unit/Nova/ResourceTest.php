<?php

declare(strict_types=1);

namespace Tests\Unit\Nova;

use App\Models\Order;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Wildside\Userstamps\Userstamps;

// Test model with editor relationship (has Userstamps)
class ModelWithEditor extends Model
{
    use Userstamps;
}

// Test model without editor relationship (no Userstamps)
class ModelWithoutEditor extends Model
{
    // No editor method
}

class ResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_eager_loads_editor_and_creator_when_model_has_userstamps(): void
    {
        $modelWithEditor = new ModelWithEditor;

        $this->assertTrue(
            method_exists($modelWithEditor, 'editor'),
            'ModelWithEditor should have editor method from Userstamps trait'
        );
        $this->assertTrue(
            method_exists($modelWithEditor, 'creator'),
            'ModelWithEditor should have creator method from Userstamps trait'
        );

        /** @var Builder|MockInterface $query */
        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($modelWithEditor);
        $query->shouldReceive('with')->with(['editor', 'creator'])->once()->andReturnSelf();

        /** @var NovaRequest|MockInterface $request */
        $request = Mockery::mock(NovaRequest::class);
        $request->shouldReceive('has')->with('orderBy')->andReturn(false);

        $result = Resource::indexQuery($request, $query);

        $this->assertSame($query, $result);
    }

    #[Test]
    public function it_does_not_eager_load_when_model_lacks_userstamps(): void
    {
        $modelWithoutEditor = new ModelWithoutEditor;

        $this->assertFalse(
            method_exists($modelWithoutEditor, 'editor'),
            'ModelWithoutEditor should not have editor method'
        );
        $this->assertFalse(
            method_exists($modelWithoutEditor, 'creator'),
            'ModelWithoutEditor should not have creator method'
        );

        /** @var Builder|MockInterface $query */
        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($modelWithoutEditor);
        $query->shouldNotReceive('with');

        /** @var NovaRequest|MockInterface $request */
        $request = Mockery::mock(NovaRequest::class);
        $request->shouldReceive('has')->with('orderBy')->andReturn(false);

        $result = Resource::indexQuery($request, $query);

        $this->assertSame($query, $result);
    }

    #[Test]
    public function it_eager_loads_userstamps_for_order_model(): void
    {
        // Order model uses Userstamps trait which provides editor and creator relationships
        $orderModel = new Order;

        $this->assertTrue(
            method_exists($orderModel, 'editor'),
            'Order model should have editor method from Userstamps trait'
        );
        $this->assertTrue(
            method_exists($orderModel, 'creator'),
            'Order model should have creator method from Userstamps trait'
        );

        /** @var Builder|MockInterface $query */
        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($orderModel);
        $query->shouldReceive('with')->with(['editor', 'creator'])->once()->andReturnSelf();

        /** @var NovaRequest|MockInterface $request */
        $request = Mockery::mock(NovaRequest::class);
        $request->shouldReceive('has')->with('orderBy')->andReturn(false);

        Resource::indexQuery($request, $query);
    }
}
