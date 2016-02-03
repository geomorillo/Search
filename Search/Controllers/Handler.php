<?php

namespace Modules\Search\Controllers;

use Modules\Search\Controllers\Config as Config;
use Helpers\Csrf as Csrf;


/**
 * Class Handler
 */
class Handler {

    protected $db;

    /**
     * returns a 32 bits token and resets the old token if exists
     *
     * @return string
     */
    public function getToken() {
        return $_SESSION['ls_session']['token'] = Csrf::makeToken();
        ;
    }

    /**
     * receives a posted variable and checks it against the same one in the session
     *
     * @param  $session_parameter
     * @param  $session_value
     * @return bool
     */
    public function verifySessionValue($session_parameter, $session_value) {
        $white_list = array('token', 'anti_bot');

        if (in_array($session_parameter, $white_list) &&
                $_SESSION['ls_session'][$session_parameter] === $session_value
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * checks required fields, max length for search input and numbers for pagination
     *
     * @param  $input_array
     * @return array
     */
    public function validateInput($input_array) {
        $error = array();

        foreach ($input_array as $k => $v) {
            if (!isset($v) || (trim($v) == '' && $v != '0') || $v == null) {
                array_push($error, $k);
            } elseif ($k === 'ls_current_page' || $k === 'ls_items_per_page') {
                if ((int) $v < 0) {
                    array_push($error, $k);
                }
            } elseif ($k === 'ls_query' && strlen($v) > Config::getConfig('maxInputLength')) {
                array_push($error, $k);
            }
        }

        return $error;
    }

    /**
     * forms the response object including
     * status (success or failed)
     * message
     * result (html result)
     *
     * @param $status
     * @param $message
     * @param string     $result
     */
    public function formResponse($status, $message, $result = '') {
        $css_class = ($status === 'failed') ? 'error' : 'success';

        $message = "<tr><td class='{$css_class}'>{$message}</td></tr>";

        echo json_encode(array('status' => $status, 'message' => $message, 'result' => $result));
    }

    /**
     * @param     $query_id: This is html id
     * @param     $query
     * @param int $current_page
     * @param int $items_per_page
     *
     * @return array
     * @throws \Exception
     */
    public function getResult($query_id, $query, $current_page = 1, $items_per_page = 0) {
        // get data sources list
        $dataSources = Config::getConfig('dataSources');

        if (!isset($dataSources[$query_id])) {
            throw new \Exception("There is no data info for {$query_id}");
        }

        // get info for the selected data source
        $dbInfo = $dataSources[$query_id];
        $this->db = new \Modules\Search\Models\Search(); //\Models\Search();
        
        return $this->db->getData($dbInfo, $query, $current_page, $items_per_page);
    }


    /**
     * @param $dbInfo
     * @param $query
     * @param $current_page
     * @param $items_per_page
     * @return array
     * @throws \Exception
     */

    /**
     * @return string
     */
    public function getJavascriptAntiBot() {
        return $_SESSION['ls_session']['anti_bot'] = Config::getConfig('antiBot');
    }

    /**
     * Calculate the timestamp difference between the time page is loaded
     * and the time searching is started for the first time in seconds
     *
     * @param  $page_loaded_at
     * @return bool
     */
    public function verifyBotSearched($page_loaded_at) {
        // if searching starts less than start time offset it seems it's a Bot
        return (time() - $page_loaded_at < Config::getConfig('searchStartTimeOffset')) ? false : true;
    }

}
