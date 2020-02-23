<?php
# Locations
define ("DS", DIRECTORY_SEPARATOR);
define ("ROOT", dirname(__FILE__));
define ("INC", "inc");

require_once ("config.php");
require_once ("functions.php");
require_once ("dbkey.php");
require_once ("view.php");
require_once ("model.php");

class Validateform {  
  protected $required;
  protected $model;
  
  protected $sql;
  protected $rows = array();
  private $conx;
  
  protected $sanitized = array();
  
  public function __construct() {
    session_start();
    
    if (!isset($_POST["submitForm"])) {
      header("Location: ../form.php");
      exit();
    }
    
    $_SESSION["submitForm"] = true;
    
    if (isset($_SESSION["error"])) {
      unset($_SESSION["error"]);
    }
    
    $_SESSION["error"] = array();
    
    $this->model = new Model();
  } # End construct
  
  public function required() {
        
    $required = REQUIRED;
    
    # Check required
    foreach ($required as $value) {
      if (!isset($_POST[$value]) || $_POST[$value] == "") {
        $_SESSION["error"][] = $value." is required";
      }
    }
    
    $this->error_check();
  }
  
  public function names() {
    # Validate firstName and lastName
    if (!preg_match('/^[\w .]+$/', $_POST["firstName"])) {
      $_SESSION["error"][] = "First name must be letter and numbers only";
    }
    
    if (!preg_match("/^[\w .]+$/", $_POST["lastName"])) {
      $_SESSION["error"][] = "Last name must be letter and numbers only";
    }
    
    # Validate email
    if (isset($_POST["email"]) && $_POST["email"] != "") {
      if(!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"][] = "Email is invalid";
      }
    }
    
    $this->error_check();
  }
  
  public function password_match() {
    # Validate password match
    if (isset($_POST["password"]) && isset($_POST["verify"])) {
      if ($_POST["password"] != "" && $_POST["verify"] != "") {
        if ($_POST["password"] != $_POST["verify"]) {
          $_SESSION["error"][] = "Passwords don't match";
        }
        else if (strlen($_POST["password"]) < 6 || strlen($_POST["verify"]) < 6) {
          $_SESSION["error"][] = "Passwords should be more than 6 characters";
        }
      }
    }
    
    $this->error_check();
  }
  
  public function additional() {
    # Additional validations
    if (isset($_POST["state"]) && strlen($_POST["state"]) > 0) {
      if (!is_valid_state($_POST["state"])) {
        $_SESSION["error"][] = "Please choose a valid state.";
      }
    }

    if (isset($_POST["zip"]) && $_POST["zip"] != "") {
      if (!is_valid_zip($_POST["zip"])) {
        $_SESSION["error"][] = "ZIP code error.";
      }
    }

    if (isset($_POST["phone"]) && $_POST["phone"] != "") {
      if (!preg_match("/^[\d]+$/", $_POST["phone"])) {
        $_SESSION["error"][] = "Phone number should be only digits.";
      }
      else if (strlen($_POST["phone"] < 10)) {
        $_SESSION["error"][] = "Phone number must be at least 10 digits.";
      }
      
      if (!isset($_POST["phonetype"]) || $_POST["phonetype"] == "") {
        $_SESSION["error"][] = "Please choose a phone number type.";
      }
      else {
        $validPhoneTypes = array(
        "work",
        "home"
        );
        
        if (!in_array($_POST["phonetype"], $validPhoneTypes)) {
          $_SESSION["error"][] = "Please choose a valid phone number type.";
        }
      }
    }
    
    $this->error_check();
  }
  
  protected function error_check() {
    if (count($_SESSION["error"]) > 0) {
      error_log("Error");
      header("Location: ../form.php");
      exit();
    }
  }
  
  # Register user entry
  public function sanitize() {
    $this->sanitized = array();
    
    foreach ($_POST as $key => $value) {
      $this->sanitized[$key] = $this->open_link()->real_escape_string($value);
    }
        
    return $this->sanitized;    
  }
  
  public function register() {
    $sql = "nothing";
    
    if(file_exists(SQL . DS . "createtable.sql")) {
      $sql = file_get_contents(SQL . DS . "createtable.sql");
    }
    else {
      echo $sql;
    
    $email = $this->sanitized["email"];
    echo $email;
    
        
    /* unset($_SESSION["submitForm"]);
    unset($_SESSION["error"]);
    $_SESSION["success"] = true;
    header("Location: ../form.php");
    exit(); */
  }
  
  # Open DB link
  private function open_link() {
    $this->conx = new Mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    if ($this->conx->connect_errno) {
      echo "Failed to connect to MySQL: " . $this->conx-> connect_error;
      exit();
    }
    
    return $this->conx;
  }
  
  # Close DB link
  private function close_link() {
    $this->conx->close();
  }
  
  # Submit SQL query for INSERT, UPDATE or DELETE
  protected function set_query($sql) {
    $this->open_link();
    $this->conx->query($sql);
    $this->close_link();
  }
  
  protected function get_query($sql) {
    $this->open_link();
    $resul = $this->conx->query($sql);
    while ($this->rows[] = $result->fetch_assoc());
    $result->free();
    $this->close_link();
    array_pop($this->rows);
  }
  
 
  
  
    
    # Final errors check
    /* if (count($_SESSION["error"]) > 0) {
      error_log("Error");
      header("Location: ../form.php");
      exit();
    }
    else {
      if(registerUser($_POST)) {
        unset($_SESSION["submitForm"]);
        unset($_SESSION["error"]);
        $_SESSION["success"] = true;
        header("Location: ../form.php");
        exit();    
      }
      else {    
        error_log("Problem registering user: {$_POST["email"]}.", $_SESSION["error"][] = "Problem registering account.", die(header("Location: ../form.php")));
        exit();
      }
    } */
  
}

$validate = new Validateform();
$validate->required();
$validate->names();
$validate->password_match();
$validate->additional();
$validate->sanitize();
$validate->register();


function registerUser($form) {
  $conx = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
  if (!$conx) {
    $messLog = "MySQL connection failed: ".mysqli_connect_errno();
    error_log($messLog);
    return false;
  }
  
  $email = mysqli_real_escape_string($conx, $_POST["email"]);
  
  $sql = "SELECT id 
          FROM customer
          WHERE email = '{$email}'";
  
  $query = mysqli_query($conx, $sql);
  if (!$query) {
    error_log(die("SQL query error: ".mysqli_error($conx)));
    return false;
  }
  
  $result = mysqli_num_rows($query);
  if ($result == 1) {
    $_SESSION["error"][] = "A user with that e-mail address already exists.";
    mysqli_close($conx);
    mysqli_free_result($query);
    return false;
  }
  
  mysqli_free_result($query);
  
  $firstName = mysqli_real_escape_string($conx, $_POST["firstName"]);
  $lastName = mysqli_real_escape_string($conx, $_POST["lastName"]);
  
  $salt = "\$6\$rounds=5000\$".randomStr(8)."\$";
  $password = mysqli_real_escape_string($conx, $_POST["password"]);
  $crypted = substr(crypt($password, $salt),strlen($salt));
  
  $street = isset($_POST["address"]) ? mysqli_real_escape_string($conx, $_POST["address"]) : "";
  $city = isset($_POST["city"]) ? mysqli_real_escape_string($conx, $_POST["city"]) : "";
  $state = isset($_POST["state"]) ? mysqli_real_escape_string($conx, $_POST["state"]) : "";
  $zip = isset($_POST["zip"]) ? mysqli_real_escape_string($conx, $_POST["zip"]) : "";
  $phone = isset($_POST["phone"]) ? mysqli_real_escape_string($conx, $_POST["phone"]) : "";
  $phonetype = isset($_POST["phonetype"]) ? mysqli_real_escape_string($conx, $_POST["phonetype"]) : "";
  
  $sql = "INSERT INTO customer (
          email,
          create_date,
          password,
          salt,
          first_name,
          last_name,
          street,
          city,
          state,
          zip,
          phone,
          phone_type
          )
          VALUES(
          '{$email}',
          NOW(),
          '{$crypted}',
          '{$salt}',
          '{$firstName}',
          '{$lastName}',
          '{$street}',
          '{$city}',
          '{$state}',
          '{$zip}',
          '{$phone}',
          '{$phonetype}'
          )";
  
  $query = mysqli_query($conx, $sql);
  
  if ($query) {
    $id = mysqli_insert_id($conx);
    error_log("New user with email:{$email} registered as ID {$id}.");
    mysqli_close($conx);
    return true;
  }
  else {
    error_log("Couldn't add new registry, the query failed: ".mysqli_error($conx));
    mysqli_close($conx);
    return false;
  }
}//End function

?>