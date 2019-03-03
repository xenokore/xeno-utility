<?php

namespace Xenokore\Utility\Tests;

use Xenokore\Utility\Helper\StringHelper;
use Xenokore\Utility\Exception\DirectoryNotAccessibleException;

use PHPUnit\Framework\TestCase;

class StringHelperTest extends TestCase
{
    public function testGenerate()
    {
        $string = StringHelper::generate(12);

        $this->assertIsString($string);
        $this->assertTrue(mb_strlen($string) === 12);

        $string = StringHelper::generate(5, "A");

        $this->assertIsString($string);
        $this->assertTrue(mb_strlen($string) === 5);
        $this->assertEquals("AAAAA", $string);
    }

    public function testContains()
    {
        $this->assertIsBool(StringHelper::contains("a", "a"));

        $this->assertTrue(StringHelper::contains("abcde", "cd"));
        $this->assertFalse(StringHelper::contains("abcde", "xyz"));
    }

    public function testReplace()
    {
        $this->assertIsString(StringHelper::replace("", []));

        $this->assertEquals(
            "hello test",
            StringHelper::replace(
                "byebye world",
                [
                    "byebye" => "hello",
                    "test" => "world"
                ]
            )
        );
    }

    public function testMatch()
    {
        $this->assertIsBool(StringHelper::match("", ""));

        $this->assertTrue(StringHelper::match("abc", "abc"));

        $this->assertTrue(StringHelper::match("abc/def", "abc/*"));

        $this->assertFalse(StringHelper::match("abc/def", "def/*"));
    }

    public function testStartsWith()
    {
        $this->assertIsBool(StringHelper::startsWith("", ""));

        // Case insensitive
        $this->assertTrue(StringHelper::startsWith("abcdef", "abc"));
        $this->assertFalse(StringHelper::startsWith("abcdef", "def"));

        $this->assertTrue(StringHelper::startsWith("abcdef", ["c", "ab"]));
        $this->assertFalse(StringHelper::startsWith("abcdef", ["c", "def"]));

        // Case sensitive
        $this->assertTrue(StringHelper::startsWith("ABCDEF", "ABC", true));
        $this->assertFalse(StringHelper::startsWith("ABCDEF", "abc", true));

        $this->assertTrue(StringHelper::startsWith("ABCDEF", ["DEF", "ABC"], true));
        $this->assertFalse(StringHelper::startsWith("abcdef", ["DEF", "ABC"], true));
    }

    public function testEndsWith()
    {
        $this->assertIsBool(StringHelper::endsWith("", ""));

        // Case insensitive
        $this->assertTrue(StringHelper::endsWith("abcdef", "def"));
        $this->assertFalse(StringHelper::endsWith("abcdef", "abc"));

        $this->assertTrue(StringHelper::endsWith("abcdef", ["c", "ef"]));
        $this->assertFalse(StringHelper::endsWith("abcdef", ["c", "abc"]));

        // Case sensitive
        $this->assertTrue(StringHelper::endsWith("ABCDEF", "DEF", true));
        $this->assertFalse(
            StringHelper::endsWith("ABCDEF", "def", true)
        );

        $this->assertTrue(StringHelper::endsWith("ABCDEF", ["ABC", "DEF"], true));
        $this->assertFalse(StringHelper::endsWith("abcdef", ["ABC", "DEF"], true));
    }

    public function testCamelize()
    {
        $this->assertIsString(StringHelper::camelize("test_subject"));

        $this->assertEquals("TestSubject", StringHelper::camelize("test_subject"));

        $this->assertEquals("Test_subject", StringHelper::camelize("test_subject", "-"));

        $this->assertEquals("TestSubject", StringHelper::camelize("test-subject", "-"));
    }

    public function testLength()
    {
        // TODO: this should test Unicode strings where strlen() would fail and mb_strlen() must be used

        $this->assertIsInt(StringHelper::length("abcdef"));

        $this->assertEquals(6, StringHelper::length("abcdef"));
    }

    public function testSubtract()
    {
        // TODO: this should test Unicode strings where substr() would fail and mb_substr() must be used
        $this->assertIsString(StringHelper::subtract("abcdef"));

        $this->assertEquals("def", StringHelper::subtract("abcdef", 3));

        $this->assertEquals("abc", StringHelper::subtract("abcdef", 0, 3));

        $this->assertEquals("bcde", StringHelper::subtract("abcdef", 1, 4));
    }
}
