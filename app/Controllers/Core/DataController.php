<?php
// Namespace
namespace App\Controllers\Core;

// Extend Base Controller
use App\Controllers\Core\BaseController;

// Load Models
use App\Models\UserModel;
use App\Models\UserDetailModel;

// Load Library
use App\Libraries\ExportExcel;

// etc
use DateTime;
use IntlDateFormatter;

class DataController extends BaseController {

    protected $userModel;
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userDetailModel = new UserDetailModel();
    }

    // QUERY BUILDER GET METHOD FEATURE :
    // 1. Get Detail Data =
    //      - Define Variable Result 
    //        example = $query['data'] = 'data'
    // 
    //      - Select Field as with Array
    //        example = $query['select'] = ['field' => 'as_name', ...]
    // 
    //      - Join Table with Array
    //        example = $query['join'] = ['tablename' => 'field_1 = field_2']
    // 
    //      - Group By Query
    //        example = $query['groupBy'] = 'field, field_2, ...'
    // 
    //      - Get softDeteled Data 
    //        example = $query['withDeleted'] = true | false
    // 
    //      - Sanitize Result
    //        json decoded of object or array, formated date
    // 
    //        Call This Function with $this->generateDetailData
    // 
    // 2. Get List Data = 
    //      - Define Variable Result
    //        example = $query['data'] = 'data'
    // 
    //      - Select Field as with Array
    //        example = $query['select'] = ['field' => 'as_name', ...]
    // 
    //      - Join Table with Array
    //        example = $query['join'] = ['tablename' => 'field_1 = field_2']
    // 
    //      - Group By Query
    //        example = $query['groupBy'] = 'field, field_2, ...'
    // 
    //      - Search With 'search' Params & Search Query
    //        example = $query['search'] = ['field_1', 'field_2', ...] || parmas = base_url?search=value
    // 
    //      - Filter With Params
    //        example = base_url?as_name=value
    // 
    //      - Filter Range Date with startDate and enDate Params
    //        example = base_url?startDate=data&&endDate=date
    // 
    //      - Filter with Filter array Params
    //        example = base_url?filter[]['type']=string|date&&filter[]['field']=as_name&&filter[]['value']=value&&filter[]['comparison']='='|'<'|'<='|'>='|'<>'|'bet'
    // 
    //      - Order Data by Default
    //        example = $query['order'] = '-field', start with - for descending
    // 
    //      - Order Data by Params & Replace Default Order
    //        example = base_url?sort=-as_name , start with - for descending
    // 
    //      - Pagination with Custom Page Name and Limit
    //        example = $query['pagination'] = ['status' => true|false, 'page' => 'pageName', 'limit' => 10]
    // 
    //      - Get softDeteled Data
    //        example = $query['onlyDeleted'] = true, to get only softDeleted data || $query['withDeleted'] = true to get with softDeleted data 
    // 
    //      - rowCount 
    //        Total Data Count
    // 
    //      - Sanitize Result
    //        json decoded of object or array, formated date
    // 
    //        Call This Function with $this->generateListData


    // ---- QUERY BUILDER GET METHOD START ----

    // ===========================================
    // Function to generate one data from database
    public function generateDetailData($query, $model, $debug = false) {
        $builder = $model;

        // Setup Query Data
        $dataQuery = isset($query['data']) ? $query['data'] : 'data';
        $selectQuery = isset($query['select']) ? $query['select'] : '';
        $joinQuery = isset($query['join']) ? $query['join'] : [];
        $whereQuery = isset($query['where']) ? $query['where'] : '';
        $groupByQuery = isset($query['groupBy']) ? $query['groupBy'] : '';
        // filter deleted
        $withDeleted = isset($query['with_deleted']) ? $query['with_deleted'] : false;

        $data = [];

        // --------------
        // Building Query

        // Set Select
        if (!empty($selectQuery)) {
            $this->selectField($selectQuery, $builder);
        }
        // Set Join
        if (!empty($joinQuery)) {
            $this->joinTable($joinQuery, $builder);
        }
        // set where
        if (!empty($whereQuery)) {
            $this->whereDetail($whereQuery, $builder);
        }
        // Set Group By
        if (!empty($groupByQuery)) {
            $this->groupByQuery($groupByQuery, $builder);
        }
        // Set With Deleted
        if ($withDeleted) {
            $builder->getWithDeleted();
        }
        $builder->limit(1);
        // End QUery Builder
        // -----------------

        // Get Result QUery
        $builder = $builder->get();
        $result = $builder->getResultArray();
        $data[$dataQuery] = $this->sanitizeQueryResult($result);

        // ----------------------------
        // Get Last Query if Debug true
        if ($debug) {
            print_r('<pre>');
            print_r($builder->getCompiledSelect());
        }

        // Return Result
        return $data;
    } // =========================================

    // =============================================
    // Function to generate data list from data base
    public function generateListData($params, $query, $model, $debug = false) {
        $builder = $model;
        // Setup Params Data
        $startDate = isset($params['startDate']) ? $params['startDate'] : '';
        unset($params['startDate']);
        $endDate = isset($params['endDate']) ? $params['endDate'] : '';
        unset($params['endDate']);
        $search = isset($params['search']) ? $params['search'] : '';
        unset($params['search']);
        $filter = isset($params['filter']) ? $params['filter'] : '';
        unset($params['filter']);
        $sort = isset($params['sort']) ? $params['sort'] : '';
        unset($params['sort']);

        // Set Pagination
        $pagination = true;
        $pageName = 'current';
        $currentPage = 1;
        $pageLimit = 10;
        // Pagination from params
        if (isset($params['pagination_bool'])) {
            $pagination = $params['pagination_bool'] == '1' ? true : false;
            unset($params['pagination_bool']);
        }
        // Pagination With Query
        unset($params['pagination_bool']);
        if (isset($query['pagination'])) {
            $paginate = $query['pagination'];
            if (isset($paginate['status']) && !empty($paginate['status'])) {
                $pagination = $paginate['status'] == '1' ? true : false;
            }
            if (isset($paginate['page'])) {
                $pageName = !empty($paginate['page']) ? $paginate['page'] : 'current';
            }
            if (isset($paginate['limit'])) {
                $pageLimit = !empty($paginate['limit']) ? $paginate['limit'] : 10;
            }
        }
        $currentPage = isset($params["page_'{$pageName}'"]) ? $params["page_'{$pageName}'"] : 1;

        // Setup Query Data
        $dataQuery = isset($query['data']) ? $query['data'] : 'data';
        $selectQuery = isset($query['select']) ? $query['select'] : '';
        $joinQuery = isset($query['join']) ? $query['join'] : [];
        $whereQuery = isset($query['where']) ? $query['where'] : '';
        $groupByQuery = isset($query['groupBy']) ? $query['groupBy'] : '';
        $searchQuery = isset($query['search']) ? $query['search'] : '';
        $orderQuery = isset($query['order']) ? $query['order'] : [];
        // filter deleted
        $onlyDeleted = isset($query['onlyDeleted']) ? $query['onlyDeleted'] : false;
        $withDeleted = isset($query['withDeleted']) ? $query['withDeleted'] : false;

        $data = [];

        // --------------
        // Building Query
        // Set Select
        if (!empty($selectQuery)) {
            $this->selectField($selectQuery, $builder);
        }
        // Set Join
        if (!empty($joinQuery)) {
            $this->joinTable($joinQuery, $builder);
        }
        // Set Where
        if (!empty($whereQuery)) {
            $this->whereDetail($whereQuery, $builder);
        }
        // Set Group By
        if (!empty($groupByQuery)) {
            $this->groupByQuery($groupByQuery, $builder);
        }
        // Set Search
        if (!empty($searchQuery) && !empty($search)) {
            $this->searchField($search, $searchQuery, $builder);
        }
        // Set Filter Date
        if (!empty($startDate) && !empty($endDate)) {
            $this->filterDate($startDate, $endDate, $selectQuery, $builder);
        }
        // Set Filter Array
        if (!empty($filter)) {
            $this->filterArray($filter, $selectQuery, $builder);
        }
        // Set Order Query
        if (!empty($orderQuery) & empty($sort)) {
            $this->orderField($orderQuery, $builder);
        }
        // Set Sort Data with Params
        if (!empty($sort)) {
            $this->sortField($sort, $selectQuery, $builder);
        }
        // Set Filter Params
        if (!empty($params)) {
            $this->filterParams($params, $selectQuery, $builder);
        }

        // Set Only Deleted
        if ($onlyDeleted) {
            $builder->getOnlyDeleted();
        }
        // Set With Deleted
        if ($withDeleted) {
            $builder->getWithDeleted();
        }
        // Set Pagination if true
        if ($pagination) {
            $result = $builder->paginate($pageLimit, $pageName);
            $rowCount = $builder->countAllResults();
        } else {
            $builder = $builder->get();
            $result = $builder->getResultArray();
            $rowCount = $builder->countAllResults();
        }
        // End Query Builder
        // -----------------

        // ----------------------------
        // Get Last Query if debug true
        if ($debug) {
            print_r('<pre>');
            print_r($builder->getCompiledSelect());
        }

        // -------------
        // Building Data
        $data['rowCount'] = (string) $rowCount;
        $data[$dataQuery] = $this->sanitizeQueryResult($result);

        if ($pagination) {
        	$data['pager'] = $model->pager;
			$data['currentPage'] = $currentPage;
            $data['page'] = $pageName;
			$data['limit'] = $pageLimit;
        }
        // -------------

        // return data
        return  $data;
    } // ===========================================
    
    // -----------------------------
    // Generate Select Query Builder
    private function selectField($selectQuery = [], $builder) {
        foreach ($selectQuery as $key => $value) {
            $builder->select("{$key} AS {$value}");
        }
        return $builder;
    } // ---------------------------

    // ---------------------------
    // Generate Join Query Builder
    private function joinTable($joinQUery = [], $builder) {
        if (!empty($joinQUery)) {
            foreach($joinQUery as $key => $value) {
                $builder->join($key, $value);
            }
            return $builder;
        }
    } // -------------------------

    // -------------------------------
    // Generate Group By Query Builder
    private function groupByQuery($groupByQuery, $builder) {
        $builder->groupBy($groupByQuery);
        return $builder;
    } // -----------------------------
    
    // -----------------------------
    // Generate Search Query Builder
    private function searchField($search, $searchQuery, $builder) {
        foreach($searchQuery as $key => $value) {
            $builder->orLike($value, $search);
        }
        return $builder;
    } // ---------------------------

    //----------------------------- 
    // Generate Where Query Builder
    private function whereDetail($whereQuery, $builder) {
        foreach ($whereQuery as $key => $value) {
            $builder->where($key, $value);
        }
        return $builder;
    } //---------------------------

    // --------------------------------------
    // Generate Query Builder Filter By Array
    private function filterArray($filter = [], $selectQuery ,$builder) {
        if (!empty($filter)) {
            foreach ($selectQuery as $keyQuery => $valueQuery) {
                foreach($filter as $key => $value) {
                    $field = $value['field'];
                    $comparison = $value['comparison'];
                    $var = $value['value'];
                    $type = $value['type'];

                    if (endsWith($keyQuery, $field)) {
                        switch ($type) {
                            case 'string':
                            $allowedComparison = array('=', '<', '>', '<>', '!=');
                            switch($comparison) {
                                case '=':
                                    $builder->where($keyQuery, $var);
                                    break;
                                case '!=':
                                    $builder->whereNot($keyQuery, $var);
                                    break;
                                case '<':
                                    $builder->like($keyQuery, $var, 'before');
                                    break;
                                case '>':
                                    $builder->like($keyQuery, $var, 'after');
                                    break;
                                case '<>':
                                    $builder->like($keyQuery, $var);
                                    break;
                            }
                            case 'numeric':
                                if (is_numeric($var)) {
                                    $allowedComparison = array('=', '<', '>', '<=', '>=', '<>');
                                }
                                if (!in_array($comparison, $allowedComparison)) {
                                  $comparison = '=';
                                }
                                $builder->where("{$keyQuery} {$comparison} '{$var}'");
                                break;
                            case 'boolean':
                                $value = $value == 'true' ? '1' : '0';
                                $builder->where($keyQuery, $var);
                                break;
                            case 'date':
                                if (endsWith($field, 'date')) {
                                    $startDate = '';
                                    $endDate = '';
                                    if (strstr($var, '::')) {
                                        $date_value = explode('::', $var);
                                        $startDate = $date_value[0];
                                        $endDate = $date_value[1];
                                    } else {
                                        $startDate = $value;
                                    }
                                    $allowedComparison = array('=', '<', '>', '<=', '>=', '<>', 'bet');
                                    if (!in_array($comparison, $allowedComparison)) {
                                        $comparison = '=';
                                    }
                                    if ($comparison == 'bet') {
                                        if ($this->validateDate($startDate) && $this->validateDate($endDate)) {
                                            $builder->where("{$keyQuery} BETWEEN '{$startDate}' AND '{$endDate}'");
                                        }
                                    } else {
                                        if ($this->validateDate($startDate)) {
                                            $builder->where("{$keyQuery} {$comparison} '{$startDate}'");
                                        }
                                    }
                                }
                                
                                if (endsWith($field, 'datetime')) {
                                    $startDate = '';
                                    $endDate = '';
                                    if (strstr($var, '::')) {
                                        $date_value = explode('::', $var);
                                        $startDate = $date_value[0];
                                        $endDate = $date_value[1];
                                    } else {
                                        $startDate = $value;
                                    }
                    
                                    $arr_allowed = array('=', '<', '>', '<=', '>=', '<>', 'bet');
                                    if (!in_array($comparison, $arr_allowed)) {
                                    $comparison = '=';
                                    }
                                    if ($comparison == 'bet') {
                                        if ($this->validateDate($startDate, 'Y-m-d H:i:s') && $this->validateDate($endDate, 'Y-m-d H:i:s')) {
                                            $builder->where("{$keyQuery} BETWEEN '{$startDate}' AND '{$endDate}'");
                                        } else if ($this->validateDate($startDate) && $this->validateDate($endDate)) {
                                            $builder->where("{$keyQuery} BETWEEN '{$startDate}' AND '{$endDate}'");
                                        }
                                    } else {
                                        if ($this->validateDate($startDate, 'Y-m-d H:i:s')) {
                                            $builder->where("{$keyQuery} {$comparison} '{$startDate}'");
                                        } else if ($this->validateDate($startDate)) {
                                            $builder->where("{$keyQuery} {$comparison} '{$startDate}'");
                                        }
                                    }
                                }
                        }
                    }
                }
            }
            return $builder;
        }
    } // ------------------------------------

    // ---------------------------------------
    // Generate Query Builder Filter By Params
    private function filterParams($params, $selectQuery, $builder) {
        foreach ($selectQuery as $keyQuery => $valueQuery) {
            foreach ($params as $key => $value) {
                if (endsWith($keyQuery, $key)) {
					if (endsWith($key, 'datetime')) {
						if ($this->validateDate($value)) {
							$keyQuery = "DATE($keyQuery)";
						} else {
							if (!$this->validateDate($value, 'Y-m-d H:i:s')) {
								$value = '';
							}
						}
					}
					if (endsWith($keyQuery, 'date')) {
						if (!$this->validateDate($value)) {
							$value = '';
						}
					}
					$builder->where($keyQuery, $value);
                }
            }
        }
        return $builder;
    } // -------------------------------------

    // --------------------------------------------------------
    // Generate Query Filter Date By startDate & endDate Params
    private function filterDate($startDate, $endDate, $selectQuery, $builder) {
        foreach ($selectQuery as $key => $value) {
            if (endsWith($key, 'date')) {
                if($startDate != $endDate) {
                    $builder->where("{$key} BETWEEN '{$startDate}' AND '{$endDate}'");
                } else {
                    $builder->where($key, $startDate);
                }
            } elseif(endsWith($key, 'datetime')) {
                if($startDate != $endDate) {
                    $builder->where("DATE({$key}) BETWEEN '{$startDate}' AND '{$endDate}'");
                } else {
                    $builder->where($key, $startDate);
                }
            }
        }
        return $builder;
    } // ------------------------------------------------------

    // ------------------------------------
    // Generate Query Builder OrderBy Field
    private function orderField($orderQuery, $builder) {
        foreach ($orderQuery as $key => $value) {
            if (startsWith($value, '-')) {
                $field = str_replace('-', '', $value);
                $builder->orderBy($field, 'DESC');
            } else {
                $builder->orderBy($value, 'ASC');
            }
        }
        return $builder;
    } // ----------------------------------

    // -------------------------------------------
    // Generate Query Builder OrderBy using Params
    private function sortField($sort, $selectQuery, $builder) {
        if (startsWith($sort, '-')) {
            $field = str_replace('-', '', $sort);
            $order = 'DESC';
        } else {
            $field = $sort;
            $order = 'ASC';
        }
        foreach ($selectQuery as $key => $value) {
            if (endsWith($key, $field)) {
                $builder->orderBy($key, $order);
            }
        }
        return $builder;
    } // -----------------------------------------

    // ------------------------------------
    // Sanitazion Data Result Query Builder
    private function sanitizeQueryResult($data) {
        foreach ($data as $keyData => $valData) {
            foreach ($valData as $key => $value) {
                if (is_null($valData)) {
                    $data[$keyData][$key] = '';
                }
                if (endsWith($key, 'object')) {
                    $data[$keyData][$key] = json_decode(empty($val) ? '{}' : $val);
                }
                if (endsWith($key, 'array')) {
                    $data[$keyData][$key] = json_decode(empty($val) ? '[]' : $val);
                }
                if (endsWith($key, 'date')) {
                    if ($value == '0000-00-00' || null == $value || empty($value)) {
                        $data[$keyData][$key] = '';
                    }
                    $data[$keyData][$key] = $this->dateFormatter($value);
                }
                if (endsWith($key, 'datetime')) {
                    if ($value == '0000-00-00 00:00:00' || null == $value || empty($value)) {
                        $data[$keyData][$key] = '';
                    }
                    $data[$keyData][$key] = $this->dateFormatter($value);
                }
            }
        }
        return $data;
    } // ----------------------------------

    // ---- END QUERY BUILDER GET METHOD ----


    // HELPER FUNCTION CONTAINS : 
    // - Date validation === $this->validateDate
    // - Generate slug ==== $this->generateSlug 
    // - Generate Code From DB === $this->generateCode
    // - Generate Unique Code with AlphaNum === $this->generateUniqueCode
    // - Sanitize Phone Number === $this->sanitizePhoneNumber
    // - Generate Duration Label === $this->generateDurationLabel
    // - Generate Duration form 2 date === $this->generateDuration
    // - Number formater with Prefix === $this->numberId
    // - Export Excel with PHP SpreadSheet Library === $this->exportExcel
    // - Format Date from 2022-01-01 to 01 January 2022 === $this->dateFormatter

    // ----- START HELPER FUNCTION -----

    // ---------------
    // Date Validation
    protected function validateDate($date, $format = 'Y-m-d') {
        if($format == 'Y-m'){
            $date = $date . '-01';
            $format = 'Y-m-d';
        }
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    } // -------------

    // ----------------------------------
    // Generating Slug form Name or Title
    protected function generateSlug($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
    
        // trim
        $text = trim($text, '-');
    
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    
        // lowercase
        $text = strtolower($text);
    
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
    
        if (empty($text)) {
            return 'n-a';
        }
    
        return $text;
    } // --------------------------------

    // ---------------
    // Generating Code
    protected function generateCode($model, $field, $useDate = false, $prefix = '', $digit = 5, $where = []) {
        $year = date('y');
        $month = date('m');
        $day = date('d');
        $date = $year.$month.$day;

        // -------------------
        // Query Get Last Code
        $builder = $model;
        // set where
        if (!empty($whereQuery)) {
            $this->whereDetail($where, $builder);
        }
        if ($useDate === true) {
            $builder->like($field, $date);
        }
        $builder->select($field);
        $builder->limit(1);
        $builder->orderBy($field, 'DESC');
        $code = $builder->first();
    
        // -----------
        // Create Code
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
    } // -------------

    // ----------------------
    // Generating Unique Code
    protected function generateUniqueCode($lenght = 6, $option = 'full') {
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
    } // --------------------

    // ---------------------
    // Sanitize Phone Number
    protected function sanitizePhoneNumber($no, $code = '62') {
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
    } // -------------------

    // -----------------------
    // Generate Duration Label
    protected function generateDurationLabel($duration, $type = 'seconds') {
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
    } // ---------------------

    // -----------------
    // Generate Duration
    protected function generateDuration($dateOne, $dateTwo, $type = 'seconds') {
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
    } // ---------------

    // ----------------------------------
    // Generate Format Number From String
    protected function numberId($str, $prefix = '', $decimal = 0) {
        return $prefix . number_format($str, $decimal, ',', '.');
    } // --------------------------------

    // ---------------------
    // Generate Welcome Word
    protected function sayHello() {
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
    } // -------------------

    // ---------------------------------
	// Export Excel With PHP SpreadSheet
	protected function exportExcel($data, $option = [], $type = 'Standard') {
		$exportExcel = new ExportExcel($data, $option);

		if ($type == 'Standard') {
			$export = $exportExcel->exportStandard();

		} elseif ($type == 'Child') {
			$export = $exportExcel->exportChild();
		}

		return $export;
	} // -------------------------------

    // ----------------------------
    // Format Date to Readable Date
    protected function dateFormatter($datetime, $lang = 'id_ID', $type = 'FULL', $hour = null, $region = null, $format = 'dd MMM yyy HH:mm') {
        if ($type == 'FULL') {
            $IntlDateFormatter = IntlDateFormatter::FULL;
        } elseif ($type == 'SHORT') {
            $IntlDateFormatter = IntlDateFormatter::SHORT;
        } elseif ($type == 'MEDIUM') {
            $IntlDateFormatter = IntlDateFormatter::MEDIUM;
        } elseif ($type == 'LONG') {
            $IntlDateFormatter = IntlDateFormatter::MEDIUM;
        }
        $formater = new IntlDateFormatter($lang, $IntlDateFormatter, $IntlDateFormatter, $hour, $region, $format);
        $formatedDate = $formater->format(strtotime($datetime));
        return $formatedDate;
    } // --------------------------

    // ----- END HELPER FUNCTION -----
    
}