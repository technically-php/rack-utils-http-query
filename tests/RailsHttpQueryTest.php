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
    public function it_should_escape_values()
    {
        $value = ['ids' => ['[]', '&', '?']];
        $this->assertQueryString('ids[]=%5B%5D&ids[]=%26&ids[]=%3F', $value);
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


    /**
     * @test
     * @param string $expected
     * @param array $data
     * @dataProvider rack_utils_parse_nested_query_test_cases
     */
    public function it_should_pass_rack_utils_parse_nested_query_test_cases($expected, $data)
    {
        $this->assertQueryString($expected, $data);
    }

    /**
     * @see https://github.com/rack/rack/blob/master/test/spec_utils.rb
     * @see https://github.com/rack/rack/blob/100745eeb069578aba2ab18969bfb845e880ab8e/test/spec_utils.rb
     */
    public static function rack_utils_parse_nested_query_test_cases()
    {
        $test_cases = [
            'foo'           => ['foo' => null],
            'foo='          => ['foo' => ''],
            'foo=bar'       => ['foo' => 'bar'],
            'foo=%22bar%22' => ['foo' => '"bar"'],

            'foo=1&bar=2'  => ['foo' => 1, 'bar' => 2],
            'foo&bar='     => ['foo' => null, 'bar' => ''],
            'foo=bar&baz=' => ['foo' => 'bar', 'baz' => ''],

            'my+weird+field=q1%212%22%27w%245%267%2Fz8%29%3F' => ['my weird field' => "q1!2\"'w$5&7/z8)?"],
            'a=b&pid%3D1234=1023'                             => ['a' => 'b', 'pid=1234' => '1023'],
        ];

        $data_set = [];
        foreach ($test_cases as $expected => $data) {
            $data_set[$expected] = [$expected, $data];
        }
        return $data_set;
    }

    private function assertQueryString($expected, $data, $message = null)
    {
        $encoded = RailsHttpQuery::build($data);
        $this->assertEquals($expected, $encoded, $message);
    }
}
