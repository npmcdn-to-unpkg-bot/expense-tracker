<?php

namespace Core;

class View
{
    protected $_controller = 'home';
    protected $_action = 'index';

    protected $_loader;
    protected $_environment;
    protected $_template;

    protected $_contents;

    protected $_vars = [];

    public function __construct()
    {
        $this->_loader = new \Twig_Loader_Filesystem(SYSTEM_DOC_ROOT . '/views');
        $this->_environment = new \Twig_Environment($this->_loader, []);

    }

    public function render()
    {
        $this->_template = $this->_environment->loadTemplate($this->_controller . '/' . $this->_action . '.twig');
        $this->_contents =  $this->_template->render($this->_vars);
    }

    public function get_contents()
    {
        return $this->_contents;
    }

    public function set_template(Array $params)
    {
        if (isset($params['controller'])) {
            $this->_controller = $params['controller'];
        }
        if (isset($params['action'])) {
            $this->_action = $params['action'];
        }
    }

    public function set_var($name, $content)
    {
        $this->_vars[$name] = $content;
    }

}