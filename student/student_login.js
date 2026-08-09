const password = document.getElementById('password');

function togglePassword() {
  if (password.type === "password") {
    password.type = "text";
  }else{
    password.type = "password";
  }
}