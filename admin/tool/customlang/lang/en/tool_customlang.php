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
 * Strings for Language customisation admin tool
 *
 * @package    tool
 * @subpackage customlang
 * @copyright  2010 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['checkin'] = 'Save strings to language pack';
$string['checkout'] = 'Open language pack for editing';
$string['checkoutdone'] = 'Language pack loaded';
$string['checkoutinprogress'] = 'Loading language pack';
$string['cliexportfileexists'] = 'File for {$a->lang} already exists, skipping. If you want to overwrite add the --override=true option.';
$string['cliexportheading'] = 'Starting to export lang files.';
$string['cliexportnofilefoundforlang'] = 'No file found to export. Skipping export for this language.';
$string['cliexportfilenotfoundforcomponent'] = 'File {$a->filepath} not found for language {$a->lang}. Skipping this file.';
$string['cliexportstartexport'] = 'Exporting language {$a}';
$string['cliexportzipdone'] = 'Zip created: {$a}';
$string['cliexportzipfail'] = 'Cannot create zip {$a}';
$string['clifiles'] = 'Files to import into {$a}';
$string['cliimporting'] = 'Import files string (mode {$a})';
$string['clinolog'] = 'Nothing to import into {$a}';
$string['climissinglang'] = 'Missing language';
$string['climissingfiles'] = 'Missing valid files';
$string['climissingmode'] = 'Missing or invalid mode (valid is all, new or update)';
$string['climissingsource'] = 'Missing file or folder';
$string['confirmcheckin'] = 'You are about to save modifications to your local language pack. This will export the customised strings from the translator into your site data directory and your site will start using the modified strings. Press \'Continue\' to proceed with saving.';
$string['customlang:edit'] = 'Edit local translation';
$string['customlang:export'] = 'Export local translation';
$string['customlang:view'] = 'View local translation';
$string['export'] = 'Export custom strings';
$string['exportfilter'] = 'Select component(s) to export';
$string['filter'] = 'Filter strings';
$string['filtercomponent'] = 'Show strings of these components';
$string['filtercustomized'] = 'Customised only';
$string['filtermodified'] = 'Modified in this session only';
$string['filteronlyhelps'] = 'Help only';
$string['filtershowstrings'] = 'Show strings';
$string['filterstringid'] = 'String identifier';
$string['filtersubstring'] = 'Only strings containing';
$string['headingcomponent'] = 'Component';
$string['headinglocal'] = 'Local customisation';
$string['headingstandard'] = 'Standard text';
$string['headingstringid'] = 'String';
$string['import'] = 'Import custom strings';
$string['import_mode'] = 'Import mode';
$string['import_new'] = 'Create only strings without local customisation';
$string['import_update'] = 'Update only strings with local customisation';
$string['import_all'] = 'Create or update all strings from the component(s)';
$string['importfile'] = 'Import file';
$string['langpack'] = 'Language component(s)';
$string['markinguptodate'] = 'Marking the customisation as up-to-date';
$string['markinguptodate_help'] = 'The customised translation may get outdated if either the English original or the master translation has modified since the string was customised on your site. Review the customised translation. If you find it up-to-date, click the checkbox. Edit it otherwise.';
$string['markuptodate'] = 'mark as up-to-date';
$string['modifiedno'] = 'There are no modified strings to save.';
$string['modifiednum'] = 'There are {$a} modified strings. Do you wish to save these changes to your local language pack?';
$string['nolocallang'] = 'No local strings found.';
$string['notice_ignorenew'] = 'Ignoring string {$a->component}/{$a->stringid} because it is not customised.';
$string['notice_ignoreupdate'] = 'Ignoring string {$a->component}/{$a->stringid} because it is already defined.';
$string['notice_inexitentstring'] = 'String {$a->component}/{$a->stringid} not found.';
$string['notice_missingcomponent'] = 'Missing component {$a->component}.';
$string['notice_success'] = 'String {$a->component}/{$a->stringid} updated successfully.';
$string['nostringsfound'] = 'No strings found, please modify the filter settings';
$string['placeholder'] = 'Placeholders';
$string['placeholder_help'] = 'Placeholders are special statements like `{$a}` or `{$a->something}` within the string. They are replaced with a value when the string is actually printed.

It is important to copy them exactly as they are in the original string. Do not translate them nor change their left-to-right orientation.';
$string['placeholderwarning'] = 'string contains a placeholder';
$string['pluginname'] = 'Language customisation';
$string['savecheckin'] = 'Save changes to the language pack';
$string['savecontinue'] = 'Apply changes and continue editing';
$string['privacy:metadata'] = 'The Language customisation plugin does not store any personal data.';

$string['batchreplaceheading'] = 'Language customization: Batch search and replace';
$string['batchreplacenosearch'] = '-s  --search option is missing.';
$string['batchreplacenoreplace'] = '-r  --replace option is missing.';
$string['batchreplacelangprompt'] = 'Choose language pack {$a}';
$string['batchreplacelangnotfound'] = 'Language pack not found. Aborting program.';
$string['batchreplacematches'] = 'Found {$a->numofrows} matches in components \'{$a->componentsstring}\' of language pack \'{$a->lang}\'';
$string['batchreplacestage'] = '<newline>{$a->lang}: {$a->component} {$a->stringid}:<newline><newline><colour:red>-<bell>\'{$a->subject}\'<newline><colour:green>-<bell>\'{$a->match}\'<newline><newline><colour:blue><bold>({$a->matchnumber}/{$a->totalmatches}) Stage this match [y,n,a,N,?]?<colour:normal><normal>';
$string['batchreplacestagedanger'] = '<newline>{$a->lang}: {$a->component} {$a->stringid}:<newline><newline><colour:red>-<bell>\'{$a->subject}\'<newline><colour:green>-<bell>\'{$a->match}\'<newline><newline><colour:blue><bold>({$a->matchnumber}/{$a->totalmatches}) Stage this match [y,n,N,?]?<colour:normal><normal>';
$string['batchreplacestagehelp'] = '<colour:red><bold>y - stage this match<newline>n - do not stage this match<newline>a - stage this match and all later matches in this search<newline>N - do not stage this match and all later matches in this search<newline>? - print help<colour:normal><normal>';
$string['batchreplacestagehelpdanger'] = '<colour:red><bold>y - stage this match<newline>n - do not stage this match<newline>N - do not stage this match and all later matches in this search<newline>? - print help<colour:normal><normal>';
$string['batchreplacestageall'] = '{$a->lang}: {$a->component} {$a->stringid}:<newline><colour:red>-<bell>\'{$a->subject}\'<newline><colour:green>-<bell>\'{$a->match}\'<colour:normal>';
$string['batchreplacedanger'] = '<newline><newline><bgcolour:red> Notice. The following {$a->amount} strings are considered unsafe to replace, take extra care when replacing.<normal>';
$string['batchreplacedrymode'] = 'Dry mode. Replacements will not take effect unless --run option is provided.';
$string['batchreplacecheckin'] = 'Saving strings to language pack';
$string['batchreplacesuccess'] = 'Succesfully replaced strings.';
$string['batchreplaceconfirm'] = 'You are about to save modifications to your local language pack. This will export the customised strings from the translator into your site data directory and your site will start using the modified strings. Do you want to proceed with saving [yN]?';
$string['batchreplaceabort'] = 'Aborting program';
$string['batchreplaceregexnotsupported'] = 'Program can\'t run, because the current database does not support regex';
$string['batchreplaceassumeerror'] = 'Pram can\'t run, because both --assume-yes and --assume-no option have been set. Can only accept one';
$string['batchreplacestageassumeno'] = '{$a->lang}: {$a->component} {$a->stringid}:<newline>-<bell>\'{$a->subject}\'<colour:normal>';