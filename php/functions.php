<?php

/**
 * @file
 * The basic functions to parse your NatWest bank statement
 */

/**
 * Converts the table rows to a multidimensional array
 *
 * @param string $html
 *
 * @return array
 */
function table_to_array($html) {
  $DOM = new DOMDocument;
  $DOM->loadHTML($html);

  $items = $DOM->getElementsByTagName('tr');

  $rows = array();
  foreach ($items as $node) {
    $rows[] = cell_values($node->childNodes);
  }

  return $rows;
}

/**
 * Helper fmunction for table_to_array, converts the cells in a row to an array
 *
 * @param DOMNodeList $elements
 *
 * @return array
 */
function cell_values(DOMNodeList $elements) {
  $array = array();
  foreach ($elements as $element) {
    $array[] = $element->nodeValue;
  }
  return $array;
}

/**
 * Returns the passed data form the table as a usable array to make into a CSV
 *
 * @param array $array
 *
 * @return array
 */
function parse_raw_array($array) {
  $out = array();
  foreach ($array as $row) {
    // Is this a data row?
    if (count($row) === 7 && $row[0] !== 'Date') {
      $orig_date        = $row[0];
      $orig_type        = $row[1];
      $orig_description = $row[2];
      $orig_in          = $row[3];
      $orig_out         = $row[4];
      $orig_balance     = $row[5];

      $type = trim($orig_type, '-');
      $type = empty($type) ? '???' : $type;

      $balance = parse_cell_empty($orig_balance);

      $details = parse_description_cell($orig_type, $orig_description);

      $date = explode(' ', $orig_date);

      $out[] = array(
        $date[2],
        $date[1],
        $date[0],
        $type,
        parse_cell_empty($orig_in),
        parse_cell_empty($orig_out),
        $balance,
        empty($details[0]) ? '' : $details[0],
        empty($details[1]) ? '' : $details[1],
        empty($details[2]) ? '' : $details[2],
        empty($details[3]) ? '' : $details[3],
      );
    }
  }

  return $out;
}

/**
 * Helper function to clean up the non numeric values in the table
 *
 * @param string $value
 *
 * @return float
 */
function parse_cell_empty($value) {
  if ($value === ' - ') {
    return (float) 0;
  }

  $value = str_replace(',', '', $value);
  $value = str_replace('£', '', $value);
  $value = str_replace('Â', '', $value);

  return (float) $value;
}

/**
 * Creates the statement CSV from the array
 *
 * @param type $array
 */
function make_the_csv($array) {
  $fp = fopen(DOCROOT . 'pastebin/statement.csv', 'w');

  $columns = array(
    'Year',
    'Month',
    'Dat',
    'Type',
    'In',
    'Out',
    'Balance',
    'Reference',
    'Data 1',
    'Data 2',
    'Data 3',
  );

  fputcsv($fp, $columns, ',', '"');

  foreach ($array as $row) {
    fputcsv($fp, $row, ',', '"');
  }

  fclose($fp);
}

/**
 * Sorts out the relevant info for the Description field of the CSV and puts
 * any extra data in the Data columns if they are relevant
 *
 * @param string $type
 * @param array $orig_description
 *
 * @return array
 */
function parse_description_cell($type, $orig_description) {
  $data = explode(',', $orig_description);

  foreach ($data as &$tmp) {
    $tmp = trim($tmp);
  }

  $return = array(NULL, NULL, NULL, NULL);

  switch ($type) {
    case 'ATM':
    case 'BAC':
    case 'CDM':
    case 'Charge':
    case 'INT':
      $return[0] = safe_return_array_item(0, $data);
      break;
    case 'D/D':
      $return[0] = safe_return_array_item(0, $data);
      $return[1] = safe_return_array_item(1, $data);
      break;
    case 'OTR':
      $return[0] = safe_return_array_item(1, $data);
      break;
    case 'POS':
      $return[0] = safe_return_array_item(1, $data);
      $return[1] = safe_return_array_item(2, $data);
      $return[2] = safe_return_array_item(3, $data);
      $return[3] = safe_return_array_item(0, $data);
      break;
    case 'TFR':
      $return[0] = safe_return_array_item(1, $data);
      $return[1] = safe_return_array_item(0, $data);
      break;
    default:
      $return[0] = '??? Probably Cashier ???';
      $return[1] = implode(', ', $data);
      break;
  }

  return $return;
}

/**
 * Helper function to return an empty string when trying to reference an array
 * item that may not exist
 *
 * @param int $id
 * @param array $array
 *
 * @return string
 */
function safe_return_array_item($id, $array) {
  return empty($array[$id]) ? '' : $array[$id];
}
