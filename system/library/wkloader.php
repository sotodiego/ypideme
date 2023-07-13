<?php
class Wkloader {
	/**
	 * [protected registry variable]
	 * @var [object]
	 */
	protected $registry;

  /**
   * [__construct constructor for the intialize the required]
   * @param [type] $registry [description]
   */
	public function __construct($registry) {
		$this->registry = $registry;
	}

  public function senatizeModelRoute($route) {
    return preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
  }

	public function buildPath($route) {
		return 'model_' . str_replace(array('/', '-', '.'), array('_', '', ''), $route);
	}

	public function createOcClass($route) {
		return 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $route);
	}

	public function model($route, $type = 'admin') {

    $route = $this->senatizeModelRoute($route);

    $this->registry->get('event')->trigger('model/' . $route . '/before', array(&$route));

		$path = $this->buildPath($route);

		if (!$this->registry->has($path)) {

			$file  = ($type == 'catalog') ? DIR_CATALOG . 'model/' . $route . '.php' : DIR_APPLICATION . 'model/' . $route . '.php';

			$class = $this->createOcClass($route);

			if (is_file($file)) {
				include_once($file);

				$proxy = new Proxy();

				foreach (get_class_methods($class) as $method) {
					$proxy->{$method} = $this->callback($this->registry, $route . '/' . $method,$type);
				}

				$this->registry->set('model_' . str_replace(array('/', '-', '.'), array('_', '', ''), (string)$route), $proxy);
			} else {
				throw new \Exception('Error: Could not load model ' . $route . '!');
			}
		}
		$this->registry->get('event')->trigger('model/' . $route . '/after', array(&$route));

	}
  protected function callback($registry, $route,$type) {
		return function($args) use($registry, &$route ,$type) {
			static $model = array();

			$output = null;

			// Trigger the pre events
			$result = $registry->get('event')->trigger('model/' . $route . '/before', array(&$route, &$args, &$output));

			if ($result) {
				return $result;
			}

			// Store the model object
			if (!isset($model[$route])) {
        $file  = ($type == 'catalog') ? DIR_CATALOG  . 'model/' .  substr($route, 0, strrpos($route, '/')) . '.php' : DIR_APPLICATION . 'model/' .  substr($route, 0, strrpos($route, '/')) . '.php';

				// $file = DIR_APPLICATION . 'model/' .  substr($route, 0, strrpos($route, '/')) . '.php';
				$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', substr($route, 0, strrpos($route, '/')));

				if (is_file($file)) {
					include_once($file);

					$model[$route] = new $class($registry);
				} else {
					throw new \Exception('Error: Could not load model ' . substr($route, 0, strrpos($route, '/')) . '!');
				}
			}

			$method = substr($route, strrpos($route, '/') + 1);

			$callable = array($model[$route], $method);

			if (is_callable($callable)) {
				$output = call_user_func_array($callable, $args);
			} else {
				throw new \Exception('Error: Could not call model/' . $route . '!');
			}

			// Trigger the post events
			$result = $registry->get('event')->trigger('model/' . $route . '/after', array(&$route, &$args, &$output));

			if ($result) {
				return $result;
			}

			return $output;
		};
	}

}
