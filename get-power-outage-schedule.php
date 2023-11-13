<?php
/* Email addresses of information recipients */
$mail_to = [
	'mail@example.com',
	'mail2@company.org'
];

/* Sender email details */
$mail_subject = 'Power outage schedule in Miami';
$mail_from_name = 'Miami Electricity Supplier';
$mail_from = 'electro@miami-example.com';

/* Html source */
$url = 'https://www.miami-example.com/htmlpages/Show/overview/subordinate/kjvbdskjv/54543';

/* Search phrase */
$search_word = 'miami';

/* Table data headers "<table>" to form structured information from the received data */
$start_date = 'START DATE AND TIME: ' . "\n";
$end_date = 'END DATE AND TIME: ' . "\n";
$planned_emergency = 'PLANNED/EMERGENCY: ';
$location = 'LOCALITY: ' . "\n";
$contact = 'CONTACT NUMBER: ' . "\n";

$result = '';

/* File for recording formatted received information */
$schedule = dirname( __FILE__ ) . '/power-outage-schedule.txt';

/* Initial data */
$html = file_get_contents( $url );

/* Specified confines of an html page */
preg_match( '/<div class=\"nav-path\">([\s\S]*)<\/table>(?=[\s\S]*<\/div>[\s]+<div class=\"error_help\">)/', $html, $matches );
$html = "\xEF\xBB\xBF" . $matches[0];

/* Html to Xml converting */
$doc = new DOMDocument();
$doc->loadHTML( $html );
$sxml = simplexml_import_dom( $doc );
$sxml = json_decode( json_encode( $sxml ), TRUE );

/* Parsing and generation of structured information */
$trows = $sxml['body']['div'][1]['table']['tbody']['tr'];
foreach( $trows as $trow ) {
	$search_result = mb_stripos( $trow['td'][3], $search_word, 0, 'UTF-8' );
	if( $search_result ) {
		$result .= '---------------------------------------------------------------------' . "\n\n\n" .
		$start_date . preg_replace( '!\s++!u', ' ', $trow['td'][4] ) . "\n\n" . $end_date . preg_replace( '!\s++!u', ' ', $trow['td'][5] ) . "\n\n" .
		$planned_emergency . preg_replace( '!\s++!u', ' ', $trow['td'][2] ) . "\n\n" . $location . preg_replace( '!\s++!u', ' ', $trow['td'][3] ) . "\n\n" .
		$contact . preg_replace( '!\s++!u', ' ', $trow['td'][6] ) . "\n\n\n";
	}
}

if( empty( $result ) ) {
	exit;
}

$result .= '---------------------------------------------------------------------';

/* Sending information by email and putting in a file */
if( !file_exists( $schedule ) || $result !== file_get_contents( $schedule ) ) {
	$headers = 'From: =?UTF-8?B?' . base64_encode( $mail_from_name ) . '?= <' . $mail_from . '>' . "\r\n" .
		'BCC:' . implode( ',', $mail_to ) . "\r\n" .
		'Content-Type: text/plain; charset=utf-8';
	mail( NULL, $mail_subject, $result, $headers );
	file_put_contents( $schedule, $result );
}
?>
