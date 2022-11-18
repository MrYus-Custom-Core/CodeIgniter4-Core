<?php 

// ! QUERY BUILDER GET METHOD FEATURE :
// ? 1. Get Detail Data =
// *      - Define Variable Result 
// *        example = $query['data'] = 'data'
//
// *      - Select Field as with Array
// *        example = $query['select'] = ['field' => 'as_name', ...]
//
// *      - Join Table with Array
// *        example = $query['join'] = ['tablename' => 'field_1 = field_2']
//
// *      - Group By Query
// *        example = $query['groupBy'] = 'field, field_2, ...'
//
// *      - Get softDeteled Data 
// *        example = $query['withDeleted'] = true | false
//
// *      - Sanitize Result
// *        json decoded of object or array, formated date
//
// *        Call This Function with generateDetailData
//
// ? 2. Get List Data = 
// *      - Define Variable Result
// *        example = $query['data'] = 'data'
//
// *      - Select Field as with Array
// *        example = $query['select'] = ['field' => 'as_name', ...]
//
// *      - Join Table with Array
// *        example = $query['join'] = ['tablename' => 'field_1 = field_2']
//
// *      - Group By Query
// *        example = $query['groupBy'] = 'field, field_2, ...'
//
// *      - Search With 'search' Params & Search Query
// *        example = $query['search'] = ['field_1', 'field_2', ...] || parmas = base_url?search=value
//
// *      - Filter With Params
// *        example = base_url?as_name=value
//
// *      - Filter Range Date with startDate and enDate Params
// *        example = base_url?startDate=data&&endDate=date
//
// *      - Filter with Filter array Params
// *        example = base_url?filter[]['type']=string|date&&filter[]['field']=as_name&&filter[]['value']=value&&filter[]['comparison']='='|'<'|'<='|'>='|'<>'|'bet'
//
// *      - Order Data by Default
// *        example = $query['order'] = '-field', start with - for descending
//
// *      - Order Data by Params & Replace Default Order
// *        example = base_url?sort=-as_name , start with - for descending
//
// *      - Pagination with Custom Page Name and Limit
// *        example = $query['pagination'] = ['status' => true|false, 'page' => 'pageName', 'limit' => 10]
//
// *      - Get softDeteled Data
// *        example = $query['onlyDeleted'] = true, to get only softDeleted data || $query['withDeleted'] = true to get with softDeleted data 
//
// *      - rowCount 
// *        Total Data Count
//
// *      - Sanitize Result
// *        json decoded of object or array, formated date
//
// *        Call This Function with generateListData


// ! ---- QUERY BUILDER GET METHOD START ----

// * ===========================================
// ? Function to generate one data from database
function generateDetailData($query, $model, $debug = false) {
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

    // * --------------
    // ? Building Query

    // ? Set Select
    if (!empty($selectQuery)) {
        selectField($selectQuery, $builder);
    }
    // ? Set Join
    if (!empty($joinQuery)) {
        joinTable($joinQuery, $builder);
    }
    // ? set where
    if (!empty($whereQuery)) {
        whereDetail($whereQuery, $builder);
    }
    // ? Set Group By
    if (!empty($groupByQuery)) {
        groupByQuery($groupByQuery, $builder);
    }
    // ? Set With Deleted
    if ($withDeleted) {
        $builder->getWithDeleted();
    }
    $builder->limit(1);
    // ? End QUery Builder
    // * -----------------

    // Get Result QUery
    $result = $builder->get();
    $result = $result->getResultArray(false);
    $data[$dataQuery] = sanitizeQueryResult($result);

    // * ----------------------------
    // ? Get Last Query if Debug true
    if ($debug) {
        print_r('<pre>');
        print_r($builder->getLastQuery());
    }

    // ? Return Result
    return $data;
} // * =========================================

// * =============================================
// ? Function to generate data list from data base
function generateListData($params, $query, $model, $debug = false) {
    $builder = $model;
    $rowCount = generateCountListData($params, $query, $builder);
    // ? Setup Params Data
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

    // ? Set Pagination
    $pagination = true;
    $pageName = 'current';
    $currentPage = 1;
    $pageLimit = 10;
    // ? Pagination from params
    if (isset($params['pagination_bool'])) {
        $pagination = $params['pagination_bool'] == '1' ? true : false;
        unset($params['pagination_bool']);
    }
    // ? Pagination With Query
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

    // * --------------
    // ? Building Query
    // ? Set Select
    if (!empty($selectQuery)) {
        selectField($selectQuery, $builder);
    }
    // ? Set Join
    if (!empty($joinQuery)) {
        joinTable($joinQuery, $builder);
    }
    // ? Set Where
    if (!empty($whereQuery)) {
        whereDetail($whereQuery, $builder);
    }
    // ? Set Group By
    if (!empty($groupByQuery)) {
        groupByQuery($groupByQuery, $builder);
    }
    // ? Set Search
    if (!empty($searchQuery) && !empty($search)) {
        searchField($search, $searchQuery, $builder);
    }
    // ? Set Filter Date
    if (!empty($startDate) && !empty($endDate)) {
        filterDate($startDate, $endDate, $selectQuery, $builder);
    }
    // ? Set Filter Array
    if (!empty($filter)) {
        filterArray($filter, $selectQuery, $builder);
    }
    // ? Set Order Query
    if (!empty($orderQuery) & empty($sort)) {
        orderField($orderQuery, $builder);
    }
    // ? Set Sort Data with Params
    if (!empty($sort)) {
        sortField($sort, $selectQuery, $builder);
    }
    // ? Set Filter Params
    if (!empty($params)) {
        filterParams($params, $selectQuery, $builder);
    }

    // ? Set Only Deleted
    if ($onlyDeleted) {
        $builder->getOnlyDeleted();
    }
    // ? Set With Deleted
    if ($withDeleted) {
        $builder->getWithDeleted();
    }
    
    // ? Set Pagination if true
    if ($pagination) {
        $result = $builder->paginate($pageLimit, $pageName);
    } else {
        $result = $builder->get();
        $result = $result->getResultArray();
    }
    // ? End Query Builder
    // * -----------------

    // * ----------------------------
    // ? Get Last Query if debug true
    if ($debug) {
        print_r('<pre>');
        print_r($builder->getLastQuery());
    }

    // * -------------
    // ? Building Data
    $data['rowCount'] = $rowCount;
    $data[$dataQuery] = sanitizeQueryResult($result);

    if ($pagination) {
        $data['pager'] = $model->pager;
        $data['currentPage'] = $currentPage;
        $data['page'] = $pageName;
        $data['limit'] = $pageLimit;
    }
    // * -------------

    // ? return data
    return  $data;
} // * ===========================================

// * =============================================
// ? Function to generate data list from data base
function generateCountListData($params, $query, $model, $debug = false) {
    $builder = $model;
    // ? Setup Params Data
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

    // ? Set Pagination
    $pagination = true;
    $pageName = 'current';
    $currentPage = 1;
    $pageLimit = 10;
    // ? Pagination from params
    if (isset($params['pagination_bool'])) {
        $pagination = $params['pagination_bool'] == '1' ? true : false;
        unset($params['pagination_bool']);
    }
    // ? Pagination With Query
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

    // * --------------
    // ? Building Query
    // ? Set Select
    if (!empty($selectQuery)) {
        selectField($selectQuery, $builder);
    }
    // ? Set Join
    if (!empty($joinQuery)) {
        joinTable($joinQuery, $builder);
    }
    // ? Set Where
    if (!empty($whereQuery)) {
        whereDetail($whereQuery, $builder);
    }
    // ? Set Group By
    if (!empty($groupByQuery)) {
        groupByQuery($groupByQuery, $builder);
    }
    // ? Set Search
    if (!empty($searchQuery) && !empty($search)) {
        searchField($search, $searchQuery, $builder);
    }
    // ? Set Filter Date
    if (!empty($startDate) && !empty($endDate)) {
        filterDate($startDate, $endDate, $selectQuery, $builder);
    }
    // ? Set Filter Array
    if (!empty($filter)) {
        filterArray($filter, $selectQuery, $builder);
    }
    // ? Set Order Query
    if (!empty($orderQuery) & empty($sort)) {
        orderField($orderQuery, $builder);
    }
    // ? Set Sort Data with Params
    if (!empty($sort)) {
        sortField($sort, $selectQuery, $builder);
    }
    // ? Set Filter Params
    if (!empty($params)) {
        filterParams($params, $selectQuery, $builder);
    }

    // ? Set Only Deleted
    if ($onlyDeleted) {
        $builder->getOnlyDeleted();
    }
    // ? Set With Deleted
    if ($withDeleted) {
        $builder->getWithDeleted();
    }
    $rowCount = $builder->countAllResults();
    return $rowCount;
} // * ===========================================


// * -----------------------------
// ? Generate Select Query Builder
function selectField($selectQuery = [], $builder) {
    foreach ($selectQuery as $key => $value) {
        $builder->select("{$key} AS {$value}");
    }
    return $builder;
} // * ---------------------------

// * ---------------------------
// ? Generate Join Query Builder
function joinTable($joinQUery = [], $builder) {
    if (!empty($joinQUery)) {
        foreach($joinQUery as $key => $value) {
            $builder->join($key, $value);
        }
        return $builder;
    }
} // * -------------------------

// * -------------------------------
// ? Generate Group By Query Builder
function groupByQuery($groupByQuery, $builder) {
    $builder->groupBy($groupByQuery);
    return $builder;
} // * -----------------------------

// * -----------------------------
// ? Generate Search Query Builder
function searchField($search, $searchQuery, $builder) {
    foreach($searchQuery as $key => $value) {
        $builder->orLike($value, $search);
    }
    return $builder;
} // * ---------------------------

// * ---------------------------- 
// ? Generate Where Query Builder
function whereDetail($whereQuery, $builder) {
    foreach ($whereQuery as $key => $value) {
        $builder->where($key, $value);
    }
    return $builder;
} // * --------------------------

// * --------------------------------------
// ? Generate Query Builder Filter By Array
function filterArray($filter = [], $selectQuery ,$builder) {
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
                                    if (validateDate($startDate) && validateDate($endDate)) {
                                        $builder->where("{$keyQuery} BETWEEN '{$startDate}' AND '{$endDate}'");
                                    }
                                } else {
                                    if (validateDate($startDate)) {
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
                                    if (validateDate($startDate, 'Y-m-d H:i:s') && validateDate($endDate, 'Y-m-d H:i:s')) {
                                        $builder->where("{$keyQuery} BETWEEN '{$startDate}' AND '{$endDate}'");
                                    } else if (validateDate($startDate) && validateDate($endDate)) {
                                        $builder->where("{$keyQuery} BETWEEN '{$startDate}' AND '{$endDate}'");
                                    }
                                } else {
                                    if (validateDate($startDate, 'Y-m-d H:i:s')) {
                                        $builder->where("{$keyQuery} {$comparison} '{$startDate}'");
                                    } else if (validateDate($startDate)) {
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
} // * ------------------------------------

// * ---------------------------------------
// ? Generate Query Builder Filter By Params
function filterParams($params, $selectQuery, $builder) {
    foreach ($selectQuery as $keyQuery => $valueQuery) {
        foreach ($params as $key => $value) {
            if (endsWith($keyQuery, $key)) {
                if (endsWith($key, 'datetime')) {
                    if (validateDate($value)) {
                        $keyQuery = "DATE({$keyQuery})";
                    } else {
                        if (!validateDate($value, 'Y-m-d H:i:s')) {
                            $value = '';
                        }
                    }
                }
                if (endsWith($keyQuery, 'date')) {
                    if (!validateDate($value)) {
                        $value = '';
                    }
                }
                $builder->where("{$keyQuery} = '{$value}'");
            }
        }
    }
    return $builder;
} // * -------------------------------------

// * --------------------------------------------------------
// ? Generate Query Filter Date By startDate & endDate Params
function filterDate($startDate, $endDate, $selectQuery, $builder) {
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
} // * ------------------------------------------------------

// * ------------------------------------
// ? Generate Query Builder OrderBy Field
function orderField($orderQuery, $builder) {
    foreach ($orderQuery as $key => $value) {
        if (startsWith($value, '-')) {
            $field = str_replace('-', '', $value);
            $builder->orderBy($field, 'DESC');
        } else {
            $builder->orderBy($value, 'ASC');
        }
    }
    return $builder;
} // * ----------------------------------

// * -------------------------------------------
// ? Generate Query Builder OrderBy using Params
function sortField($sort, $selectQuery, $builder) {
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
} // * -----------------------------------------

// * ------------------------------------
// ? Sanitazion Data Result Query Builder
function sanitizeQueryResult($data) {
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
                $data[$keyData][$key] = dateFormatter($value);
            }
            if (endsWith($key, 'datetime')) {
                if ($value == '0000-00-00 00:00:00' || null == $value || empty($value)) {
                    $data[$keyData][$key] = '';
                }
                $data[$keyData][$key] = dateFormatter($value);
            }
        }
    }
    return $data;
} // * ----------------------------------

// ! ---- END QUERY BUILDER GET METHOD ----