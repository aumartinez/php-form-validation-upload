<?php
# Locations
define ("DS", DIRECTORY_SEPARATOR);
define ("ROOT", dirname(__FILE__));
define ("INC", "inc");

require_once (INC . DS . "config.php");
require_once (INC . DS . "dbkey.php");
require_once (INC . DS . "view.php");
require_once (INC . DS . "model.php");

class Form {
  protected $model;
  protected $view;
  private $page;
  
  public function __construct() {
    session_start();
    
    $this->model = new Model();
    $this->view = new View();
    
    if (isset($_SESSION["success"])) {
      $this->page = "success";
      unset($_SESSION["success"]);
    }
    else {
      $this->page = "form";  
    }
    
    $this->view->render($this->model->build_page($this->page));    
  }
}

$form = new Form();

?>