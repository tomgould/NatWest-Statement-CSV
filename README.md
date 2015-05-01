NatWest Statement CSV
===========

Converts the useless bank statement table form NatWest's site in to a CSV
with data separated into useful columns for sorting and filtering.

1: To use this copy the HTML of the NatWest bank statement using FireBug
   or View Source.

2: Paste this HTML into a html file located at pastebin/statement.html

3: Call the index file with PHP on the command line eg:
   $ php /var/www/natparse/index.php

4: The CSV should be generated in the same directory, now you can
   look at the data in a sensible way and sort or filter your transactions

