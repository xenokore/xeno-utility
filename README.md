Xeno Utility Library
====================

A library filled with utility functions.


## ArrayHelper

************************************************************************************************

```php
ArrayHelper::set(array &$array, string $key, mixed $value): array
```
Set an array item to a given value using "dot" notation.  
If the given key is null, the entire array will be replaced.
Returns the new array.

************************************************************************************************

```php
ArrayHelper::get(array $array, string $key, $default = null, bool $throw = false): mixed
```
Get an item from an array using "dot" notation.  
If the `$throw` argument is set to true an `ArrayKeyNotFoundException` is
thrown instead of returning the default value.

************************************************************************************************

```php
ArrayHelper::isAccessible(mixed $value): bool
```
Determine whether a given value is *array accessible*.  
Checks if the value is an instance of `\ArrayAccess`.

************************************************************************************************

```php
ArrayHelper::exists(array $array, string|int $key)
```
Determine if a given key exists in the provided array.

************************************************************************************************

```php
ArrayHelper::convertDotNotationToArray(array $array): array
```
Creates a multidimensional array based on an array of dotnotation keys.  
Useful for configuration arrays.

Example:
```
[
    'a.b.c' => 'value'
]
```
Turns into
```
[
    'a' => [
        'b' => [
            'c' => 'value'
        ]
    ]
]
```

************************************************************************************************

```php
ArrayHelper::convertArrayToDotNotation(array $array): array
```
Convert multidimensional array to 2D array with dotnotation keys.  
https://stackoverflow.com/a/10424516/5865844  
Useful for configuration arrays.

Example:
```
[
    'a' => [
        'b' => [
            'c' => 'value'
        ]
    ]
]
```
Turns into
```
[
    'a.b.c' => 'value'
]
```

************************************************************************************************

```php
ArrayHelper::moveToTop(array &$array, string $key): void
```
Move an array item to the start of the array.

************************************************************************************************

```php
ArrayHelper::moveToBottom(array &$array, string $key): void
```
Move an array item to the end of the array.

************************************************************************************************

```php
ArrayHelper::mergeRecursiveDistinct(array $array1, array $array2): array
```
Merge 2 arrays recursively and replaces distinct non-array values.

************************************************************************************************

```php
ArrayHelper::getChecksum(array $array, bool $sort = false): ?string
```
Get a checksum of the given array.

************************************************************************************************

```php
ArrayHelper::getValueBasedOnCurrentDay(array $array): mixed
```
Gets a value from an array based on the current day.
Each day the value shifts to the next one.
Maximum array size is around 20 million. (`Ymd` as an int).

************************************************************************************************

## ClassHelper

```php
ClassHelper::getClassConstant(object $class, $value, ?string $prefix = null, bool $remove_prefix = true): ?string
```
Get the name of a constant based on value.  
Can be given a prefix to narrow the search.

************************************************************************************************

```php
ClassHelper::getClassInfoFromFile(string $file_path): array
```
Get the info of a class by filepath.  
Returns an array with the class name and the full namespace.  
Does not use reflection and does not initiate the class.

************************************************************************************************

```php
ClassHelper::getClassNameFromFile(string $file_path): ?string
```
Get the class name from a file containing a class.  
The namespace is not included.

************************************************************************************************

```php
ClassHelper::getNamespaceFromFile(string $file_path): ?string
```
Get the namespace from a file containing a class.

************************************************************************************************

```php
ClassHelper::getFullClassNameFromFile(string $file_path): ?string
```
Get the full class name from a file containing a class.
The returned string contains the namespace and class name.

************************************************************************************************

```php
ClassHelper::getMethodCodeFromClass(object $class, string $method_name, bool $include_function_definition = false): ?string
```
Get the raw code from a method of a class.

************************************************************************************************

```php
ClassHelper::callPrivateMethod(object $class, string $method_name, array ...$arguments): mixed
```
Call a private method of a class.  
Useful for unit testing internal workings.

************************************************************************************************

## DirectoryHelper
