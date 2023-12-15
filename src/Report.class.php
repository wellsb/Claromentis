<?php

/**
 *
 * @author Brendan Wells brendan.wells6@gmail.com
 *
 * Report class
 *
 */

namespace Report;

use Files\Filer;

require_once(dirname(__FILE__) ."/Filer.class.php");

/**
 *
 * Class to parse input CSV data
 * perform calculations for output CSV
 * generated hash for uniqueness
 * 
 * @return void
 * 
 */
class Reporter extends Filer
{

    // @const int current VAT rate
    const VATRATE = 20;

    const ALLOWEDCATEGORIES  = [
        "Hotel", "Fuel", "Food", "Parking"
    ];

    /**
     *
     * Take uploaded csvUploadFile and parse to an array
     * 
     * @param   string   $csvUploadFile The csv file
     * 
     * @return  array   $csvAsArray The file as an array
     * 
     */
    public function parseCsvUpload($csvUploadFile)
    {
        if (!isset($csvUploadFile)) {
            throw new \Exception('csv missing');
        } else {
            ini_set('auto_detect_line_endings', TRUE);
            $handle = fopen($csvUploadFile, 'r');

            while (($data = fgetcsv($handle)) !== FALSE ) {
                $csvAsArray[] = $data;
            }

            ini_set('auto_detect_line_endings', FALSE);

            if (!isset($csvAsArray)) {
                throw new \Exception('error understanding csv data');
            } else if(!is_array($csvAsArray)) {
                throw new \Exception('error parsing csv data');
             } else {
                return $csvAsArray;
            }
        }
    }

    /**
     *
     * Perform the actual calculations
     * 
     * @param   array   $csvUploadFile The input csv array
     * 
     * @return  array   $totals Two reports, one simple, one detailed
    *
    */
    public function calculateExpenseReport($csvData)
    {

        // declare array to fill with report data
        $totals = [];

        // Work through csv rows and calculate running totals
        foreach ($csvData as $csvRow) {
        
            if (in_array($csvRow[0], self::ALLOWEDCATEGORIES)) {

                // Calculate running total
                @$totals['Gross']['runningTotal'] += ($csvRow[1] * $csvRow[2]);
        
                // Calculate running total by category of expense
                @$totals['runningTotalByCategory'][$csvRow[0]] += ($csvRow[1] * $csvRow[2]);
        
                // Calculate running total of VAT by category of expense (assuming the same VAT rate for all item types for brevity)
                @$totals['runningTotalByCategoryVat'][$csvRow[0]] = ($totals['runningTotalByCategory'][$csvRow[0]] / 100 * self::VATRATE);
        
                // Calculate running total by category of expense with VAT added
                @$totals['runningTotalByCategoryInclVat'][$csvRow[0]]
                = ($totals['runningTotalByCategory'][$csvRow[0]] + $totals['runningTotalByCategoryVat'][$csvRow[0]]);
        
                // Calculate running total of occurance of each type of expenditure
                @$totals['runningTotalByOccurance'][$csvRow[0]] = $totals['runningTotalByOccurance'][$csvRow[0]] += $csvRow[2];
            } else {
                throw new \Exception('Unsupported expense category detected');
            }
        
            // Calculate total VAT
            @$totals['Gross']['totalVat'] = ($totals['Gross']['runningTotal'] / 100 * self::VATRATE);

            // Calculate grand total with Vat
            @$totals['Gross']['total'] = $totals['Gross']['runningTotal'] + $totals['Gross']['totalVat'];
        }

        if (!isset($totals)) {
            throw new \Exception('Problam generating report');
        } else {
            return $totals = [
                'simple' => $totals['runningTotalByCategory'],
                'detail' => $totals
            ];
        }
    }

    /**
     *
     * Helper function to perform page redirrects as long
     * as headers have not already been sent
     * 
     * @param   string   $url Where to go next
     * @param   string   $permanent nature of redirect
     * 
     * @return  void
    *
    */
    public function Redirect($url, $permanent = false)
    {
        if (headers_sent() === false)
        {
            header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
        } else {
            echo "headers sent";
        }
    }
}