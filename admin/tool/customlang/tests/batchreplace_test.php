<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * CLI customlang import tool.
 *
 * @package    tool_customlang
 * @copyright  2020 Scott Verbeek <scottverbeek@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/customlang/locallib.php');

/**
 * Class for customlang batchreplace functions tests.
 *
 * @package    tool_customlang
 * @copyright  2020 Scott Verbeek <scottverbeek@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class tool_customlang_testcase extends advanced_testcase {

    /**
     * Data provider for {@see self::test_replace_string_group_with_hash()}.
     *
     * @return array List of data sets - (string) data set name => (array) data
     */
    public function replace_string_group_with_hash_provider(): array {
        return [
            'Basic test' => [
                'subject'        => 'This is a {test}',
                'openingbracket' => '{',
                'closingbracket' => '}',
                'expectedstring' => 'This is a 850c381493d20b719b662abac4e6f4fc',
                'hashvalues'     => ['850c381493d20b719b662abac4e6f4fc' => '{test}']
            ],
            'Test with two replacements' => [
                'subject'        => 'This is {test}a second {test}',
                'openingbracket' => '{',
                'closingbracket' => '}',
                'expectedstring' => 'This is 850c381493d20b719b662abac4e6f4fca second 850c381493d20b719b662abac4e6f4fc',
                'hashvalues'     => ['850c381493d20b719b662abac4e6f4fc' => '{test}']
            ],
            'Test with two braces' => [
                'subject'        => 'This is (test)a second (test)',
                'openingbracket' => '(',
                'closingbracket' => ')',
                'expectedstring' => 'This is 9fc8f9a51b6551b2422d599e075f0cada second 9fc8f9a51b6551b2422d599e075f0cad',
                'hashvalues'     => ['9fc8f9a51b6551b2422d599e075f0cada' => '(test)']
            ],
            'Test with with no group' => [
                'subject'        => 'This is a test',
                'openingbracket' => '(',
                'closingbracket' => ')',
                'expectedstring' => 'This is a test',
                'hashvalues'     => []
            ],
            'Test with with no group but thas brackets' => [
                'subject'        => 'This is a ((te)st',
                'openingbracket' => '(',
                'closingbracket' => ')',
                'expectedstring' => 'This is a ((te)st',
                'hashvalues'     => []
            ],
            'Test with a single closing bracket' => [
                'subject'        => 'This ]is a test',
                'openingbracket' => '[',
                'closingbracket' => ']',
                'expectedstring' => 'This ]is a test',
                'hashvalues'     => []
            ],
            'Test with a some special brackets' => [
                'subject'        => 'This is qa testp',
                'openingbracket' => 'q',
                'closingbracket' => 'p',
                'expectedstring' => 'This is ee552abfd8f8ba6a606e8e55a7e97025',
                'hashvalues'     => ['ee552abfd8f8ba6a606e8e55a7e97025' => 'qa testp']
            ]
        ];
    }

    /**
     * Data provider for {@see self::test_replace_hash_to_string()}.
     *
     * @return array List of data sets - (string) data set name => (array) data
     */
    public function replace_hash_to_string_provider(): array {
        return [
            'Basic test' => [
                'subject'        => 'This is a 850c381493d20b719b662abac4e6f4fc',
                'hashvalues'     => ['850c381493d20b719b662abac4e6f4fc' => '{test}'],
                'expectedstring' => 'This is a {test}',
            ],
            'Test with more to reset' => [
                'subject'        => 'This is a 850c381493d20b719b662abac4e6f4fc and another850c381493d20b719b662abac4e6f4fc',
                'hashvalues'     => ['850c381493d20b719b662abac4e6f4fc' => '{test}'],
                'expectedstring' => 'This is a {test} and another{test}'
            ],
            'Test with no hash values' => [
                'subject'        => 'Test',
                'hashvalues'     => ['850c381493d20b719b662abac4e6f4fc' => '{test}'],
                'expectedstring' => 'Test'
            ]
        ];
    }

    /**
     * Data provider for {@see self::test_is_safe_to_replace()}.
     *
     * @return array List of data sets - (string) data set name => (array) data
     */
    public function is_safe_to_replace_provider(): array {
        return [
            'Basic test' => [
                'subject'        => 'Cool to replace',
                'search'         => 'to',
                'expectedreturn' => true
            ],
            'Test with {' => [
                'subject'        => 'Cool to { replace',
                'search'         => 'to',
                'expectedreturn' => true
            ],
            'Test with }' => [
                'subject'        => 'Cool to } replace',
                'search'         => 'to',
                'expectedreturn' => true
            ],
            'Test with {...}' => [
                'subject'        => 'Uncool{ to } replace',
                'search'         => 'to',
                'expectedreturn' => false
            ],
            'Test with %...%' => [
                'subject'        => '%cool% to replace',
                'search'         => 'to',
                'expectedreturn' => true
            ],
            'Test with %...% unsuccesfull' => [
                'subject'        => 'Uncool %to% replace',
                'search'         => 'to',
                'expectedreturn' => false
            ],
            'Test with link' => [
                'subject'        => 'index/course/index.php',
                'search'         => 'course',
                'expectedreturn' => false
            ],
            'Test with trailing s' => [
                'subject'        => 'This is a test with courses',
                'search'         => 'course',
                'expectedreturn' => false
            ],
            'Test with trailing new line' => [
                'subject'        => 'This is a test with course\r',
                'search'         => 'course',
                'expectedreturn' => true
            ],
            'Test with trailing line break' => [
                'subject'        => 'This is a test with course\n',
                'search'         => 'course',
                'expectedreturn' => true
            ],
            'Test with trailing .' => [
                'subject'        => 'This is a test with course.',
                'search'         => 'course',
                'expectedreturn' => true
            ],
            'Test with trailing . and space' => [
                'subject'        => 'This is a test with course. ',
                'search'         => 'course',
                'expectedreturn' => true
            ],
            'Test with trailing . and new line' => [
                'subject'        => 'This is a test with course\n',
                'search'         => 'course',
                'expectedreturn' => true
            ],
            'Test with trailing . and line break' => [
                'subject'        => 'This is a test with course\r',
                'search'         => 'course',
                'expectedreturn' => true
            ],
            'Test with trailing course.php' => [
                'subject'        => 'This is a test with course.php',
                'search'         => 'course',
                'expectedreturn' => false
            ],
            'Test with line breaks' => [
                'subject'        => 'Tours will be displayed on any page whose URL matches this Dashboard
                Dashboard
                ',
                'search'         => 'Dashboard',
                'expectedreturn' => true
            ],
            'Test with prefix and suffix' => [
                'subject'        => 'Tours will be displayed on any page whose URL matches this **Dashboard**',
                'search'         => 'Dashboard',
                'expectedreturn' => true,
                'prefix'         => '**',
                'suffix'         => '**'
            ],
            'Test with suffix' => [
                'subject'        => 'Tours will be displayed on any page whose URL matches this Dashboards',
                'search'         => 'Dashboard',
                'expectedreturn' => true,
                'prefix'         => null,
                'suffix'         => 's'
            ],
            'Test with prefix' => [
                'subject'        => 'Tours will be displayed on any page whose URL matches this *Dashboard',
                'search'         => 'Dashboard',
                'expectedreturn' => true,
                'prefix'         => '*'
            ]
        ];
    }

    /**
     * Data provider for {@see self::test_get_highlighted_regex_search_subject()}.
     *
     * @return array List of data sets - (string) data set name => (array) data
     */
    public function get_highlighted_regex_search_subject_provider(): array {
        return [
            'Basic test' => [
                'subject'        => 'This is a Test',
                'pattern'        => '/([A-Z])\w+/',
                'expectedstring' => '!This! is a !Test!',
            ],
            'Basic test with replacement' => [
                'subject'        => 'This is a Test',
                'pattern'        => '/([A-Z])\w+/',
                'expectedstring' => '!Hello! is a !Hello!',
                'replacement'    => 'Hello'
            ],
            'matches any string that starts with The' => [
                'subject'        => 'The end',
                'pattern'        => '/^The/',
                'expectedstring' => '!The! end',
            ],
            'matches a string that has ab followed by zero or one c' => [
                'subject'        => 'ab abc abcc babc',
                'pattern'        => '/abc?/',
                'expectedstring' => '!ab! !abc! !abc!c b!abc!',
            ],
            'matches a single character that is a digit' => [
                'subject'        => 'abc ac acb aob a2b a42c',
                'pattern'        => '/\d/',
                'expectedstring' => 'abc ac acb aob a!2!b a!4!!2!c',
            ],
            'matches only if the pattern is fully surrounded by word characters' => [
                'subject'        => 'ab abc abcc babcd',
                'pattern'        => '/\babc\b/',
                'expectedstring' => 'ab !abc! abcc babcd',
            ]
        ];
    }

    /**
     * Data provider for {@see self::test_check_prefix()}.
     *
     * @return array List of data sets - (string) data set name => (array) data
     */
    public function check_prefix_provider(): array {
        return [
            'Test with . prefix' => [
                'haystack' => '.Test',
                'needle'   => '.',
                'expected' => true
            ],
            'Test with no prefix' => [
                'haystack' => 'Test',
                'needle'   => '',
                'expected' => true
            ],
            'Test with space prefix' => [
                'haystack' => ' Test',
                'needle'   => ' ',
                'expected' => true
            ],
            'Test with multiple character prefix' => [
                'haystack' => '**Test',
                'needle'   => '**',
                'expected' => true
            ],
            'Test with prefix exeeding haystack'  => [
                'haystack' => '1Test',
                'needle'   => '321',
                'expected' => false
            ]
        ];
    }

    /**
     * Data provider for {@see self::test_check_suffix()}.
     *
     * @return array List of data sets - (string) data set name => (array) data
     */
    public function check_suffix_provider(): array {
        return [
            'Test with s suffix' => [
                'haystack' => 'tests',
                'needle'   => 's',
                'expected' => true,
            ],
            'Test with no suffix' => [
                'haystack' => 'test',
                'needle'   => '',
                'expected' => true,
            ],
            'Test incorrect suffix' => [
                'haystack' => 'testn',
                'needle'   => 's',
                'expected' => false,
            ],
            'Test question mark suffix' => [
                'haystack' => 'test?',
                'needle'   => '?',
                'expected' => true,
            ],
            'Test with multiple character suffix' => [
                'haystack' => 'testss',
                'needle'   => 'ss',
                'expected' => true,
            ],
            'Test with space' => [
                'haystack' => 'test ',
                'needle'   => ' ',
                'expected' => true,
            ],
            'Test with new line suffix' => [
                'haystack' => 'test
                ',
                'needle'   => '
                ',
                'expected' => true,
            ],
            'Test with space and newline suffix' => [
                'haystack' => 'test
                ',
                'needle'   => '
                ',
                'expected' => true,
            ],
            'Test with suffix longer than haystrack' => [
                'haystack' => 'test1',
                'needle'   => '123',
                'expected' => false,
            ],
        ];
    }

    /**
     * Data provider for {@see self::test_str_slice()}.
     *
     * @return array List of data sets - (string) data set name => (array) data
     */
    public function str_slice_provider(): array {
        return [
            'Test one' => [
                'input'    => 'abcdefg',
                'slice'    => '2',
                'expected' => 'c',
            ],
            'Test one' => [
                'input'    => 'abcdefg',
                'slice'    => '2:4',
                'expected' => 'cd',
            ],
            'Test one' => [
                'input'    => 'abcdefg',
                'slice'    => '2:',
                'expected' => 'cdefg',
            ],
            'Test one' => [
                'input'    => 'abcdefg',
                'slice'    => ':4',
                'expected' => 'abcd',
            ],
            'Test one' => [
                'input'    => 'abcdefg',
                'slice'    => ':-3',
                'expected' => 'abcd',
            ],
            'Test one' => [
                'input'    => 'abcdefg',
                'slice'    => '-3:',
                'expected' => 'efg',
            ],
            'Test one' => [
                'input'    => 'this is **course',
                'slice'    => '8:16',
                'expected' => '**course',
            ]
        ];
    }

    /**
     * Test function accepts parameters passed from the specified data provider.
     *
     * @dataProvider replace_string_group_with_hash_provider
     * @param string $subject
     * @param string $openingbracket
     * @param string $closingbracket
     * @param string $expectedstring
     * @param array $expectedhashvalues
     */
    public function test_replace_string_group_with_hash(string $subject, string $openingbracket, string $closingbracket,
    string $expectedstring, array $expectedhashvalues) {
        list($result , $resulthashvalues) = tool_customlang_utils::replace_string_group_with_hash($subject, $openingbracket,
        $closingbracket);
        $this->assertEquals($result, $expectedstring);
    }

    /**
     * Test function that tests that function replace_string_group_with_hash throws exception when given charaters are the same
     *
     * @return void
     */
    public function test_exception_replace_string_group_with_hash() {
        $this->expectException(LogicException::class);
        tool_customlang_utils::replace_string_group_with_hash("test", 'x', 'x');
    }

    /**
     * Test function to test static function that replaces hash values in string to original values given the an array
     *
     * @dataProvider replace_hash_to_string_provider
     * @param string $subject
     * @param array $hashvalues
     * @param string $expectedstring
     */
    public function test_replace_hash_to_string(string $subject, array $hashvalues, string $expectedstring) {
        $result = tool_customlang_utils::replace_hash_to_string($subject, $hashvalues);
        $this->assertEquals($expectedstring, $result);
    }

    /**
     * Test function to test if the function is_safe_to_replace produces expected results
     *
     * @dataProvider is_safe_to_replace_provider
     * @param string $subject
     * @param string $search
     * @param bool $expectedreturn
     * @param string $prefix
     * @param string $suffix
     */
    public function test_is_safe_to_replace(string $subject, string $search, bool $expectedreturn,
        $prefix = null, $suffix = null
    ) {
        $this->assertEquals($expectedreturn, tool_customlang_utils::is_safe_to_replace($subject, $search, $prefix, $suffix));
    }

    /**
     * Test function to test if the function get_highlighted_regex_search_subject produces expected results
     *
     * @dataProvider get_highlighted_regex_search_subject_provider
     * @param string $subject
     * @param string $pattern
     * @param string $expectedstring
     * @param string $replacement default vaulue is null
     */
    public function test_get_highlighted_regex_search_subject(string $subject, string $pattern, string $expectedstring,
    string $replacement = null) {
        $this->assertEquals($expectedstring, tool_customlang_utils::get_highlighted_regex_search_subject(
            $subject, $pattern, $replacement, '!', '!'
        ));
    }

    /**
     * Test function to test if the function check_prefix() produces expected results
     *
     * @dataProvider check_prefix_provider
     * @param string $haystack
     * @param string $needle
     * @param bool $expected
     */
    public function test_check_prefix(string $haystack, string $needle, bool $expected) {
        $result = tool_customlang_utils::check_prefix($haystack, $needle);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test function to test if the function check_suffix() produces expected results
     *
     * @dataProvider check_suffix_provider
     * @param string $haystack
     * @param string $needle
     * @param bool $expected
     */
    public function test_check_suffix(string $haystack, string $needle, bool $expected) {
        $result = tool_customlang_utils::check_suffix($haystack, $needle);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test function to test if the function str_slice() produces expected results
     *
     * @dataProvider str_slice_provider
     * @param string $input
     * @param string $slice
     * @param string $expected
     */
    public function test_str_slice(string $input, string $slice, string $expected) {
        $result = tool_customlang_utils::str_slice($input, $slice);
        $this->assertEquals($expected, $result);
    }
}