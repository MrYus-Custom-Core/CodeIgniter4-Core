<?php

// load library first
use App\Libraries\ExportExcel;

// ---------------------------------
// Export Excel With PHP SpreadSheet
function exportExcel($data, $option = [], $type = 'Standard') {
    $exportExcel = new ExportExcel($data, $option);

    if ($type == 'Standard') {
        $export = $exportExcel->exportStandard();

    } elseif ($type == 'Child') {
        $export = $exportExcel->exportChild();
    }

    return $export;
} // -------------------------------