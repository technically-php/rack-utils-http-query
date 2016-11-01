<?php

use TechnicallyPhp\RailsHttpQuery;

class RailsHttpQueryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_omit_indexes_for_numeric_arrays()
    {
        $value = ['ids' => [1, 2, 3]];
        $this->assertQueryString('ids[]=1&ids[]=2&ids[]=3', $value);
    }

    /**
     * @test
     */
    public function it_should_not_omit_gapped_numeric_indexes()
    {
        $value = ['ids' => [0 => 1, 1 => 2, 4 => 3]];
        $this->assertQueryString('ids[]=1&ids[]=2&ids[4]=3', $value);
    }

    /**
     * @test
     */
    public function it_should_not_omit_string_indexes()
    {
        $value = ['ids' => ['min' => 10, 'max' => 100, 'avg' => 55]];
        $this->assertQueryString('ids[min]=10&ids[max]=100&ids[avg]=55', $value);
    }

    private function assertQueryString($expected, $data, $message = null)
    {
        $encoded = RailsHttpQuery::build($data);
        $this->assertEquals($expected, $encoded, $message);
    }
}
