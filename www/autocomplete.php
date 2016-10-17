<?php
	/*	File:		autocomplete.php
	*	Version:	4.1
	*	Date:		21.Jan.2010
	*       $Revision: 216 $
	*	FINDOLOGIC GmbH
	*/

	/* check if the server allows fopen with URLs */
	if (!ini_get('allow_url_fopen')) {
		die('allow_url_fopen is not enabled, please check your server config');
	}

	// e.g.              "ABCDEFABCDEFABCDEFABCDEFABCDEFAB"
	define("FL_SHOP_ID", "2B0FBF92DA961920C586D75B93A4C1FF");
	// e.g. "http://srvXY.findologic.com/ps/mein-laden.de/"
	// for OXID Shops use "http://srvXY.findologic.com/ps/xml/" instead of shop URL
	define("FL_SERVICE_URL", "service.findologic.com/ps/xml_2.0");

	// get the revision this was created from
	define("FL_REVISION", "May 29, 2015 14:34");

	/*
	 *	do http-request
	 */
	$parameters = $_GET;
	$parameters['shopkey'] = FL_SHOP_ID;
	$parameters['revision_timestamp'] = FL_REVISION;

	/* manually pass the arg_separator as '&' to avoid problems with different configurations */
	$url = "http://".FL_SERVICE_URL."/autocomplete.php?" . http_build_query($parameters, '', '&');

	/* manually pass the arg_separator as '&' to avoid problems with different configurations */
	$handle = fopen($url,'r');

	/* check if the connection to the autocomplete service was successful */
	if ($handle === false) {
		die('Could not connect to search service, please check your shop config');
	}

	/* get the Content-type (which includes the charset) from the http response and pass it through */
	$meta_data = stream_get_meta_data($handle);
	$meta_data = $meta_data['wrapper_data'];
	$meta_data = array_values(preg_grep('/Content-Type/', $meta_data));
	if (count($meta_data) == 1) {
		header($meta_data[0]);
	}

	if (!$handle) {
		$content = "";
	} else {
		$content = "";
		while (!feof($handle)) {
			$content .= fread($handle, 512);
		}
		fclose($handle);
	}

	echo $content;
