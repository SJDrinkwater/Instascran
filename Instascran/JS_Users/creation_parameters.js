// Function to show or hide the password
function togglePasswordVisibility(inputId) {
    var passwordField = document.getElementById(inputId);
    var icon = passwordField.nextElementSibling;

    if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}


function selectImage(img) {
    var images = document.querySelectorAll('.image-container img');
    images.forEach(function(image) {
        image.classList.remove('selected');
    });
    img.classList.add('selected');
}


// Function to check the strength of the password and display requirements
function checkPasswordStrength(password) {
    var strengthElement = document.getElementById("password_strength");
    var requirementsElement = document.getElementById("password_requirements");

    var strongRegex = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@$!%*?&])[A-Za-z\\d@$!%*?&]{8,}$");
    var mediumRegex = new RegExp("^(?=.*[a-zA-Z])(?=.*\\d)[A-Za-z\\d@$!%*?&]{6,}$");

    if (strongRegex.test(password)) {
        strengthElement.textContent = "Strong password";
        strengthElement.style.color = "green";
    } else if (mediumRegex.test(password)) {
        strengthElement.textContent = "Medium password";
        strengthElement.style.color = "orange";
    } else {
        strengthElement.textContent = "Weak password";
        strengthElement.style.color = "red";
    }

    // Display password requirements
    var length = document.getElementById("length");
    var uppercase = document.getElementById("uppercase");
    var lowercase = document.getElementById("lowercase");
    var number = document.getElementById("number");
    var symbol = document.getElementById("symbol");

    length.classList.remove("checked", "unchecked");
    uppercase.classList.remove("checked", "unchecked");
    lowercase.classList.remove("checked", "unchecked");
    number.classList.remove("checked", "unchecked");
    symbol.classList.remove("checked", "unchecked");

    if (password.length >= 8) {
        length.classList.add("checked");
    } else {
        length.classList.add("unchecked");
    }

    if (/[A-Z]/.test(password)) {
        uppercase.classList.add("checked");
    } else {
        uppercase.classList.add("unchecked");
    }

    if (/[a-z]/.test(password)) {
        lowercase.classList.add("checked");
    } else {
        lowercase.classList.add("unchecked");
    }

    if (/\d/.test(password)) {
        number.classList.add("checked");
    } else {
        number.classList.add("unchecked");
    }

    if (/[@$!%*?&]/.test(password)) {
        symbol.classList.add("checked");
    } else {
        symbol.classList.add("unchecked");
    }
}

// Check password strength and display requirements when typing in the password field
document.getElementById("password").addEventListener("input", function() {
    checkPasswordStrength(this.value);
});

// Check password strength and display requirements when typing in the confirm password field
document.getElementById("confirm_password").addEventListener("input", function() {
    var password = document.getElementById("password").value;
    if (this.value !== password) {
        document.getElementById("password_strength").textContent = "Passwords do not match";
        document.getElementById("password_strength").style.color = "red";
        document.getElementById("password_requirements").textContent = "";
    } else {
        checkPasswordStrength(password);
    }
});
// Add event listeners to form inputs to check form completion
var formInputs = document.querySelectorAll('input[type="text"], input[type="password"], input[type="radio"]');
formInputs.forEach(function(input) {
    input.addEventListener("input", checkFormCompletion);
});

document.querySelector('form').addEventListener('submit', function(event) {
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;
    var userIcon = document.querySelector('input[name="user_icon"]:checked');
    var registerButton = document.querySelector('input[type="submit"]');

    // Flag to check if form submission is allowed
    var allowSubmission = true;

    // Check if all fields are filled and password matches the confirm password
    if (!(username && password && confirmPassword && password === confirmPassword && userIcon)) {
        alert("Please fill in all fields and ensure passwords match.");
        allowSubmission = false;
    }

    // Check if password meets strength requirements
    var strengthElement = document.getElementById("password_strength");
    if (strengthElement.textContent !== "Strong password") {
        alert("Please choose a stronger password.");
        allowSubmission = false;
    }

    // Prevent form submission if any condition fails
    if (!allowSubmission) {
        event.preventDefault(); // Prevent form submission
    }
});

window.addEventListener('DOMContentLoaded', function() {
    var queryParams = new URLSearchParams(window.location.search);
    var usernameExists = queryParams.get('username_exists');
    if (usernameExists === 'true') {
        alert("Username already exists! Please choose a different username.");
    }
});
