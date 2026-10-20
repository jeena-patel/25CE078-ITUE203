
const form = document.getElementById("registrationForm");

form.addEventListener("submit", function (event) {
    event.preventDefault();

    // Get field values
    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const mobile = document.getElementById("mobile").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword =
        document.getElementById("confirmPassword").value;
    const course = document.getElementById("course").value;
    const year = document.getElementById("year").value;
    const gender = document.querySelector(
        'input[name="gender"]:checked'
    );
    const terms = document.getElementById("terms").checked;

    let valid = true;

    // Clear previous errors
    document.querySelectorAll("small").forEach(function (error) {
        error.textContent = "";
    });

    document.getElementById("successMessage").textContent = "";

    // Name validation: letters and spaces
    const nameRegex = /^[A-Za-z ]{2,50}$/;

    if (!nameRegex.test(name)) {
        document.getElementById("nameError").textContent =
            "Enter a valid name (2-50 letters).";
        valid = false;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailRegex.test(email)) {
        document.getElementById("emailError").textContent =
            "Enter a valid email address.";
        valid = false;
    }

    // Mobile validation: exactly 10 digits
    const mobileRegex = /^[6-9]\d{9}$/;

    if (!mobileRegex.test(mobile)) {
        document.getElementById("mobileError").textContent =
            "Enter a valid 10-digit Indian mobile number.";
        valid = false;
    }

    // Password: uppercase, lowercase, digit, special character
    const passwordRegex =
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (!passwordRegex.test(password)) {
        document.getElementById("passwordError").textContent =
            "Use 8+ characters with uppercase, lowercase, number and special character (@$!%*?&).";
        valid = false;
    }

    // Confirm password
    if (confirmPassword === "" || confirmPassword !== password) {
        document.getElementById("confirmPasswordError").textContent =
            "Passwords do not match.";
        valid = false;
    }

    // Course validation
    if (course === "") {
        document.getElementById("courseError").textContent =
            "Please select a course.";
        valid = false;
    }

    // Year validation
    if (year === "") {
        document.getElementById("yearError").textContent =
            "Please select your year.";
        valid = false;
    }

    // Gender validation
    if (!gender) {
        document.getElementById("genderError").textContent =
            "Please select your gender.";
        valid = false;
    }

    // Terms validation
    if (!terms) {
        document.getElementById("termsError").textContent =
            "You must accept the terms and conditions.";
        valid = false;
    }

    // Final result
    if (valid) {
        document.getElementById("successMessage").textContent =
            "Registration form validated successfully!";
        form.reset();
    }
});

// Clear messages when the form is reset
form.addEventListener("reset", function () {
    document.querySelectorAll("small").forEach(function (error) {
        error.textContent = "";
    });
    document.getElementById("successMessage").textContent = "";
});
