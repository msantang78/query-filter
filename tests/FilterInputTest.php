<?php

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Msantang\QueryFilters\FilterInput;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FilterInputTest extends TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
    {
        m::close();
    }

    public function testConstructorFromArray()
    {
        $filter_input = FilterInput::fromArray(['id' => 1]);

        $this->assertInstanceOf(FilterInput::class, $filter_input);
    }

    public function testConstructorFromRequest()
    {
        $filter_input = FilterInput::fromRequest();

        $this->assertInstanceOf(FilterInput::class, $filter_input);
    }

    /**
     * Test Validation.
     */
    public function testValidate()
    {
        $filter_input = FilterInput::fromArray(['id' => '1'], ['id' => 'id'], null, ['id' => 'integer']);

        $validatorMock = m::mock('Illuminate\Validation\Validator');
        $validatorMock->shouldReceive('fails')
            ->once()
            ->andReturn(false);

        Validator::shouldReceive('make')
            ->once()
            ->with(['id' => '1'], ['id' => 'integer'])
            ->andReturn($validatorMock);

        $this->assertTrue($filter_input->validate());
    }

    public function testValidateOrFail()
    {
        $filter_input = FilterInput::fromArray(['id' => 'string'], ['id' => 'id'], null, ['id' => 'integer']);

        $validatorMock = m::mock('Illuminate\Validation\Validator');
        $validatorMock->shouldReceive('fails')
            ->once()
            ->andReturn(true);

        Validator::shouldReceive('make')
            ->once()
            ->with(['id' => 'string'], ['id' => 'integer'])
            ->andReturn($validatorMock);

        $this->expectException(ValidationException::class);

        $filter_input->validateOrFail();
    }

    /**
     * @dataProvider InputProvider
     */
    public function testMappedData($data, $mapping, $mapped)
    {
        $filter_input = FilterInput::fromArray($data, $mapping);
        $this->assertEquals($filter_input->getMappedData(), $mapped);
    }

    public function testDefaultMappedData()
    {
        $data = [
            'id'       => 1,
            'name'     => 'martin',
            'name_opt' => 'eq',
        ];

        $mapped = [
            'id' => [
                0 => [0 => 1],
            ],
            'name' => [
                0 => [
                  0 => 'martin',
                  1 => 'eq',
                ],
            ],
        ];

        $filter_input = FilterInput::fromArray($data);
        $this->assertEquals($filter_input->getMappedData(), $mapped);
    }

    public function InputProvider()
    {
        return [
            [
                ['byid' => '1'],  // input
                ['id' => 'byid'], // mapping
                ['id' => [[1]]],   // correct answer
            ], [
                ['byid' => '1', 'byid_opt' => 'eq'],  // input
                ['id' => 'byid'],                     // mapping
                ['id' => [[1, 'eq']]],                 // correct answer
            ], [
                ['byid' => '1', 'byid_opt' => 'eq', 'idmax' => '2'],  // input
                ['id' => 'byid|idmax'],                               // mapping mutiple filter to one field
                ['id' => [[1, 'eq'], [2]]],                            // correct answer
            ], [
                ['byid' => '1', 'byid_opt' => 'eq', 'idmax' => '2', 'idmax_opt' => 'neq'],  // input
                ['id' => 'byid|idmax'],                                                     // mapping
                ['id' => [[1, 'eq'], [2, 'neq']]],                                           // correct answer
            ], [
                ['byid' => '1', 'byid_opt' => 'eq', 'idmax' => '2'],  // input
                ['id' => 'byid'],                                     // mapping
                ['id' => [[1, 'eq']]],                                 // correct answer
            ], [
                ['userrole' => 'admin', 'userrole_opt' => 'eq'],  // input
                ['role.name' => 'userrole'],                      // mapping
                ['role.name' => [['admin', 'eq']]],                // correct answer
            ],
        ];
    }
}
