<?php

namespace Xenokore\Utility\Helper;

class ClassHelper
{
    public static function getClassConstant(object $class, $value, ? string $prefix = null, bool $remove_prefix = true): ? string
    {
        $reflection = new \ReflectionClass($class);
        $constants  = $reflection->getConstants();

        if ($constants) {
            foreach ($constants as $constant_name => $constant_value) {
                if (!$prefix || StringHelper::startsWith($constant_name, $prefix)) {
                    if ($constant_value == $value) {
                        if ($prefix && $remove_prefix) {
                            return StringHelper::subtract($constant_name, StringHelper::length($prefix));
                        } else {
                            return $constant_name;
                        }
                    }
                }
            }
        }

        return null;
    }

    public static function getInfo(string $file_path): array
    {
        $return = [
            'class'     => null,
            'namespace' => null,
        ];

        if (!FileHelper::isAccessible($file_path)) {
            return $return;
        }

        // PHP 8 and above
        // @link https://stackoverflow.com/a/67099502
        if (\version_compare(PHP_VERSION, '8.0.0') >= 0) {

            $tokens    = \PhpToken::tokenize(\file_get_contents($file_path));

            for ($i = 0; $i < count($tokens); $i++) {
                if ($tokens[$i]->getTokenName() === 'T_NAMESPACE') {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j]->getTokenName() === 'T_NAME_QUALIFIED') {
                            $return['namespace'] = $tokens[$j]->text;
                            break;
                        }
                    }
                }

                if ($tokens[$i]->getTokenName() === 'T_CLASS') {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j]->getTokenName() === 'T_WHITESPACE') {
                            continue;
                        }

                        if ($tokens[$j]->getTokenName() === 'T_STRING') {
                            $return['class'] = $tokens[$j]->text;
                            break 2;
                        } else {
                            break;
                        }
                    }
                }
            }

        } else { // Below PHP 8

            $fp     = fopen($file_path, 'r');
            $buffer = '';
            $i      = 0;
            while (!$return['class']) {
                if (!$fp || feof($fp)) {
                    break;
                }

                $buffer .= fread($fp, 256);

                // Prefix and suffix the buffer to prevent unterminated comment warning
                $tokens = token_get_all('/**/' . $buffer . '/**/');

                if (strpos($buffer, '{') === false) {
                    continue;
                }

                for (; $i<count($tokens); $i++) {
                    if ($tokens[$i][0] === T_NAMESPACE) {
                        for ($j=$i+1; $j<count($tokens); $j++) {
                            if ($tokens[$j][0] === T_STRING) {
                                $return['namespace'] .= '\\'.$tokens[$j][1];
                            } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                                break;
                            }
                        }
                    }

                    if ($tokens[$i][0] === T_CLASS) {
                        for ($j=$i+1; $j<count($tokens); $j++) {
                            if ($tokens[$j] === '{') {
                                $return['class'] = $tokens[$i+2][1];
                            }
                        }
                    }
                }
            }

            if ($fp) {
                fclose($fp);
            }
        }

        return $return;
    }

    public static function getClass(string $file_path): ? string
    {
        return self::getInfo($file_path)['class'];
    }

    public static function getNamespace(string $file_path): ? string
    {
        return self::getInfo($file_path)['namespace'];
    }

    public static function getClassAndNamespace(string $file_path): ? string
    {
        $info   = self::getInfo($file_path);
        $string = $info['namespace'] . '\\' . $info['class'];
        $string = trim($string, '\\');
        return (!empty($string)) ? '\\' . $string : null;
    }

    public static function getMethodCode($class, string $method_name, bool $include_function_definition = false): ? string
    {
        // https://stackoverflow.com/a/50329308/5865844
        if (!$class || !$method_name) {
            return null;
        }

        $func = new \ReflectionMethod($class, $method_name);

        $filename   = $func->getFileName();
        $start_line = $func->getStartLine() - 1;
        $end_line   = $func->getEndLine();
        $length     = $end_line - $start_line;

        $body = '';

        try {
            $source = file($filename);
            $source = implode('', array_slice($source, 0, count($source)));
            $source = preg_split("/(\n|\r\n|\r)/", $source);

            for ($i = $start_line; $i < $end_line; $i++) {
                $body .= $source[$i] . PHP_EOL;
            }
        } catch (\Exception $ex) {
        }

        if ($include_function_definition) {
            return $body;
        } else {
            $result = preg_match_all('/{((?>[^{}]++|(?R))*)}/', $body, $matches);
            if ($result && !empty($matches[1])) {
                return $matches[1][0];
            }
        }

        return null;
    }

    /**
     * Call a private method of a class. Useful for testing internal workings.
     * @param object $object
     * @param string $method
     * @param array ...$arguments
     * @return void
     */
    public static function callPrivateMethod(object $object, string $method_name, array ...$arguments)
    {
        // Get class name
        $full_class_name = get_class($object);
        if (!$full_class_name) {
            return null;
        }

        try {

            // Get the method from the class
            $reflector = new \ReflectionClass($full_class_name);
            $method = $reflector->getMethod($method_name);

            // Set method to public
            $method->setAccessible(true);

            // Call the new method in the original class
            return $method->invokeArgs($object, $arguments);
        } catch (\Exception $ex) {
            return null;
        }
    }

    public static function getClassesAndFilepathsInDir(string $path, string $root_namespace = 'App', bool $relative_filepath = false, array $ignore_classes_ending_with = [])
    {
        $return = [];

        // Create CLASS => FILE array
        foreach(DirectoryHelper::tree($path, true) as $class_path){
            $class_full_name = $root_namespace . explode('.', $class_path)[0];

            // Ignore classes ending with a specific string
            if(!empty($ignore_classes_ending_with)){
                if(StringHelper::endsWith($class_full_name, $ignore_classes_ending_with)){
                    continue;
                }
            }

            if($relative_filepath){
                $return[$class_full_name] = $class_path;
            } else {
                $return[$class_full_name] = $path . $class_path;
            }
        }

        return $return;
    }

    public static function getClassesInDir(string $path, string $root_namespace = 'App', array $ignore_classes_ending_with = [])
    {
        $return = [];

        foreach(DirectoryHelper::tree($path, true) as $class_path){
            $class_full_name = $root_namespace . explode('.', $class_path)[0];

            // Ignore classes ending with a specific string
            if(!empty($ignore_classes_ending_with)){
                if(StringHelper::endsWith($class_full_name, $ignore_classes_ending_with)){
                    continue;
                }
            }

            $return[] = $class_full_name;
        }

        return $return;
    }
}
