# Rails HTTP query

Tiny package to build HTTP URL query string compatible with Rails app.

# Usage

This library produces output without explicit indices. As simple as that.
   
``` php
$vars = ['fruits' => ['apple', 'banana', 'orange']];
$query_string = \TechnicallyPhp\RailsHttpQuery::build($vars);
var_dump($query_string); 
// will output: "fruits[]=apple&fruits[]=banana&fruits[]=orange" (urlencoded)
```

# Motivation

PHP stock [`http_build_query()`](http://php.net/manual/en/function.http-build-query.php) 
converts array into items with indices explicitly defined. 
This is not compatible with the way Rails applications parse requests. 
     
``` php
$vars = ['fruits' => ['apple', 'banana', 'orange']];
$query_string = http_build_query($vars);
var_dump($query_string); 
// will output: "fruits[0]=apple&fruits[1]=banana&fruits[2]=orange" (urlencoded) 
```

