<?php

class Model {  
  protected $view;
  protected $sql;
  protected $rows = array();
  protected $conx;
  
  public function __construct() {
    $this->view = new View();    
  }
  
  public function build_page($page) {
    $html_src = "";
    if (file_exists( HTML . DS . $page . ".html")) {
      $html_src .= file_get_contents(HTML . DS . $page . ".html");
      $html_src .= "\n";
    }
    
    # If errors are returned    
    $error_key = "ERROR";
    $error_mess = "";
            
    if (isset($_SESSION["error"]) && isset($_SESSION["submitForm"])) {
      unset($_SESSION["submitForm"]);
      
      $error_mess = "\n";
      $error_mess .= "Errors found!";
      $error_mess .= "<br />\n";      
        
        foreach ($_SESSION["error"] as $error) {
          $error_mess .= $error . "<br />\n";
        }
    }
    
    # If form is successful
    $success_key = "SUCCESS";
    $success_mess = "";
    
    if (isset($_SESSION["success"])) {
      $email = $_SESSION["user"];
      
      $sql = "SELECT * 
          FROM customers
          WHERE email = '{$email}'";
          
      $result = $this->get_query($sql);
      
      $success_mess = $result;
      
      unset($_SESSION["success"]);
      unset($_SESSION["user"]);
    }
    
    $this->view->add_locale($error_key, $error_mess);
    $this->view->add_locale($success_key, $success_mess);
    $html = $this->view->replace_locales($html_src);
        
    return $html;
  }
  
  # Open DB link
  protected function open_link() {
    $this->conx = new Mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    if ($this->conx->connect_errno) {
      echo "Failed to connect to MySQL: " . $this->conx-> connect_error;
      exit();
    }
    
    return $this->conx;
  }
  
  # Close DB link
  protected function close_link() {
    $this->conx->close();
  }
  
  # Submit SQL query for INSERT, UPDATE or DELETE
  protected function set_query($sql) {
    $this->open_link();
    $this->conx->query($sql);
    $this->close_link();
  }
  
  protected function set_multyquery($sql) {
    $this->open_link();
    $this->conx->multi_query($sql);
    $this->close_link();
  }
  
  # Submit SELECT SQL query
  protected function get_query($sql) {
    $this->open_link();
    $result = $this->conx->query($sql);
    while ($this->rows[] = $result->fetch_assoc());
    $result->free();
    $this->close_link();
    array_pop($this->rows);
    
    return $this->rows[0];
  } 
  
}

?>