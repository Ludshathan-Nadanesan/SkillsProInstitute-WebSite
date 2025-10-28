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


// show or hide password
const passwordField = document.querySelector('.login-container .login-form #form .password #userpwod');
const togglePassword = document.querySelector('.login-container .login-form #form .password #show-password');

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
    document.querySelector(".login-container .login-form #form .password").classList.add("active");
});

passwordField.addEventListener("blur", () => {
    document.querySelector(".login-container .login-form #form .password").classList.remove("active");
});

const passwordWrapper = document.querySelector(".login-container .login-form #form .password");

document.addEventListener("click", (e) => {
    if (passwordWrapper.contains(e.target)) {
        // Clicked inside wrapper (input or eye button)
        passwordWrapper.classList.add("active");
    } else {
        // Clicked outside
        passwordWrapper.classList.remove("active");
    }
});
