<?php

/*
 * Todos los derechos reservados por Manuel Jhobanny Morillo Ordoñez 
 * 2016
 * Contacto: geomorillo@yahoo.com
 */

namespace Modules\Search\Controllers;

use Core\View;
use Core\Controller;
use Core\Router;
use Modules\Search\Controllers\Config as Config;

class Main extends Controller {

    private $data;
    private $handler;

    public function __construct() {
        parent::__construct();
        $this->data = null;
        $this->handler = new \Modules\Search\Controllers\Handler();
    }

    public function routes() {
        Router::any("search", 'Modules\Search\Controllers\Main@index');
        Router::any("start", 'Modules\Search\Controllers\Main@start');
        Router::any('process_livesearch', 'Modules\Search\Controllers\Main@process_livesearch');

    }

    public function index() {
        View::renderModule('Search/templates/search/header');
        View::renderModule('Search/views/search');
        View::renderModule('Search/templates/search/footer');
    }

    public function js() {
         echo "<script src='" . DIR . "app/Modules/Search/templates/search/js/main.js' type='text/javascript'></script>";
    }

    public function start() {
        $this->handler->getJavascriptAntiBot();
        $this->data['token'] = $this->handler->getToken();
        $this->data['time'] = time();
        $this->data['maxInputLength'] = Config::getConfig('maxInputLength');
        echo json_encode($this->data);
    }

    public function process_livesearch() {
        $controlOrigin = Config::getConfig('accessControlAllowOrigin');
        header("Access-Control-Allow-Origin: $controlOrigin");
        header('Access-Control-Allow-Methods: *');
        header('Content-Type: application/json');
        $errors = Handler::validateInput($_POST);

        if (!empty($errors)) {
            // Required inputs are not provided
            $this->handler->formResponse('failed', 'Error: Required or invalid inputs: ' . implode(',', $errors));
        }

// 2. A layer of security against those Bots that submit a form quickly
        if (!$this->handler->verifyBotSearched($_POST['ls_page_loaded_at'])) {
            // Searching is started sooner than the search start time offset
            $this->handler->formResponse('failed', 'Error: You are too fast, or this is a Bot. Please search now.');
        }

// 3. Verify the token - CSRF protection
        if (!$this->handler->verifySessionValue('token', $_POST['ls_token']) ||
                !$this->handler->verifySessionValue('anti_bot', $_POST['ls_anti_bot'])
        ) {
            // Tokens are not matched
            $this->handler->formResponse('failed', 'Error: Please refresh the page. It seems that your session is expired.');
        }

        try {
            // 4. Start looking for the query
            $result = json_encode($this->handler->getResult(
                            $_POST['ls_query_id'], $_POST['ls_query'], (int) $_POST['ls_current_page'], (int) $_POST['ls_items_per_page']
            ));
        } catch (\Exception $e) {
            $catchedError = $e->getMessage();
        }

        if (empty($catchedError)) {
            // 5. Return the result
            $this->handler->formResponse('success', 'Successful request', $result);
        } else {
            $this->handler->formResponse('failed', $catchedError);
        }
    }

}
