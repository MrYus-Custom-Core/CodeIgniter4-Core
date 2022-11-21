<?php

// ? HELPER Contains Functions : 
// * - Date validation === validateDate
// * - Generate slug ==== generateSlug 
// * - Generate Code From DB === generateCode
// * - Generate Unique Code with AlphaNum === generateUniqueCode
// * - Sanitize Phone Number === sanitizePhoneNumber
// * - Generate Duration Label === generateDurationLabel
// * - Generate Duration form 2 date === generateDuration
// * - Number formater with Prefix === numberId
// * - Export Excel with PHP SpreadSheet Library === exportExcel
// * - Format Date from 2022-01-01 to 01 January 2022 === dateFormatter
// * - Encrypt Data To Hex using CI4 Encrypter === encryptToHex
// * - Decrypt Data From Hex using CI4 Encrypter === decryptFromHex
// * - Encrypt Data using CI4 Encrypter === encrypterCI
// * - Decrypt Data using CI4 Encrypter === decrypterCI

// * ----- START HELPER FUNCTION -----

// * ---------------
// ? Date Validation
function validateDate($date, $format = 'Y-m-d') {
    if($format == 'Y-m'){
        $date = $date . '-01';
        $format = 'Y-m-d';
    }
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
} // * -------------

// * ----------------------------------
// ? Generating Slug form Name or Title
function generateSlug($text) {
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

    $text = trim($text, '-');

    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    $text = strtolower($text);

    $text = preg_replace('~[^-\w]+~', '', $text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
} // * --------------------------------

// * ---------------
// ? Generating Code
function generateCode($model, $field, $useDate = false, $prefix = '', $digit = 5, $where = []) {
    $year = date('y');
    $month = date('m');
    $day = date('d');
    $date = $year.$month.$day;

    // * -------------------
    // ? Query Get Last Code
    $builder = $model;
    // ? set where
    if (!empty($whereQuery)) {
        whereDetail($where, $builder);
    }
    if ($useDate === true) {
        $builder->like($field, $date);
    }
    $builder->select($field);
    $builder->limit(1);
    $builder->orderBy($field, 'DESC');
    $code = $builder->first();

    // * -----------
    // ?Create Code
    if (count($code) > 0) {
        $lastCode = $code[$field];
        $start = strlen($lastCode) - $digit;
        $oldCode = '';
            
        for ($x = $start; $x < strlen($lastCode); $x++) {
            $oldCode .= $lastCode[$x];
        }
        $newCode = sprintf("%0{$digit}d", (int)$oldCode + 1);
        if (!empty($prefix) && $useDate === true) {
            $code = $prefix."-".$date." -".$newCode;
        } elseif (empty($prefix) && $useDate  === true) {
            $code = $date."-".$newCode;
        } elseif (!empty($prefix) && $useDate === false) {
            $code = $prefix."-".$newCode;
        } else {
            $code = $newCode;
        }
    } else {
        $newCode = sprintf("%0{$digit}d", 1);
        $code = $prefix."-".$date."-000001";
        if (!empty($prefix) && $useDate  === true) {
            $code = $prefix."-".$date." -".$newCode;
        } elseif (!empty($prefix) && !$useDate) {
            $code = $date."-".$newCode;
        } elseif (!empty($prefix) && $useDate === false) {
            $code = $prefix."-".$newCode;
        } else {
            $code = $newCode;
        }
    }
    return $code;
} // * -------------

// * ----------------------
// ? Generating Unique Code
function generateUniqueCode($lenght = 6, $option = 'full') {
    if ($option == 'full') {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    } elseif($option == 'numNoLower') {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    } elseif($option == 'numNoUpper') {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    } elseif($option == 'alpha') {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    } elseif($option == 'alphaLower') {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
    } elseif($option == 'alphaUpper') {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    } elseif($option == 'num') {
        $characters = '0123456789';
    } else {
        return false;
    }
    $charactersLength = strlen($characters);
    $code = '';
    for ($i = 0; $i < $lenght; $i++) {
        $code .= $characters[rand(0, $charactersLength - 1)];
    }
    return $code;
} // * --------------------

// * ---------------------
// ? Sanitize Phone Number
function sanitizePhoneNumber($no, $code = '62') {
    $no = str_replace(' ', '', $no);
    $no = str_replace('-', '', $no);
    if (startsWith($no, '+')) {
        $no = ltrim($no, "+");
    }
    $tempNo = '';
    if (startsWith($no, $code)) {
        $tempNo = $no;
    } else {
        if (startsWith($no, '0')) {
        $tempNo = $code . ltrim($no, "0");
        } else {
        $tempNo = $code . $no;
        }
    }
    return $tempNo != '' ? $tempNo : $no;
} // * -------------------

// * -----------------------
// ? Generate Duration Label
function generateDurationLabel($duration, $type = 'seconds') {
    if ($type == 'hours') {
        $days = floor($duration/24);
        $hours = $duration%24;
        return "$days hari $hours jam";
    } elseif ($type = 'minutes') {
        $days = floor($duration/1440);
        $hours = floor($duration/24)%60%1;
        $minutes = floor($duration/60)%60;
        return "$days hari $hours jam $minutes menit";
    } elseif ($type = 'seconds') {
        $days = floor($duration/86400);
        $hours = floor($duration/24)%60%60%1;
        $minutes = floor($duration/60)%60;
        $seconds = $duration%60;
        return "$days hari $hours jam $minutes menit $seconds detik";
    }
    return false;
} // * ---------------------

// * -----------------
// ? Generate Duration
function generateDuration($dateOne, $dateTwo, $type = 'seconds') {
    $endTime = strtotime($dateOne);
    $startTime = strtotime($dateTwo);
    $duration = 0;
    $hours = 60 * 60;
    $minutes = 60;
    if ($endTime > $startTime) {
        $duration = $endTime - $startTime;
    } else {
        $duration = $startTime - $endTime;
    }

    if ($type == 'hours') {
        if ($duration > 0) {
            $duration = $duration / $hours;
        }
        return $duration;
    } elseif ($type == 'minutes') {
        if ($duration > 0) {
            $duration = $duration / $minutes;
        }
        return $duration;
    } elseif ($type == 'seconds') {
        return $duration;
    }
    return false;
} // * ---------------

// * ----------------------------------
// ? Generate Format Number From String
function numberId($str, $prefix = '', $decimal = 0) {
    return $prefix . number_format($str, $decimal, ',', '.');
} // * --------------------------------

// * ---------------------
// ? Generate Welcome Word
function sayHello() {
    $time = date('H:i');
    if ($time >= '00:00' && $time <= '02:59') {
        return 'malam';
    }
    if ($time >= '03:00' && $time <= '09:59') {
        return 'pagi';
    }
    if ($time >= '10:00' && $time <= '14:59') {
        return 'siang';
    }
    if ($time >= '15:00' && $time <= '18:59') {
        return 'Sore';
    }
    if ($time >= '19:00' && $time <= '23:59') {
        return 'malam';
    }
} // * -------------------

// * ----------------------------
// ? Format Date to Readable Date
function dateFormatter($datetime, $lang = 'id_ID', $type = IntlDateFormatter::FULL, $type2 = IntlDateFormatter::FULL, $timezone = null, $calendar = null, $format = 'yyyy MMM dd HH:mm') {
    $formater = datefmt_create($lang, $type, $type2, $timezone, $calendar, $format);
    $formatedDate = datefmt_format($formater, strtotime($datetime));
    return $formatedDate;
} // * --------------------------

// * ------------------------
// ? Get Full URL with Params
function currentFullUrl() {
    $currentURL = current_url();
    $params   = $_SERVER['QUERY_STRING'];
    $fullURL = $currentURL . '?' . $params;
    return $fullURL;
} // * ----------------------

// * -------------------------------------
// ? Encrypt Data To Hex with CI Encrypter
function encryptToHex($data) {
    if (!empty($data)) {
        $data = encrypterCI(json_encode($data));
        $data = bin2hex($data);
        return $data;
    } else {
        return '';
    }
} // * -----------------------------------

// * ---------------------------------------
// ? Decrypt Data From Hex with CI Encrypter
function decryptFromHex($data) {
    if (!empty($data)) {
        $data = hex2bin($data);
        $data = json_decode(decrypterCI($data));
        return $data;
    } else {
        return '';
    }
} // * ------------------------------------

// * ------------------------------
// ? Encrypt Data with CI Encrypter
function encrypterCI($data) {
    if (!empty($data)) {
        $encrypt = \Config\Services::encrypter();
        $data = $encrypt->encrypt($data);
        return $data;
    } else {
        return '';
    }
} // * ----------------------------

// * ------------------------------
// ? Decrypt Data with CI Encrypter
function decrypterCI($data) {
    if (!empty($data)) {
        $encrypt = \Config\Services::encrypter();
        $data = $encrypt->decrypt($data);
        return $data;
    } else {
        return '';
    }
} // * ----------------------------