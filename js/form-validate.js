//Form validation
$(document).ready(function(){
  //To Do
  
  $("#theForm").submit(function(evt){
    $(".loader").addClass("active");
    let errors = validateForm();
    
    if (errors.length == 0) {      
      return true;
    }
    else {
      removeErrors();      
      displayErrors(errors);
      evt.preventDefault();
      window.scrollTo(0,0);
      $(".loader").removeClass("active");
      return false;
    }
  });
    
  function validateForm() {
    let errors = [];
      
    //Check required empty fields input
    if ($("#firstName").val().length == 0) {
      errors.push("firstName");
    }
    
    if ($("#lastName").val().length == 0) {
      errors.push("lastName");
    }
    
    if ($("#email").val().length == 0) {
      errors.push("email");
    }
    
    if ($("#password").val().length == 0) {
      errors.push("password");
    }
    else if ($("#password").val().length < 6) {
      errors.push("password");
    }
    
    if ($("#verify").val().length == 0) {
      errors.push("verify");
    }
    
    //Check passwords match
    if ($("#password").val() != $("#verify").val()) {
      errors.push("verify");
    }
    
    //Validate zip code (basic)
    if ($("#zip").val()) {
      let zip = $("#zip").val();
      let patt = /[^0-9]/g;
      
      let testZip = patt.exec(zip);
      if(testZip) {
        errors.push("zip");
      }
      else if (zip.length != 5) {
        errors.push("zip");
      }
    }
    
    //Validate email
    let email = $("#email").val();
    let regExp = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    let testEmail = regExp.exec(email);
    
    if (!testEmail) {
       errors.push("email");
    }
    
    //Validate phone number
    if ($("#phone").val().length > 0) {
      let phoneNumb = $("#phone").val();
      
      //Remove any non valid character
      phoneNumb = phoneNumb.replace(/[^0-9]/g, "");
      
      if (phoneNumb.length != 10) {
        errors.push("phone");
      }
      if (!$("input[name=phonetype]:checked").val()) {
        errors.push("phonetype");
      }
    }
    
    return errors;
  }//End function
    
  function displayErrors(errors) {  
    for (let i = 0, len = errors.length; i < len; i++) {
      $("#" + errors[i] + " ~ .errorMess").addClass("active");
    }      
    $("#errorDiv").html("Errors found");
  }
    
  function removeErrors() {
    $(".errorMess.active").removeClass("active");
  }
});