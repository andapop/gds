<?php
/* Ispired by Users to CSV plugin by Yoast http://wordpress.org/extend/plugins/users-to-csv/
*/

function gds_valToCsvHelper($val, $separator, $trimFunction) {
		if ($trimFunction) $val = $trimFunction($val);
		//If there is a separator (;) or a quote (") or a linebreak in the string, we need to quote it.
		$needQuote = FALSE;
		do {
			if (strpos($val, '"') !== FALSE) {
				$val = str_replace('"', '""', $val);
				$needQuote = TRUE;
				break;
			}
			if (strpos($val, $separator) !== FALSE) {
				$needQuote = TRUE;
				break;
			}
			if ((strpos($val, "\n") !== FALSE) || (strpos($val, "\r") !== FALSE)) { // \r is for mac
				$needQuote = TRUE;
				break;
			}
		} 
		while (FALSE);
		if ($needQuote) {
			$val = '"' . $val . '"';
		}
		return $val;
	}

	function gds_arrayToCsvString($array, $separator=';', $trim='both', $removeEmptyLines=TRUE) {
		if (!is_array($array) || empty($array)) return '';
		switch ($trim) {
			case 'none':
				$trimFunction = FALSE;
				break;
			case 'left':
				$trimFunction = 'ltrim';
				break;
			case 'right':
				$trimFunction = 'rtrim';
				break;
			default: //'both':
				$trimFunction = 'trim';
			break;
		}
		$ret = array();
		reset($array);
		if (is_array(current($array))) {
			while (list(,$lineArr) = each($array)) {
				if (!is_array($lineArr)) {
					//Could issue a warning ...
					$ret[] = array();
				} else {
					$subArr = array();
					while (list(,$val) = each($lineArr)) {
						$val      = gds_valToCsvHelper($val, $separator, $trimFunction);
						$subArr[] = $val;
					}
				}
				$ret[] = join($separator, $subArr);
			}
			$crlf = gds_define_newline();
			return join($crlf, $ret);
		} else {
			while (list(,$val) = each($array)) {
				$val   = gds_valToCsvHelper($val, $separator, $trimFunction);
				$ret[] = $val;
			}
			return join($separator, $ret);
		}
	}

	function gds_define_newline() {
		$unewline = "\r\n";
		if (strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'win')) {
		   $unewline = "\r\n";
		} else if (strstr(strtolower($_SERVER["HTTP_USER_AGENT"]), 'mac')) {
		   $unewline = "\r";
		} else {
		   $unewline = "\n";
		}
		return $unewline;
	}

	function gds_get_browser_type() {
		$USER_BROWSER_AGENT="";

		if (ereg('OPERA(/| )([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT='OPERA';
		} else if (ereg('MSIE ([0-9].[0-9]{1,2})',strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT='IE';
		} else if (ereg('OMNIWEB/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT='OMNIWEB';
		} else if (ereg('MOZILLA/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
			$USER_BROWSER_AGENT='MOZILLA';
		} else if (ereg('KONQUEROR/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
	    	$USER_BROWSER_AGENT='KONQUEROR';
		} else {
	    	$USER_BROWSER_AGENT='OTHER';
		}

		return $USER_BROWSER_AGENT;
	}

	function gds_get_mime_type() {
		$USER_BROWSER_AGENT= gds_get_browser_type();

		$mime_type = ($USER_BROWSER_AGENT == 'IE' || $USER_BROWSER_AGENT == 'OPERA')
			? 'application/octetstream'
			: 'application/octet-stream';
		return $mime_type;
	}

	function gds_createcsv($lo_id = false, $hi_id = false) {
		global $wpdb;

		$sep = ";";

		// Get the columns and create the first row of the CSV
		$fields = array('Chian ID','Passcode','Date Created');

		$csv = gds_arrayToCsvString($fields, $sep);
		$csv .= gds_define_newline();

		// Query the entire contents from the Chains table and put it into the CSV

		if((false === $lo_id) || (false === $hi_id)){
			$query = "SELECT ID, passcode, date_created FROM $wpdb->chains WHERE exported = false";	
		} else {
			$query = "SELECT ID, passcode, date_created FROM $wpdb->chains WHERE ID >= $lo_id AND ID <= $hi_id";
		}			

		$results = $wpdb->get_results($query,ARRAY_A);
				
		$csv .= gds_arrayToCsvString($results, $sep);

		$now = gmdate('D, d M Y H:i:s') . ' GMT';

		$export_date = date('d_M_Y_H-i-s');

		header('Content-Type: ' . gds_get_mime_type());
		header('Expires: ' . $now);

		header('Content-Disposition: attachment; filename="export_'.$export_date.'.csv"');
		header('Pragma: no-cache');


		echo $csv;
	}


	function gds_all_createcsv() {
		global $wpdb;

		$sep = ";";

		// Get the columns and create the first row of the CSV
		$fields = array('Chian ID','Passcode','Date Created');

		$csv = gds_arrayToCsvString($fields, $sep);
		$csv .= gds_define_newline();

		// Query the entire contents from the Chains table and put it into the CSV
		$query = "SELECT ID, passcode, date_created FROM $wpdb->chains WHERE exported = false";

		$results = $wpdb->get_results($query,ARRAY_A);
				
		$csv .= gds_arrayToCsvString($results, $sep);

		$now = gmdate('D, d M Y H:i:s') . ' GMT';

		$export_date = date('d_M_Y_H-i-s');

		header('Content-Type: ' . gds_get_mime_type());
		header('Expires: ' . $now);

		header('Content-Disposition: attachment; filename="export_'.$export_date.'.csv"');
		header('Pragma: no-cache');


		echo $csv;
	}

	
?>