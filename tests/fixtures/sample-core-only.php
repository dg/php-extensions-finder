<?php

declare(strict_types=1);

// File that only uses core PHP functionality

$arr = [1, 2, 3];
$count = count($arr);
$str = implode(',', $arr);

class MyClass
{
	public function test(): string
	{
		return 'hello';
	}
}

$obj = new MyClass();
$result = $obj->test();

$date = new DateTime();
$reflection = new ReflectionClass(MyClass::class);
