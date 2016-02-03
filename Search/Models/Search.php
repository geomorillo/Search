<?php

namespace Modules\Search\Models;

use Core\Model;

/**
 * Description of GenericModel
 *  
 * @author Geovanny
 */
class Search extends Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * @param  $dbInfo
     * @param  $query
     * @param  $current_page
     * @param  $items_per_page
     * @throws \Exception
     * @return array
     */
    public function getData($dbInfo, $query, $current_page, $items_per_page) {
        // get connection
        //$db = DB::getConnection($dbInfo);
        //self::$db = Database::get();
        $sql = "SELECT COUNT(*) AS COUNT FROM {$dbInfo['table']}";

        // append where clause if search columns is set in the config
        $whereClause = '';
        if (!empty($dbInfo['searchColumns'])) {
            $whereClause .= ' WHERE';
            $counter = 1;

            $binary = $dbInfo['caseSensitive'] == true ? 'BINARY' : '';

            switch ($dbInfo['comparisonOperator']) {
                case '=':
                    $comparisonOperator = '=';
                    break;
                case 'LIKE':
                    $comparisonOperator = 'LIKE';
                    break;
                default:
                    throw new \Exception('Comparison Operator is not valid');
            }

            foreach ($dbInfo['searchColumns'] as $searchColumn) {
                if ($counter == count($dbInfo['searchColumns'])) {
                    // last item
                    $whereClause .= " {$binary} {$searchColumn} {$comparisonOperator} :query{$counter}";
                } else {
                    $whereClause .= " {$binary} {$searchColumn} {$comparisonOperator} :query{$counter} OR";
                }

                ++$counter;
            }
            $sql .= $whereClause;
        }

        // get the number of total result
        // $stmt = $db->prepare($sql);

        if (!empty($whereClause)) {
            switch ($dbInfo['searchPattern']) {
                case 'q':
                    $search_query = $query;
                    break;
                case '*q':
                    $search_query = "%{$query}";
                    break;
                case 'q*':
                    $search_query = "{$query}%";
                    break;
                case '*q*':
                    $search_query = "%{$query}%";
                    break;
                default:
                    throw new \Exception('Search Pattern is not valid');
            }
            $bindParams = array();
            for ($i = 1; $i <= count($dbInfo['searchColumns']); ++$i) {

                $toBindQuery = ':query' . $i;
                $bindParams[$toBindQuery] = $search_query;
                //array(':nome' => '%'.$dados["nome"].'%')   
                // $toBindQuery = ':query' . $i;
                // $stmt->bindParam($toBindQuery, $search_query, \PDO::PARAM_STR);
            }
        }
        $result_count = $this->db->select($sql, $bindParams);
        //  $stmt->execute();
        $number_of_result = (int) $result_count{0}->COUNT;

        if (isset($dbInfo['maxResult']) && $number_of_result > $dbInfo['maxResult']) {
            $number_of_result = $dbInfo['maxResult'];
        }

        // initialize variables
        $HTML = '';
        $number_of_pages = 1;

        if (!empty($number_of_result) && $number_of_result !== 0) {
            if (!empty($dbInfo['filterResult'])) {
                $fromColumn = implode(',', $dbInfo['filterResult']);
            } else {
                $fromColumn = '*';
            }

            $baseSQL = "SELECT {$fromColumn} FROM {$dbInfo['table']}";

            if (!empty($whereClause)) {
                // set order by
                $orderBy = !empty($dbInfo['orderBy']) ? $dbInfo['orderBy'] : $dbInfo['searchColumns'][0];

                // set order direction
                $allowedOrderDirection = array('ASC', 'DESC');
                if (!empty($dbInfo['orderDirection']) && in_array($dbInfo['orderDirection'], $allowedOrderDirection)) {
                    $orderDirection = $dbInfo['orderDirection'];
                } else {
                    $orderDirection = 'ASC';
                }

                $baseSQL .= "{$whereClause} ORDER BY {$orderBy} {$orderDirection}";
            }

            if ($items_per_page === 0) {
                if (isset($dbInfo['maxResult'])) {
                    $baseSQL .= " LIMIT {$dbInfo['maxResult']}";
                }

                // show all
                $stmt = $baseSQL;
                $bindParams = array();
                if (!empty($whereClause)) {
                    for ($i = 1; $i <= count($dbInfo['searchColumns']); ++$i) {
                        $toBindQuery = ':query' . $i;
                        $bindParams[$toBindQuery] = $search_query;
                        //$stmt->bindParam($toBindQuery, $search_query, \PDO::PARAM_STR);
                    }
                }
            } else {
                /*
                 * pagination
                 *
                 * calculate total pages
                 */
                if ($number_of_result < $items_per_page) {
                    $number_of_pages = 1;
                } elseif ($number_of_result > $items_per_page) {
                    if ($number_of_result % $items_per_page === 0) {
                        $number_of_pages = floor($number_of_result / $items_per_page);
                    } else {
                        $number_of_pages = floor($number_of_result / $items_per_page) + 1;
                    }
                } else {
                    $number_of_pages = $number_of_result / $items_per_page;
                }

                if (isset($dbInfo['maxResult'])) {
                    // calculate the limit
                    if ($current_page == 1) {
                        if ($items_per_page > $dbInfo['maxResult']) {
                            $limit = $dbInfo['maxResult'];
                        } else {
                            $limit = $items_per_page;
                        }
                    } elseif ($current_page == $number_of_pages) {
                        // last page
                        $limit = $dbInfo['maxResult'] - (($current_page - 1) * $items_per_page);
                    } else {
                        $limit = $items_per_page;
                    }
                } else {
                    $limit = $items_per_page;
                }

                /*
                 * pagination
                 *
                 * calculate start
                 */
                $start = ($current_page > 0) ? ($current_page - 1) * $items_per_page : 0;
                $stmt = "{$baseSQL} LIMIT {$start}, {$limit}";
                //$stmt = $db->prepare( "{$baseSQL} LIMIT {$start}, {$limit}"  );

                if (!empty($whereClause)) {
                    $bindParams = array();
                    for ($i = 1; $i <= count($dbInfo['searchColumns']); ++$i) {
                        $toBindQuery = ':query' . $i;
                        $bindParams[$toBindQuery] = $search_query;
                        //$stmt->bindParam($toBindQuery, $search_query, \PDO::PARAM_STR);
                    }
                }
            }

            // run the query and get the result
            $results = $this->db->select($stmt, $bindParams);
            //$stmt->execute();
            //$results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (isset($dbInfo['displayHeader']['active']) && $dbInfo['displayHeader']['active'] == true) {
                $headers = array_keys($results[0]);

                $mapper = !empty($dbInfo['displayHeader']['mapper']) ? $dbInfo['displayHeader']['mapper'] : array();

                // generate header
                $HTML .= '<tr>';
                foreach ($headers as $aHeader) {
                    $aHeader = array_key_exists($aHeader, $mapper) ? $mapper[$aHeader] : $aHeader;
                    $HTML .= "<th>{$aHeader}</th>";
                }
                $HTML .= '</tr>';
            }

            // generate HTML
            foreach ($results as $result) {
                $HTML .= '<tr>';
                foreach ($result as $column) {
                    $HTML .= "<td>{$column}</td>";
                }
                $HTML .= '</tr>';
            }
        } else {
            // To prevent XSS prevention convert user input to HTML entities
            $query = htmlentities($query, ENT_NOQUOTES, 'UTF-8');

            // there is no result - return an appropriate message.
            $HTML .= "<tr><td>There is no result for \"{$query}\"</td></tr>";
        }

        // form the return
        return array(
            'html' => $HTML,
            'number_of_results' => (int) $number_of_result,
            'total_pages' => $number_of_pages,
        );
    }

}
