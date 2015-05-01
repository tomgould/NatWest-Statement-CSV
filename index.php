<?php

/**
 * @file
 *   Converts the useless bank statement table form NatWest's UI in to a CSV
 *   with data seperated into useful columns for sorting and filtering.
 *
 *   1: To use this copy the HTML of the NatWest bank statement using FireBug
 *      or View Source.
 *   2: Paste this HTML into a html file located at pastebin/statement.html
 *   3: Call the index file with PHP on the command line eg:
 *     $ php /var/www/natparse/index.php
 *
 *   4: The csv should be generated in the same directory, now you can
 *      look at the data in a sensible way and sort or filter your transactions
 *
 */
define('DOCROOT', __DIR__ . "/");

include DOCROOT . 'php/functions.php';

$html         = file_get_contents(DOCROOT . 'pastebin/statement.html');
$raw_array    = table_to_array($html);
$parsed_array = parse_raw_array($raw_array);

make_the_csv($parsed_array);

echo "Finished\n";
echo "CSV Written to '" . DOCROOT . "pastebin/statement.csv'\n";
echo "Visit http://tom-gould.co.uk\n";
