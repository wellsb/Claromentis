<?php

/**
 *
 * @author Brendan Wells brendan.wells6@gmail.com
  * 
 * Main App.php
 * 
 */

// Pull in the Report / Filer classes
require_once(dirname(__FILE__) ."/Report.class.php");

// Be careful of namespaces
use Report\Reporter;

// Instantiate reporter class into object 
$csvReport = new Reporter($_FILES);

// Create or resume session handler
session_start();

// Start output buffer early so we can catch all headers going out before doing a redirect
ob_start();

// Hand raw uploaded file over for validation
$uploadedFile = $csvReport->handleUpload($_FILES);

// Read the validated file into an array
$csvAsArray = $csvReport->parseCsvUpload($uploadedFile);

// Perform calculations and produce reports and hand over values for display via SESSION
// (read README on how this could have been done better)
$totals = $csvReport->calculateExpenseReport($csvAsArray);
$_SESSION['totals']['simple'] = $totals['simple'];
$_SESSION['totals']['detail'] = $totals['detail'];

// Hand over the generated report via SESSION
// (read README on how this could have been done better)
$_SESSION['DownloadLinkSimple'] = $csvReport->handleDownload($totals['simple'], 'simple');
$_SESSION['DownloadLinkDetail'] = $csvReport->handleDownload($totals['detail'], 'detail');

// Return the user back to the front end
$csvReport->Redirect('../index.php', false);

// Flush the output buffer with the new header location added
ob_end_flush();
