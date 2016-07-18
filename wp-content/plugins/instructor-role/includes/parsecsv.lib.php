<?php
namespace lmsParseCSVNS;

class LmsParseCSV
{

    /*

	Class: parseCSV v0.3.2
	http://code.google.com/p/parsecsv-for-php/


	Fully conforms to the specifications lined out on wikipedia:
	 - http://en.wikipedia.org/wiki/Comma-separated_values

	Based on the concept of Ming Hong Ng's CsvFileParser class:
	 - http://minghong.blogspot.com/2006/07/csv-parser-for-php.html



	Copyright (c) 2007 Jim Myhrberg (jim@zydev.info).

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.



	Code Examples
	----------------
	# general usage
	$csv = new parseCSV('data.csv');
	print_r($csv->data);
	----------------
	# tab delimited, and encoding conversion
	$csv = new parseCSV();
	$csv->encoding('UTF-16', 'UTF-8');
	$csv->delimiter = "\t";
	$csv->parse('data.tsv');
	print_r($csv->data);
	----------------
	# auto-detect delimiter character
	$csv = new parseCSV();
	$csv->auto('data.csv');
	print_r($csv->data);
	----------------
	# modify data in a csv file
	$csv = new parseCSV();
	$csv->sort_by = 'id';
	$csv->parse('data.csv');
	# "4" is the value of the "id" column of the CSV row
	$csv->data[4] = array('firstname' => 'John', 'lastname' => 'Doe', 'email' => 'john@doe.com');
	$csv->save();
	----------------
	# add row/entry to end of CSV file
	#  - only recommended when you know the extact sctructure of the file
	$csv = new parseCSV();
	$csv->save('data.csv', array('1986', 'Home', 'Nowhere', ''), true);
	----------------
	# convert 2D array to csv data and send headers
	# to browser to treat output as a file and download it
	$csv = new parseCSV();
	$csv->output (true, 'movies.csv', $array);
	----------------


	*/


    /**
     * Configuration
     * - set these options with $object->var_name = 'value';
     */

    # use first line/entry as field names
    public $heading = true;

    # override field names
    public $fields = array();

    # sort entries by this field
    public $sort_by = null;
    public $sort_reverse = false;

    # delimiter (comma) and enclosure (double quote)
    public $delimiter = ',';
    public $enclosure = '"';

    # basic SQL-like conditions for row matching
    public $conditions = null;

    # number of rows to ignore from beginning of data
    public $offset = null;

    # limits the number of returned rows to specified amount
    public $limit = null;

    # number of rows to analyze when attempting to auto-detect delimiter
    public $auto_depth = 15;

    # characters to ignore when attempting to auto-detect delimiter
    public $auto_non_chars = "a-zA-Z0-9\n\r";

    # preferred delimiter characters, only used when all filtering method
    # returns multiple possible delimiters (happens very rarely)
    public $auto_preferred = ",;\t.:|";

    # character encoding options
    public $convert_encoding = false;
    public $input_encoding = 'ISO-8859-1';
    public $output_encoding = 'ISO-8859-1';

    # used by unparse(), save(), and output() functions
    public $linefeed = "\r\n";

    # only used by output() function
    public $output_delimiter = ',';
    public $output_filename = 'data.csv';


    /**
     * Internal variables
     */

    # current file
    public $file;

    # loaded file contents
    public $file_data;

    # array of field values in data parsed
    public $titles = array();

    # two dimentional array of CSV data
    public $data = array();


    /**
     * Constructor
     * @param   input   CSV file or string
     * @return  nothing
     */
    public function __construct($input = null, $offset = null, $limit = null, $conditions = null)
    {
        if (null !== $offset) {
            $this->offset = $offset;
        }
        if (null !== $limit) {
            $this->limit = $limit;
        }
        if (count($conditions) > 0) {
            $this->conditions = $conditions;
        }
        if (! empty($input)) {
            $this->parse($input);
        }
    }


    // ==============================================
    // ----- [ Main Functions ] ---------------------
    // ==============================================

    /**
     * Parse CSV file or string
     * @param   input   CSV file or string
     * @return  nothing
     */
    public function parse($input = null, $offset = null, $limit = null, $conditions = null)
    {
        if (! empty($input)) {
            if (null !== $offset) {
                $this->offset = $offset;
            }
            if (null !== $limit) {
                $this->limit = $limit;
            }
            if (count($conditions) > 0) {
                $this->conditions = $conditions;
            }
            if (is_readable($input)) {
                $this->data = $this->parseFile($input);
            } else {
                $this->file_data = &$input;
                $this->data = $this->parseString();
            }
            if ($this->data === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Save changes, or new file and/or data
     * @param   file     file to save to
     * @param   data     2D array with data
     * @param   append   append current data to end of target CSV if exists
     * @param   fields   field names
     * @return  true or false
     */
    public function save($file = null, $data = array(), $append = false, $fields = array())
    {
        if (empty($file)) {
            $file = &$this->file;
        }
        $mode = ( $append ) ? 'at' : 'wt' ;
        $is_php = ( preg_match('/\.php$/i', $file) ) ? true : false ;
        return $this->_wfile($file, $this->unparse($data, $fields, $append, $is_php), $mode);
    }

    /**
     * Generate CSV based string for output
     * @param   output      if true, prints headers and strings to browser
     * @param   filename    filename sent to browser in headers if output is true
     * @param   data        2D array with data
     * @param   fields      field names
     * @param   delimiter   delimiter used to separate data
     * @return  CSV data using delimiter of choice, or default
     */
    public function output($output = true, $filename = null, $data = array(), $fields = array(), $delimiter = null)
    {
        if (empty($filename)) {
            $filename = $this->output_filename;
        }
        if (null === $delimiter) {
            $delimiter = $this->output_delimiter;
        }
        $data = $this->unparse($data, $fields, null, null, $delimiter);
        if ($output) {
            header('Content-type: application/csv');
            header('Content-Disposition: inline; filename="'.$filename.'"');
            echo $data;
        }
        return $data;
    }

    /**
     * Convert character encoding
     * @param   input    input character encoding, uses default if left blank
     * @param   output   output character encoding, uses default if left blank
     * @return  nothing
     */
    public function encoding($input = null, $output = null)
    {
        $this->convert_encoding = true;
        if (null !== $input) {
            $this->input_encoding = $input;
        }
        if (null !== $output) {
            $this->output_encoding = $output;
        }
    }

    /**
     * Auto-Detect Delimiter: Find delimiter by analyzing a specific number of
     * rows to determine most probable delimiter character
     * @param   file           local CSV file
     * @param   parse          true/false parse file directly
     * @param   search_depth   number of rows to analyze
     * @param   preferred      preferred delimiter characters
     * @param   enclosure      enclosure character, default is double quote (").
     * @return  delimiter character
     */
    public function auto($file = null, $parse = true, $search_depth = null, $preferred = null, $enclosure = null)
    {

        if (null === $file) {
            $file = $this->file;
        }
        if (empty($search_depth)) {
            $search_depth = $this->auto_depth;
        }
        if (null === $enclosure) {
            $enclosure = $this->enclosure;
        }

        if (null === $preferred) {
            $preferred = $this->auto_preferred;
        }

        if (empty($this->file_data)) {
            if ($this->checkData($file)) {
                $data = &$this->file_data;
            } else {
                return false;
            }
        } else {
            $data = &$this->file_data;
        }

        $chars = array();
        $strlen = strlen($data);
        $enclosed = false;
        $n = 1;
        $to_end = true;

        // walk specific depth finding posssible delimiter characters
        for ($i = 0; $i < $strlen; $i++) {
            $ch = $data{$i};
            $nch = ( isset($data{$i + 1}) ) ? $data{$i + 1} : false ;
            $pch = ( isset($data{$i -1}) ) ? $data{$i -1} : false ;

            // open and closing quotes
            if ($ch == $enclosure && ( ! $enclosed || $nch != $enclosure)) {
                $enclosed = ( $enclosed ) ? false : true ;

                // inline quotes
            } elseif ($ch == $enclosure && $enclosed) {
                $i++;

                // end of row
            } elseif (( "\n" == $ch && "\r" != $pch || "\r" == $ch ) && ! $enclosed) {
                if ($n >= $search_depth) {
                    $strlen = 0;
                    $to_end = false;
                } else {
                    $n++;
                }

                // count character
            } elseif (! $enclosed) {
                if (! preg_match('/['.preg_quote($this->auto_non_chars, '/').']/i', $ch)) {
                    if (! isset($chars[ $ch ][ $n ])) {
                        $chars[ $ch ][ $n ] = 1;
                    } else {
                        $chars[ $ch ][ $n ]++;
                    }
                }
            }
        }

        // filtering
        $depth = ( $to_end ) ? $n -1 : $n ;
        $filtered = array();
        foreach ($chars as $char => $value) {
            if ($match = $this->checkCount($char, $value, $depth, $preferred)) {
                $filtered[ $match ] = $char;
            }
        }

        // capture most probable delimiter
        ksort($filtered);
        $delimiter = reset($filtered);
        $this->delimiter = $delimiter;

        // parse data
        if ($parse) {
            $this->data = $this->parseString();
        }

        return $delimiter;

    }


    // ==============================================
    // ----- [ Core Functions ] ---------------------
    // ==============================================

    /**
     * Read file to string and call parse_string()
     * @param   file   local CSV file
     * @return  2D array with CSV data, or false on failure
     */
    public function parseFile($file = null)
    {
        if (null === $file) {
            $file = $this->file;
        }
        if (empty($this->file_data)) {
            $this->loadData($file);
        }
        return ( ! empty($this->file_data) ) ? $this->parseString() : false ;
    }

    /**
     * Parse CSV strings to arrays
     * @param   data   CSV string
     * @return  2D array with CSV data, or false on failure
     */
    public function parseString($data = null)
    {
        if (empty($data)) {
            if ($this->checkData()) {
                $data = &$this->file_data;
            } else {
                return false;
            }
        }

        $rows = array();
        $row = array();
        $row_count = 0;
        $current = '';
        $head = ( ! empty($this->fields) ) ? $this->fields : array() ;
        $col = 0;
        $enclosed = false;
        $was_enclosed = false;
        $strlen = strlen($data);

        // walk through each character
        for ($i = 0; $i < $strlen; $i++) {
            $ch = $data{$i};
            $nch = ( isset($data{$i + 1}) ) ? $data{$i + 1} : false ;
            $pch = ( isset($data{$i -1}) ) ? $data{$i -1} : false ;

            // open and closing quotes
            if ($ch == $this->enclosure && ( ! $enclosed || $nch != $this->enclosure)) {
                $enclosed = ( $enclosed ) ? false : true ;
                if ($enclosed) {
                    $was_enclosed = true;
                }

                // inline quotes
            } elseif ($ch == $this->enclosure && $enclosed) {
                $current .= $ch;
                $i++;

                // end of field/row
            } elseif (($ch == $this->delimiter || ( "\n" == $ch && "\r" != $pch ) || "\r" == $ch ) && ! $enclosed) {
                if (! $was_enclosed) {
                    $current = trim($current);
                }
                $key = ( ! empty($head[ $col ]) ) ? $head[ $col ] : $col ;
                $row[ $key ] = $current;
                $current = '';
                $col++;

                // end of row
                if ("\n" == $ch || "\r" == $ch) {
                    if ($this->validateOffset($row_count) && $this->validateRowConditions($row, $this->conditions)) {
                        if ($this->heading && empty($head)) {
                            $head = $row;
                        } elseif (empty($this->fields) || ( ! empty($this->fields) && (($this->heading && $row_count > 0) || ! $this->heading))) {
                            if (! empty($this->sort_by) && ! empty($row[ $this->sort_by ])) {
                                if (isset($rows[ $row[ $this->sort_by ] ])) {
                                    $rows[ $row[ $this->sort_by ].'_0' ] = &$rows[ $row[ $this->sort_by ] ];
                                    unset($rows[ $row[ $this->sort_by ] ]);
                                    for ($sn = 1; isset($rows[ $row[ $this->sort_by ].'_'.$sn ]); $sn++) {
                                    }
                                    $rows[ $row[ $this->sort_by ].'_'.$sn ] = $row;
                                } else {
                                    $rows[ $row[ $this->sort_by ] ] = $row;
                                }
                            } else {
                                $rows[] = $row;
                            }
                        }
                    }
                    $row = array();
                    $col = 0;
                    $row_count++;
                    if ($this->sort_by === null && $this->limit !== null && count($rows) == $this->limit) {
                        $i = $strlen;
                    }
                }

                // append character to current field
            } else {
                $current .= $ch;
            }
        }
        $this->titles = $head;
        if (! empty($this->sort_by)) {
            ( $this->sort_reverse ) ? krsort($rows) : ksort($rows);
            if ($this->offset !== null || $this->limit !== null) {
                $rows = array_slice($rows, ($this->offset === null ? 0 : $this->offset), $this->limit, true);
            }
        }
        return $rows;
    }

    /**
     * Create CSV data from array
     * @param   data        2D array with data
     * @param   fields      field names
     * @param   append      if true, field names will not be output
     * @param   is_php      if a php die() call should be put on the first
     *                      line of the file, this is later ignored when read.
     * @param   delimiter   field delimiter to use
     * @return  CSV data (text string)
     */
    public function unparse($data = array(), $fields = array(), $append = false, $is_php = false, $delimiter = null)
    {
        if (! is_array($data) || empty($data)) {
            $data = &$this->data;
        }
        if (! is_array($fields) || empty($fields)) {
            $fields = &$this->titles;
        }
        if (null === $delimiter) {
            $delimiter = $this->delimiter;
        }

        $string = ( $is_php ) ? "<?php header('Status: 403'); die(' '); ?>".$this->linefeed : '' ;
        $entry = array();

        // create heading
        if ($this->heading && ! $append) {
            foreach ($fields as $key => $value) {
                $entry[] = $this->encloseValue($value);
            }
            $string .= implode($delimiter, $entry).$this->linefeed;
            $entry = array();
        }

        // create data
        foreach ($data as $key => $row) {
            foreach ($row as $field => $value) {
                $entry[] = $this->encloseValue($value);
            }
            $string .= implode($delimiter, $entry).$this->linefeed;
            $entry = array();
        }

        return $string;
    }

    /**
     * Load local file or string
     * @param   input   local CSV file
     * @return  true or false
     */
    public function loadData($input = null)
    {
        $data = null;
        $file = null;
        if (null === $input) {
            $file = $this->file;
        } elseif (file_exists($input)) {
            $file = $input;
        } else {
            $data = $input;
        }
        if (! empty($data) || $data = $this->_rfile($file)) {
            if ($this->file != $file) {
                $this->file = $file;
            }
            if (preg_match('/\.php$/i', $file) && preg_match('/<\?.*?\?>(.*)/ims', $data, $strip)) {
                $data = ltrim($strip[1]);
            }
            if ($this->convert_encoding) {
                $data = iconv($this->input_encoding, $this->output_encoding, $data);
            }
            if (substr($data, -1) != "\n") {
                $data .= "\n";
            }
            $this->file_data = &$data;
            return true;
        }
        return false;
    }


    // ==============================================
    // ----- [ Internal Functions ] -----------------
    // ==============================================

    /**
     * Validate a row against specified conditions
     * @param   row          array with values from a row
     * @param   conditions   specified conditions that the row must match
     * @return  true of false
     */
    public function validateRowConditions($row = array(), $conditions = null)
    {
        if (! empty($row)) {
            if (! empty($conditions)) {
                $conditions = (strpos($conditions, ' OR ') !== false) ? explode(' OR ', $conditions) : array( $conditions ) ;
                $or = '';
                foreach ($conditions as $key => $value) {
                    if (strpos($value, ' AND ') !== false) {
                        $value = explode(' AND ', $value);
                        $and = '';
                        foreach ($value as $k => $v) {
                            $and .= $this->validateRowCondition($row, $v);
                        }
                        $or .= (strpos($and, '0') !== false) ? '0' : '1' ;
                    } else {
                        $or .= $this->validateRowCondition($row, $value);
                    }
                }
                return (strpos($or, '1') !== false) ? true : false ;
            }
            return true;
        }
        return false;
    }

    /**
     * Validate a row against a single condition
     * @param   row          array with values from a row
     * @param   condition   specified condition that the row must match
     * @return  true of false
     */
    public function validateRowCondition($row, $condition)
    {
        $operators = array(
            '=',
        'equals',
        'is',
            '!=',
        'is not',
            '<',
        'is less than',
            '>',
        'is greater than',
            '<=',
        'is less than or equals',
            '>=',
        'is greater than or equals',
            'contains',
            'does not contain',
        );
        $operators_regex = array();
        foreach ($operators as $value) {
            $operators_regex[] = preg_quote($value, '/');
        }
        $operators_regex = implode('|', $operators_regex);
        if (preg_match('/^(.+) ('.$operators_regex.') (.+)$/i', trim($condition), $capture)) {
            $field = $capture[1];
            $op = $capture[2];
            $value = $capture[3];
            if (preg_match('/^([\'\"]{1})(.*)([\'\"]{1})$/i', $value, $capture)) {
                if ($capture[1] == $capture[3]) {
                    $value = $capture[2];
                    $value = str_replace("\\n", "\n", $value);
                    $value = str_replace("\\r", "\r", $value);
                    $value = str_replace("\\t", "\t", $value);
                    $value = stripslashes($value);
                }
            }
            if (array_key_exists($field, $row)) {
                if (( '=' == $op || 'equals' == $op || 'is' == $op ) && $row[ $field ] == $value) {
                    return '1';
                } elseif (( '!=' == $op || 'is not' == $op ) && $row[ $field ] != $value) {
                    return '1';
                } elseif (( '<' == $op || 'is less than' == $op ) && $row[ $field ] < $value) {
                    return '1';
                } elseif (( '>' == $op || 'is greater than' == $op ) && $row[ $field ] > $value) {
                    return '1';
                } elseif (( '<=' == $op || 'is less than or equals' == $op ) && $row[ $field ] <= $value) {
                    return '1';
                } elseif (( '>=' == $op || 'is greater than or equals' == $op) && $row[ $field ] >= $value) {
                    return '1';
                } elseif ('contains' == $op  && preg_match('/'.preg_quote($value, '/').'/i', $row[ $field ])) {
                    return '1';
                } elseif ('does not contain' == $op && ! preg_match('/'.preg_quote($value, '/').'/i', $row[ $field ])) {
                    return '1';
                } else {
                    return '0';
                }
            }
        }
        return '1';
    }

    /**
     * Validates if the row is within the offset or not if sorting is disabled
     * @param   current_row   the current row number being processed
     * @return  true of false
     */
    public function validateOffset($current_row)
    {
        if ($this->sort_by === null && $this->offset !== null && $current_row < $this->offset) {
            return false;
        }
        return true;
    }

    /**
     * Enclose values if needed
     *  - only used by unparse()
     * @param   value   string to process
     * @return  Processed value
     */
    public function encloseValue($value = null)
    {
        if (null !== $value && '' != $value) {
            $delimiter = preg_quote($this->delimiter, '/');
            $enclosure = preg_quote($this->enclosure, '/');
            if (preg_match('/'.$delimiter.'|'.$enclosure."|\n|\r/i", $value) || ( ' ' == $value{0} ||  ' ' == substr($value, -1))) {
                $value = str_replace($this->enclosure, $this->enclosure.$this->enclosure, $value);
                $value = $this->enclosure.$value.$this->enclosure;
            }
        }
        return $value;
    }

    /**
     * Check file data
     * @param   file   local filename
     * @return  true or false
     */
    public function checkData($file = null)
    {
        if (empty($this->file_data)) {
            if (null === $file) {
                $file = $this->file;
            }
            return $this->loadData($file);
        }
        return true;
    }


    /**
     * Check if passed info might be delimiter
     *  - only used by find_delimiter()
     * @return  special string used for delimiter selection, or false
     */
    public function checkCount($char, $array, $depth, $preferred)
    {
        if (count($array) == $depth) {
            $first = null;
            $equal = null;
            $almost = false;
            foreach ($array as $key => $value) {
                if (null == $first) {
                    $first = $value;
                } elseif ($value == $first && false !== $equal) {
                    $equal = true;
                } elseif ($value == $first + 1 && false !== $equal) {
                    $equal = true;
                    $almost = true;
                } else {
                    $equal = false;
                }
            }
            if ($equal) {
                $match = ( $almost ) ? 2 : 1 ;
                $pref = strpos($preferred, $char);
                $pref = ( false !== $pref ) ? str_pad($pref, 3, '0', STR_PAD_LEFT) : '999' ;
                return $pref.$match.'.'.(99999 - str_pad($first, 5, '0', STR_PAD_LEFT));
            } else {
                return false;
            }
        }
    }

    /**
     * Read local file
     * @param   file   local filename
     * @return  Data from file, or false on failure
     */
    public function _rfile($file = null)
    {
        if (is_readable($file)) {
            if (! ($fh = fopen($file, 'r'))) {
                return false;
            }
            $data = fread($fh, filesize($file));
            fclose($fh);
            return $data;
        }
        return false;
    }

    /**
     * Write to local file
     * @param   file     local filename
     * @param   string   data to write to file
     * @param   mode     fopen() mode
     * @param   lock     flock() mode
     * @return  true or false
     */
    public function _wfile($file, $string = '', $mode = 'wb', $lock = 2)
    {
        if ($fp = fopen($file, $mode)) {
            flock($fp, $lock);
            $re = fwrite($fp, $string);
            $re2 = fclose($fp);
            if (false != $re && false != $re2) {
                return true;
            }
        }
        return false;
    }
}
