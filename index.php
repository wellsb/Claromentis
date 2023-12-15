<?php

/**
 *
 * @author Brendan Wells brendan.wells6@gmail.com
  * 
 * A simple renderer in the absence of a templating system
 * 
 */

// Create or resume session handler
session_start();
if (isset($_SESSION['totals'])) {
    $totals['simple'] = $_SESSION['totals']['simple'];
    $totals['detail'] = $_SESSION['totals']['detail'];
}

echo <<<HEADER
<!DOCTYPE html>
<html lang="en-US">
<head><title>CsvReporter</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="Description" content="A small tool that allows a user to upload expense leger and receive back an expense report">
<style>
    table td, th 
    {
        height: 50px; 
        width: 100px;
        border: 1px solid black;
    }
</style>
</head>
HEADER;

echo <<<BODY
<body>
    <div id="UploadForm">
        <form action="src/App.php" method="post" enctype="multipart/form-data">
            Select CSV file to upload:<br>
            <input type="file" name="fileUpload" id="fileUpload">
            <br>
            <input type="submit" value="Upload CSV" name="submit">
        </form>
    </div>
BODY;

// Simple
if (isset($totals['simple'])) {
    echo "<br><table>";
    foreach ($totals['simple'] as $Category => $Total) {
        echo "<tr><td>$Category</td><td>$Total</td></tr>";
    }
    echo "</table>";
    
    if (isset($_SESSION['DownloadLinkSimple'])) {
        echo <<<DOWNLOADLINK
            <div id="DownloadLink">
            <a href="{$_SESSION['DownloadLinkSimple']}">Download This Report</a>
            </div>
        DOWNLOADLINK;
    }
}

if (isset($totals['detail'])) {
    if (isset($_GET['showall'])) {
        echo "<br><table><tr><td></td><td>Total</td><td>Vat</td><td>TotalIncVat</td><td>Occurs</td></tr>";
        foreach ($totals['detail']['runningTotalByCategory'] as $Category => $Total) {
            echo "
            <tr>
                <td>$Category</td><td>$Total</td>
                <td>".$totals['detail']['runningTotalByCategoryVat'][$Category]."</td>
                <td>".$totals['detail']['runningTotalByCategoryInclVat'][$Category]."</td>
                <td>".$totals['detail']['runningTotalByOccurance'][$Category]."</td>
            </tr>";
        }
        echo "</table>";

        echo "<br><table>";
        foreach ($totals['detail']['Gross'] as $Category => $Total) {
            echo "<tr><td>$Category</td><td>$Total</td></tr>";
        }
        echo "</table>
        <a href=\"index.php\">Hide Detail</a>";

        if (isset($_SESSION['DownloadLinkDetail'])) {
            echo <<<DOWNLOADLINK
                <div id="DownloadLink">
                <a href="{$_SESSION['DownloadLinkDetail']}">Download This Report</a>
                </div>
            DOWNLOADLINK;
        }
    } else {
        echo "<a href=\"index.php?showall=1\">Show Detail</a>";
    }
}

echo <<<FOOTER
</body>
</html>
FOOTER;
