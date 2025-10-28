//  On page refresh or loading
window.onload = () => {
    let savedLang = localStorage.getItem("language") || "en";
    setLang(savedLang);
    let savedTheme = localStorage.getItem("theme") || "light";
    setTheme(savedTheme);
}

// Set Theme Function
function setTheme(theme) {
    if (theme === "dark") {
        document.body.classList.add("dark");
    } else {
        document.body.classList.remove("dark");
    }
};

// Force reload on back/forward navigation
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});

// Event delegation for language buttons
document.querySelector(".three-lang").addEventListener("click", (event) => {
    if (event.target.id === "Tamil") setLang("ta");
    if (event.target.id === "Sinhala") setLang("si");
    if (event.target.id === "English") setLang("en");
});

// Set Language Function
async function setLang(lang) {
    const response = await fetch("/SkillPro/languages.json"); // This is for load JSON file
    const data = await response.json();

    // Remove "active" from all buttons first
    document.querySelectorAll(".three-lang a").forEach(element => {
        element.classList.remove("active");
    });

    // Add "active" only to the selected one
    if (lang==="ta") {
        document.getElementById("Tamil").classList.add("active");
    }
    else if (lang==="en") {
        document.getElementById("English").classList.add("active");
    } else if (lang==="si") {
        document.getElementById("Sinhala").classList.add("active");
    }

    // update all data-translate elements using JSON file
    document.querySelectorAll("[data-translate]").forEach((element) => {
        const key = element.getAttribute("data-translate");
        if (element.tagName === "INPUT") { // if element is input
            element.placeholder = data[lang][key];
        }
        else { // element is normal text
            element.textContent = data[lang][key]; 
        }
        // Save user choiced language
        localStorage.setItem("language", lang);
    });
};


// Province and Gender Drop down menu
const Dropdowns = document.querySelectorAll(".register-container .register-form .form-group .dropdown");
Dropdowns.forEach(dropdown => {
    const selected = dropdown.querySelector(".dropdown-selected");
    const options = dropdown.querySelector(".dropdown-options");
    const items = dropdown.querySelectorAll("li");

    // Get hidden input controlled by this dropdown
    const inputName = dropdown.getAttribute("data-input");
    const hiddenInput = document.getElementById(inputName);


    // Toggle dropdown on click
    selected.addEventListener("click", (e) => {
        e.stopPropagation(); // prevent closing immediately
        // Close other dropdowns first
        Dropdowns.forEach(d => {
            if (d !== dropdown) {
                d.querySelector(".dropdown-options").style.display = "none";
                d.querySelector(".dropdown-selected").classList.remove("active");
            }
        });
        options.style.display = options.style.display === "block" ? "none" : "block";
        selected.classList.toggle("active");
    });

    // Select an item
    items.forEach(item => {
        item.addEventListener("click", (e) => {
            e.stopPropagation();
            const value = item.getAttribute("data-value") || item.textContent;
            selected.innerHTML = `<span>${item.textContent}</span><i class='fa-solid fa-caret-down'></i>`;
            if (hiddenInput) {
                hiddenInput.value = value;
            }
            options.style.display = "none";
            selected.classList.remove("active");
        });
    });
});

// Close dropdowns if clicking outside
document.addEventListener("click", () => {
    Dropdowns.forEach(dropdown => {
        dropdown.querySelector(".dropdown-options").style.display = "none";
        dropdown.querySelector(".dropdown-selected").classList.remove("active");
    });
});




// password input div design
// show or hide password
const passwordField = document.querySelector('.register-container .register-form #form  .password #password');
const togglePassword = document.querySelector('.register-container .register-form #form  .password #show-password');

togglePassword.addEventListener("click", ()=> {
    let type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);

    // Toggle the eye icon
    if(togglePassword.classList.contains("fa-eye-slash"))
    {
        togglePassword.classList.remove("fa-eye-slash");
        togglePassword.classList.add("fa-eye");
    }
    else {
        togglePassword.classList.remove("fa-eye");
        togglePassword.classList.add("fa-eye-slash");
    }

});

passwordField.addEventListener("focus", () => {
    document.querySelector(".register-container .register-form #form  .password").classList.add("active");
});

passwordField.addEventListener("blur", () => {
    document.querySelector(".register-container .register-form #form  .password").classList.remove("active");
});

const passwordWrapper = document.querySelector(".register-container .register-form #form  .password");

document.addEventListener("click", (e) => {
    if (passwordWrapper.contains(e.target)) {
        // Clicked inside wrapper (input or eye button)
        passwordWrapper.classList.add("active");
    } else {
        // Clicked outside
        passwordWrapper.classList.remove("active");
    }
});




// Password Strength Suggestion
const passwordInput = document.getElementById("password");

passwordInput.addEventListener("input", () => {
    const value = passwordInput.value;
    let strength = "Weak";
    let color = "red";

    if (value.length >= 8 &&
        /[A-Z]/.test(value) &&
        /[a-z]/.test(value) &&
        /[0-9]/.test(value) &&
        /[\W]/.test(value)) {
        strength = "Strong";
        color = "green";
    } else if (value.length >= 6) {
        strength = "Medium";
        color = "orange";
    }

    let indicator = document.getElementById("strength-indicator");
    if (!indicator) {
        indicator = document.createElement("p");
        indicator.id = "strength-indicator";
        passwordInput.insertAdjacentElement("afterend", indicator);
    }
    indicator.textContent = `Password Strength: ${strength}`;
    indicator.style.color = color;
});




document.getElementById("form").addEventListener("submit", function (e) {
    let errors = [];

    // Full name
    const fullName = document.getElementById("full-name").value.trim();
    if (fullName === "" || !/^[A-Za-z\s]+$/.test(fullName)) {
        errors.push("Full Name is invalid.");
    }

    // DOB
    const dob = document.getElementById("dob").value;
    if (!dob) {
        errors.push("Date of Birth is required.");
    } else {
        const age = new Date().getFullYear() - new Date(dob).getFullYear();
        if (age < 18) {
            errors.push("You must be at least 18 years old.");
        }
    }

    // Gender
    const gender = document.getElementById("gender").value;
    const genderValidValues = ["Male","Female"]
    if (!gender) {
        errors.push("Please select a gender.");
    } else {
        if (!genderValidValues.includes(gender)) {
            errors.push("Please select a valid gender option.");
        }
    }

    // NIC
    const nic = document.getElementById("nic-no").value.trim();
    if (!/^\d{9}[Vv]$|^\d{12}$/.test(nic)) {
        errors.push("NIC number is invalid.");
    }

    // Street Address
    const street = document.getElementById("street-addr").value.trim();
    if (street === "") {
        errors.push("Street Address is required.");
    }

    // Province
    const province = document.getElementById("province").value;
    const validValues = ['Northern', 'Western', 'Southern', 'Central', 'North Western', 'Sabragamuwa', 'Eastern', 'Uva', 'North Central'];
    if (!province) {
        errors.push("Please select a province.");
    } else {
        if (!validValues.includes(province)) {
            errors.push("Please select a valid province option.");
        }
    }

    // Email
    const email = document.getElementById("email-address").value.trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push("Email is invalid.");
    }

    // Mobile
    const mobile = document.getElementById("mobile-no").value.trim();
    if (!/^\d{10}$/.test(mobile)) {
        errors.push("Mobile number must be 10 digits.");
    }

    // Password
    const password = document.getElementById("password").value;
    if (!/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/.test(password)) {
        errors.push("Password must be at least 8 chars, with uppercase, number & special char.");
    }

    // If any errors → prevent submit
    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join("\n"));
    }
});


// set today's date as max
document.addEventListener("DOMContentLoaded", function() {
    let today = new Date().toISOString().split("T")[0];
    document.getElementById("dob").setAttribute("max", today);
});
