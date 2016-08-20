<?php

namespace Core;

class App {
	
    public static $DB;


    public function run()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Pragma: no-cache"); // HTTP/1.0

        /** @var \ExpenseTracker\Router $router */
        $router = DI::get('router');
        /** @var \ExpenseTracker\Http\Request $reqeust */
        $request = DI::get('request');
        $router->handle($request);

        /** @var \Core\Dispatcher $dispatcher */
        $dispatcher = DI::get('dispatcher');
        $dispatcher->set_controller($router->get_controller_name());
        $dispatcher->set_action($router->get_action_name());
        foreach ($router->get_params() as $paramName => $paramValue) {
            $dispatcher->set_param($paramName, $paramValue);
        }

        /** @var \Core\View $view */
        $view = DI::get('view');

        $this->register_services();

        $controller_name = $dispatcher->get_controller();
        $action_name = $dispatcher->get_action();

        $controller_file = SYSTEM_DOC_ROOT . '/controllers/' . ucfirst($controller_name) . '.php';
        if (!file_exists($controller_file)) {
            throw new \RuntimeException('Controller "' . $controller_name . '" not found');
        }
        require_once $controller_file;
        $controller_class_name = '\\Controller\\' . ucfirst($controller_name);
        $controller = new $controller_class_name();

        $view->set_template([
            'controller' => $controller_name,
            'action' => $action_name
        ]);

        try {
            $controllerClass = new $controller_class_name();
            $controllerClass->{ucfirst($action_name) . 'Action'}();
//            call_user_func([ucfirst($controller_class_name), ucfirst($action_name) . 'Action']);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            echo '<pre>';
            var_dump($e->getTrace());
            echo '</pre>';
        }

        $view->render();

        echo $view->get_contents();

    }

    protected function register_services()
    {
        /** @var \Core\View $view */
//        $view = DI::get('view');

    }
    
    public static function logDB($string) {
		array_push(self::$log, $string);
	}
	
	public static function routing($URI) {
		if (in_array($URI, ['', '/', '/index.php'])) {
			App::$module = 'dash';
		} elseif ('/income/' == $URI) {
			App::$view = 'income';
		} elseif ('/forecast/' == $URI) {
			App::$view = 'forecast';
		} elseif (preg_match('/^\/add\/([0-9]+)\/$/ui', $URI, $matches)) {
			App::$view = 'add';
			App::$request['catID'] = $matches[1];
		} elseif (preg_match('/^\/stats\/([0-9]+)\/$/ui', $URI, $matches)) {
			App::$view = 'stats';
			App::$request['catID'] = $matches[1];
		} elseif (preg_match('/^\/stats\/([0-9]+)\/([0-9]+)\/([0-9]+)\/$/ui', $URI, $matches)) {
			App::$view = 'stats';
			App::$request['catID'] = $matches[1];
			App::$request['year'] = $matches[2];
			App::$request['month'] = $matches[3];
		} elseif (preg_match('/^\/controller\/([a-z0-9-]+)\/([a-z0-9-]+)\/$/ui', $URI, $matches)) {
			if (isset($matches[1], $matches[2])) {
				App::$mode = 'controller';
				self::$controllerName = $matches[1];
				self::$controllerAction  = $matches[2];
			}
		} else {
			exit('The page you are looking for doesn\'t exist');
		}
	}
	
}

?>
