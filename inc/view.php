<?php

class View {
  private $locales = array();
  
  public function __construct() {
    $this->build_locales();
  }
  
  public function render($view_name) {
    echo $view_name;
  }
  
  public function add_locale($key, $value) {
    $this->locales[$key] = $value;
    
    return $this->locales;
  }
  
  public function replace_locales($html) {    
    foreach ($this->locales as $key => $value) {
      $html = str_replace("{\$" . $key . "\$}", $value, $html);
    }
    
    return $html;    
  }
  
  protected function build_locales() {
    $this->locales = array();
    
    return $this->locales;
  }
}

?>