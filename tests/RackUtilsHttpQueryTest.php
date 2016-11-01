<?php

use TechnicallyPhp\RackUtilsHttpQuery;

class RackUtilsHttpQueryTest extends PHPUnit_Framework_TestCase
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

            'foo[]'                => ['foo' => [null]],
            'foo[]='               => ['foo' => ['']],
            'foo[]=bar'            => ['foo' => ['bar']],
            'foo[]=bar&foo%5B'     => ['foo' => ['bar'], 'foo[' => null],
            'foo[]=bar&foo%5B=baz' => ['foo' => ['bar'], 'foo[' => 'baz'],
            'foo[]=bar&foo[]'      => ['foo' => ['bar', null]],
            'foo[]=bar&foo[]='     => ['foo' => ['bar', '']],

            'foo[]=1&foo[]=2'                   => ['foo' => ['1', '2']],
            'foo=bar&baz[]=1&baz[]=2&baz[]=3'   => ['foo' => 'bar', 'baz' => ['1', '2', '3']],
            'foo[]=bar&baz[]=1&baz[]=2&baz[]=3' => ['foo' => ['bar'], 'baz' => ['1', '2', '3']],

            'x[y][z]=1'               => ['x' => ['y' => ['z' => '1']]],
            'x[y][z][]=1'             => ['x' => ['y' => ['z' => ['1']]]],
            'x[y][z][]=1&x[y][z][]=2' => ['x' => ['y' => ['z' => ['1', '2']]]],

            'x[y][][z]=1'             => ['x' => ['y' => [['z' => '1']]]],
            'x[y][][z][]=1'           => ['x' => ['y' => [['z' => ['1']]]]],
            'x[y][][z]=1&x[y][][w]=2' => ['x' => ['y' => [['z' => '1', 'w' => '2']]]],

            'x[y][][v][w]=1'             => ['x' => ['y' => [['v' => ['w' => '1']]]]],
            'x[y][][z]=1&x[y][][v][w]=2' => ['x' => ['y' => [['z' => '1', 'v' => ['w' => '2']]]]],

            'x[y][][z]=1&x[y][][z]=2'                         => ['x' => ['y' => [['z' => '1'], ['z' => '2']]]],
            'x[y][][z]=1&x[y][][w]=a&x[y][][z]=2&x[y][][w]=3' => ['x' => ['y' => [['z' => '1', 'w' => 'a'], ['z' => '2', 'w' => '3']]]],

            'x[][y]=1&x[][z][w]=a&x[][y]=2&x[][z][w]=b' => ['x' => [['y' => '1', 'z' => ['w' => 'a']], ['y' => '2', 'z' => ['w' => 'b']]]],

            'data[books][][data][page]=1&data[books][][data][page]=2' => [
                'data' => [
                    'books' => [
                        ['data' => ['page' => '1']],
                        ['data' => ['page' => '2']],
                    ],
                ],
            ],

            'x[][y][][z]=1&x[][y][][w]=2' => ['x' => [['y' => [['z' => '1', 'w' => '2']]]]],

            'x[][id]=1&x[][y][a]=5&x[][y][b]=7&x[][z][id]=3&x[][z][w]=0&x[][id]=2&x[][y][a]=6&x[][y][b]=8&x[][z][id]=4&x[][z][w]=0' => [
                'x' => [
                    ['id' => '1', 'y' => ['a' => '5', 'b' => '7'], 'z' => ['id' => '3', 'w' => '0']],
                    ['id' => '2', 'y' => ['a' => '6', 'b' => '8'], 'z' => ['id' => '4', 'w' => '0']],
                ],
            ],
        ];

        $data_set = [];
        foreach ($test_cases as $expected => $data) {
            $data_set[$expected] = [$expected, $data];
        }
        return $data_set;
    }

    private function assertQueryString($expected, $data, $message = null)
    {
        $encoded = RackUtilsHttpQuery::build($data);
        $this->assertEquals($expected, $encoded, $message);
    }
}
