# RackUtils HTTP query

Tiny package to build HTTP URL query string compatible with Rails app.

# Usage

This library produces output without explicit indices. As simple as that.
   
``` php
$vars = ['fruits' => ['apple', 'banana', 'orange']];
$query_string = \TechnicallyPhp\RackUtilsHttpQuery::build($vars);
var_dump($query_string); 
// will output: "fruits[]=apple&fruits[]=banana&fruits[]=orange"
```

# Motivation

PHP stock [`http_build_query()`](http://php.net/manual/en/function.http-build-query.php) 
converts array into items with indices explicitly defined. 
This is not compatible with the way Rails applications parse requests. 
     
``` php
$vars = ['fruits' => ['apple', 'banana', 'orange']];
$query_string = http_build_query($vars);
var_dump($query_string); 
// will output: "fruits[0]=apple&fruits[1]=banana&fruits[2]=orange"
```

Relevant StackOverflow discussions:

1. [php url query nested array with no index](http://stackoverflow.com/questions/11996573/php-url-query-nested-array-with-no-index)

# Details

This package follows 
`Rack::Utils.parse_nested_query` [specification](https://github.com/rack/rack/blob/master/test/spec_utils.rb).
Please check [RackUtilsHttpQueryTest.php](./tests/RackUtilsHttpQueryTest.php#L59).
