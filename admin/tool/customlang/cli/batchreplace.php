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
 * Batch search and replace custom language strings.
 *
 * @package    tool_customlang
 * @subpackage customlang
 * @copyright  2020 Scott Verbeek <scottverbeek@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/clilib.php");
require_once($CFG->libdir.'/adminlib.php');
require_once("$CFG->dirroot/$CFG->admin/tool/customlang/locallib.php");

$usage = <<<EOF
Batch search and replace language strings
Useful for changing strings that occur in lot's of language strings.
Default mode of this script is interactive, use -y or -n for non-interactive.

Options:
-s, --search            Case sensitive string to search for
-r, --replace           Case sensitive string that replaces --search, default: null
-l, --lang              Comma seperated language ids to export, default: $CFG->lang
-c, --components        Comma seperated components to export, default: all
-y, --yes, --assume-yes The script will run without user interaction and will anwser yes to all matches
-x, --regex             If this option has been set, the program will use the search value as a regex
-n, --no, --assume-no   The script will run without user interaction and will answer no to all questions
-p, --prefix            Case sensitive prefix that when found in a search, it is considered safe to replace, default = null
-f, --suffix            Case sensitive suffix that when found in a search, it is considered safe to replace, default = null

-h, --help              Print out this help

Examples:
Search and replace language files:
\$ php admin/tool/customlang/cli/batchreplace.php -s='course' -r='subject'

Search and replace language files with search that could have a suffix
\$ php admin/tool/customlang/cli/batchreplace.php -s='course' -r='subject' -f='s'

Search and replace the Dutch files of moodle core and the activity 'quiz':
\$ php admin/tool/customlang/cli/batchreplace.php --lang='nl' --components='moodle,quiz' --search='course' --replace='subject'

EOF;

// Get cli options.
list($options, $unrecognized) = cli_get_params(
    [
        'components' => '',
        'help' => false,
        'assume-yes' => false,
        'assume-no' => false,
        'lang' => '',
        'regex' => false,
        'replace' => null,
        'search' => '',
        'prefix' => null,
        'suffix' => null
    ],
    [
        'c' => 'components',
        'h' => 'help',
        'l' => 'lang',
        'n' => 'assume-no',
        'no' => 'assume-no',
        'r' => 'replace',
        'R' => 'run',
        's' => 'search',
        'x' => 'regex',
        'y' => 'assume-yes',
        'yes' => 'assume-yes',
        'p' => 'prefix',
        'f' => 'suffix'
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    echo $usage;
    die;
}

if ($options['assume-yes'] && $options['assume-no']) {
    // If both assume-yes and assume-no options been set? Prompt that this can't happen and stop program.
    cli_writeln(get_string('batchreplaceassumeerror', 'tool_customlang'));
    die;
}

// No search option set? Stop program.
if ($options['search'] == '' ) {
    cli_writeln(get_string('batchreplacenosearch', 'tool_customlang'));
    echo $usage;
    die;
}
$search = $options['search'];

// No replace option set? Stop program.
if ($options['replace'] == null ) {
    cli_writeln(get_string('batchreplacenoreplace', 'tool_customlang'));
    echo $usage;
    die;
}
$replace = $options['replace'];

if ($options['lang'] == '') {
    // No language option set? Default to english.
    $lang = $CFG->lang;
} else {
    // English option set? Get string.
    $lang = $options['lang'];
}

// Get all language packs.
$langs = array_keys(get_string_manager()->get_list_of_translations(true));
if ( !in_array($lang, $langs) ) {
    // Lang does not exist? Then stop the program.
    cli_writeln(get_string('batchreplacelangnotfound', 'tool_customlang'));
    die;
}

if ($options['regex'] && !$DB->sql_regex_supported()) {
    // If regex option isset and database does not support regex? Then stop.
    cli_writeln(get_string('batchreplaceregexnotsupported', 'tool_customlang'));
    die;
}

// We need a bit of extra execution time and memory here.
core_php_time_limit::raise(HOURSECS);
raise_memory_limit(MEMORY_EXTRA);

// Update the translator database.
cli_writeln(get_string('checkoutinprogress', 'tool_customlang'));
tool_customlang_utils::checkout($lang);
cli_writeln(get_string('checkoutdone', 'tool_customlang'));

$components = [];
if ($options['components']) {
    // If components option isset? Then set components.
    $components = explode(',', $options['components']);
} else {
    // No components set. We fetch all installed components, default.
    $components = tool_customlang_utils::list_components();
}

list($insql, $inparams) = $DB->get_in_or_equal($components, SQL_PARAMS_NAMED);
$sql  = "  SELECT s.*, c.name AS component
             FROM {tool_customlang_components} c
             JOIN {tool_customlang} s ON s.componentid = c.id
            WHERE s.lang = :lang
              AND c.name $insql";
$params = array_merge(array('lang' => $lang), $inparams);
if ($options['regex']) {
    $sql .= ' AND ( s.original ' . $DB->sql_regex() . ' :substringoriginal OR
                    s.master '. $DB->sql_regex() . ' :substringmaster OR
                    s.local '. $DB->sql_regex() . ' :substringlocal )';
    $params['substringoriginal'] = $params['substringmaster'] = $params['substringlocal'] = $search;
} else {
    $sql .= " AND (".$DB->sql_like('s.original', ':substringoriginal', true)." OR
                   ".$DB->sql_like('s.master', ':substringmaster', true)." OR
                   ".$DB->sql_like('s.local', ':substringlocal', true).")";
    $params['substringoriginal'] = $params['substringmaster'] = $params['substringlocal'] = '%'.$DB->sql_like_escape($search).'%';
}
$osql = "ORDER BY c.name, s.stringid";

$strings = $DB->get_records_sql($sql.$osql, $params);
$numofrows = count($strings);

if ($numofrows == 0) {
    // No results? Stop program.
    cli_writeln(get_string('nostringsfound', 'tool_customlang'));
    die;
}

$componentsstring = ($options['components'] == '') ? 'all' : implode($components);
cli_writeln(get_string('batchreplacematches', 'tool_customlang', [
    'numofrows' => $numofrows,
    'componentsstring' => $componentsstring,
    'lang' => $lang
    ]));

$matchnumber = 1;
$acceptedanswers = ['y', 'n', 'a', 'N'];

$acceptall = false;
$unsafestrings = [];
foreach ($strings as $string) {
    if ($options['regex']) {
        // Which column (local, master, original) contains the match to $search.
        if (preg_match($search, $string->local)) {
            $subject = $string->local;
        } else if (preg_match($search, $string->master)) {
            $subject = $string->master;
        } else if (preg_match($search, $string->original)) {
            $subject = $string->original;
        } else {
            // No subject found? Continue to next $string in $strings.
            $numofrows--;
            continue;
        }
    } else {
        // Which column (local, master, original) contains the match to $search.
        if (preg_match("/{$search}/", $string->local)) {
            $subject = $string->local;
        } else if (preg_match("/{$search}/", $string->master)) {
            $subject = $string->master;
        } else if (preg_match("/{$search}/", $string->original)) {
            $subject = $string->original;
        } else {
            // No subject found? Continue to next $string in $strings.
            $numofrows--;
            continue;
        }
    }

    // First make the subject safe to replace.
    list($safesubject, $hashvalues) = tool_customlang_utils::replace_string_group_with_hash($subject);

    if (!$options['regex'] && !tool_customlang_utils::is_safe_to_replace(
            $safesubject, $search, $options['prefix'], $options['suffix'])
        ) {
        // Is string considered unsafe to replace and option regex has not been set?
        // Then store them in a different array and handle them later.
        // Saving some properties we can use lates.
        $string->safesubject = $safesubject;
        $string->subject = $subject;
        $string->hashvalues = $hashvalues;
        $unsafestrings[] = $string;
        continue;
    }

    if ($options['assume-no']) {
        // If option --assume-no has been set? Then do a different loop.
        if ($options['regex']) {
            $highlightedsearch = tool_customlang_utils::get_highlighted_regex_search_subject(
                $safesubject, $search, null, '<colour:black><bgcolour:white>', '<colour:normal>'
            );
        } else {
            $highlightedsearch = str_replace($search, '<colour:black><bgcolour:white>'.$search.'<colour:normal>', $safesubject);
        }
        $highlightedsearch = tool_customlang_utils::replace_hash_to_string($highlightedsearch, $hashvalues);
        $stringoptions = [
            "lang" => $string->lang,
            "component" => $string->component,
            "stringid" => $string->stringid,
            "subject" => $highlightedsearch,
        ];
        cli_writeln( cli_ansi_format( get_string('batchreplacestageassumeno', 'tool_customlang', $stringoptions) ) );
        $matchnumber++;
        continue;
    }

    if ($options['regex']) {
        $highlightedsearch = tool_customlang_utils::get_highlighted_regex_search_subject($safesubject, $search);
        $highlightedreplace = tool_customlang_utils::get_highlighted_regex_search_subject(
            $safesubject, $search, $replace, '<colour:white><bgcolour:green>', '<colour:green>'
        );
    } else {
        $highlightedsearch = str_replace($search, '<colour:white><bgcolour:red>'.$search.'<colour:red>', $safesubject);
        $highlightedreplace = str_replace($search, '<colour:white><bgcolour:green>'.$replace.'<colour:green>', $safesubject);
    }

    $highlightedsearch = tool_customlang_utils::replace_hash_to_string($highlightedsearch, $hashvalues);
    $highlightedreplace = tool_customlang_utils::replace_hash_to_string($highlightedreplace, $hashvalues);

    $stringoptions = [
        "lang" => $string->lang,
        "component" => $string->component,
        "stringid" => $string->stringid,
        "subject" => $highlightedsearch,
        "match" => $highlightedreplace,
        "matchnumber" => $matchnumber,
        "totalmatches" => $numofrows
    ];

    if ($acceptall || $options['assume-yes']) {
        cli_writeln( cli_ansi_format( get_string('batchreplacestageall', 'tool_customlang', $stringoptions) ) );
    } else {
        // Get the answer for this match.
        $answer = cli_input(cli_ansi_format(get_string('batchreplacestage', 'tool_customlang', $stringoptions)));
        while (!in_array($answer, $acceptedanswers) && !$acceptall) {
            cli_writeln( cli_ansi_format( get_string('batchreplacestagehelp', 'tool_customlang') ) );
            $answer = cli_input(cli_ansi_format(get_string('batchreplacestage', 'tool_customlang', $stringoptions)));
        }
        if ($answer == 'a') {
            $acceptall = true;
        }

        if ($answer == 'N') {
            // Answer equal to 'N'? Then break out of foreach.
            break;
        }

        if ($answer == 'n') {
            // Answer equal to 'n'? Go to next $string in $strings one.
            $matchnumber++;
            continue;
        }
    }

    // Replace string then update record and bump number.
    $subject = str_replace($search, $replace, $safesubject);
    $string->local = tool_customlang_utils::replace_hash_to_string($subject, $hashvalues);
    $DB->update_record('tool_customlang', $string);
    $matchnumber++;
}

if (($count = count($unsafestrings)) > 0) {
    cli_writeln(cli_ansi_format(get_string('batchreplacedanger', 'tool_customlang', ['amount' => $count])));
}

$acceptedanswers = ['y', 'n', 'N'];
foreach ($unsafestrings as $string) {
    if ($options['assume-no']) {
        // If option --assume-no has been set? Then do a different loop.
        $highlightedsearch = str_replace($search, '<colour:black><bgcolour:white>'.$search.'<colour:normal>', $string->safesubject);
        $highlightedsearch = tool_customlang_utils::replace_hash_to_string($highlightedsearch, $string->hashvalues);
        $stringoptions = [
            "lang" => $string->lang,
            "component" => $string->component,
            "stringid" => $string->stringid,
            "subject" => $highlightedsearch,
        ];
        cli_writeln( cli_ansi_format( get_string('batchreplacestageassumeno', 'tool_customlang', $stringoptions) ) );
        $matchnumber++;
        continue;
    }

    $highlightedsearch = str_replace($search, '<colour:white><bgcolour:red>'.$search.'<colour:red>', $string->safesubject);
    $highlightedsearch = tool_customlang_utils::replace_hash_to_string($highlightedsearch, $string->hashvalues);
    $highlightedreplace = str_replace($search, '<colour:white><bgcolour:green>'.$replace.'<colour:green>', $string->safesubject);
    $highlightedreplace = tool_customlang_utils::replace_hash_to_string($highlightedreplace, $string->hashvalues);

    $stringoptions = [
        "lang" => $string->lang,
        "component" => $string->component,
        "stringid" => $string->stringid,
        "subject" => $highlightedsearch,
        "match" => $highlightedreplace,
        "matchnumber" => $matchnumber,
        "totalmatches" => $numofrows
    ];

    if ($options['assume-yes']) {
        cli_writeln( cli_ansi_format( get_string('batchreplacestageall', 'tool_customlang', $stringoptions) ) );
    } else {
        // No yes to all option here.
        // Get the answer for this match.
        $answer = cli_input(cli_ansi_format(get_string('batchreplacestagedanger', 'tool_customlang', $stringoptions)));
        while (!in_array($answer, $acceptedanswers)) {
            cli_writeln( cli_ansi_format( get_string('batchreplacestagehelpdanger', 'tool_customlang') ) );
            $answer = cli_input(cli_ansi_format(get_string('batchreplacestagedanger', 'tool_customlang', $stringoptions)));
        }
        if ($answer == 'N') {
            // Answer equal to 'N'? Then break out of foreach.
            break;
        }

        if ($answer == 'n') {
            // Answer equal to 'n'? Go to next $string in $strings one.
            $matchnumber++;
            continue;
        }
    }

    // Replace string then update record and bump number.
    $subject = str_replace($search, $replace, $string->safesubject);
    $string->local = tool_customlang_utils::replace_hash_to_string($subject, $string->hashvalues);
    $DB->update_record('tool_customlang', $string);
    $matchnumber++;
}

// Make sure we want to execute this.
$acceptedanswers = ['y', 'n'];
if ($options['assume-yes']) {
    $answer = 'y';
} else if ($options['assume-no']) {
    $answer = 'n';
} else {
    $answer = strtolower( cli_input( get_string('batchreplaceconfirm', 'tool_customlang') ) );
    while (!in_array($answer, $acceptedanswers)) {
        cli_writeln( cli_ansi_format( get_string('batchreplacestagehelpdanger', 'tool_customlang') ) );
        $answer = cli_input(cli_ansi_format(get_string('batchreplacestagedanger', 'tool_customlang', $stringoptions)));
    }
}
if ($answer == 'n') {
    // If answer no on last prompt? Then stop program.
    die;
}

cli_writeln(get_string('batchreplacecheckin', 'tool_customlang'));
tool_customlang_utils::checkin($lang);
cli_writeln(get_string('batchreplacesuccess', 'tool_customlang'));
