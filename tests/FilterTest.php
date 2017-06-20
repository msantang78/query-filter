<?php

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Msantang\QueryFilters\Filter;

class FilterTest extends TestCase
{
    protected $filter;

    public function setUp()
    {
        $definition = [
            'id' => 'numeric:eq',
        ];
        $this->filter = new Filter($definition);
    }

    public function testApplyFilter()
    {
        $mapped_data = ['id' => [[1]]];

        // Mock filter input
        $filter_input = m::mock('Msantang\QueryFilters\FilterInput');
        $filter_input->shouldReceive('getMappedData')
            ->once()
            ->andReturn($mapped_data);

        // mock query
        $query = m::mock(Illuminate\Database\Eloquent\Builder::class);
        $query->shouldReceive('where')
            ->once()
            ->with('id', 1)
            ->andReturn($query);

        $this->filter->apply($query, $filter_input);
    }

    public function tearDown()
    {
        m::close();
    }
}
