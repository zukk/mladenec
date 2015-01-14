<?php
  if (!isset($_REQUEST['hase']) || $_REQUEST['hase'] != '00kiKfiMWSHrg0559')
{
  echo 'Доступ запрещен!';
  exit;
  die();
}    
function writeReportLog($report='', $fname='all'){
	$report = date('y-m-d h:i:s') . "\n------\n" . $report;
	$fp1 = fopen($fname."_log.htm", "w");
	fwrite($fp1, $report);
	fclose($fp1);
}
?>