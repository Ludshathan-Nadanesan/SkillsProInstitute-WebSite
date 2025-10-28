
const navBar2 = document.querySelector(".nav-bar-2");
const hamBurgerMenu = document.querySelector(".hamburger-menu");
const mobileNavBar = document.querySelector(".mobile-nav-bar");
const highlightSectionCard = document.querySelectorAll(".highlight-section-card");
const courseCard = document.querySelectorAll(".course-card-list-section .course-card");

// Theme Selection (toggle)
const themeIcon = document.getElementById("theme-icon");
themeIcon.addEventListener("click", () => {
    let currentTheme = localStorage.getItem("theme") || "light";
    let newTheme = currentTheme === "light" ? "dark" : "light";
    setTheme(newTheme);
});

// Set Theme Function
function setTheme(theme) {
    if (theme === "dark") {
        document.body.classList.add("dark");
        navBar2.classList.add("dark");
        themeIcon.className = "fa-solid fa-sun";
        localStorage.setItem("theme", "dark");
        highlightSectionCard.forEach((element) => {
            element.classList.remove("light");
        });
        courseCard.forEach((element)=>{element.classList.remove("light")});
    } else {
        document.body.classList.remove("dark");
        navBar2.classList.remove("dark");
        themeIcon.className = "fa-solid fa-moon";
        localStorage.setItem("theme", "light");
        highlightSectionCard.forEach((element) => {
            element.classList.add("light");
        });
        courseCard.forEach((element)=>{element.classList.add("light")});
    }
};

// Hamburger Menu Functionality
hamBurgerMenu.addEventListener("click", () => {
    hamBurgerMenu.classList.toggle("active");
    mobileNavBar.classList.toggle("active");
});

// Event delegation for language buttons
document.querySelectorAll(".three-lang").forEach((element) => {
    element.addEventListener("click", (event) => {
        event.preventDefault();
        if (event.target.id === "Tamil") setLang("ta");
        if (event.target.id === "Sinhala") setLang("si");
        if (event.target.id === "English") setLang("en");
    })
});

// Set Language Function
async function setLang(lang) {
    const response = await fetch("/SkillPro/languages.json"); // This is for load JSON file
    const data = await response.json();

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

//  On page refresh or loading
window.onload = () => {
    let savedLang = localStorage.getItem("language") || "en";
    setLang(savedLang);
    let savedTheme = localStorage.getItem("theme") || "light";
    setTheme(savedTheme);
};

// Force reload on back/forward navigation
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});

// ========================================== view more course section logic =================
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("course-modal");
    const modalContent = document.querySelector(".modal-content");
    const closeBtn = document.querySelector(".close-btn");

    // Open modal when "View More" clicked
    document.querySelectorAll(".event-card").forEach(card => {
        card.addEventListener("click", (e) => {
            const detail = JSON.parse(card.dataset.detail);

            // Example: build details from dataset or card
            const detailsHtml = `
                <h2>${detail.title}</h2>
                <p><b>Date:</b> ${detail.start_date} - ${detail.end_date}</p>
                <p><b>Branch:</b> ${detail.branch}</p>
                <p><b>Description:</b> ${detail.content}</p>
            `;
            document.getElementById("event-details").innerHTML = detailsHtml;

            modal.style.display = "flex"; // show modal
        });
    });

    // Close when clicking X
    closeBtn.addEventListener("click", () => {
        modal.style.display = "none";
    });

    // Close when clicking outside modal content
    modal.addEventListener("click", (e) => {
        if (!modalContent.contains(e.target)) {
            modal.style.display = "none";
        }
    });
});



// ============================== Search bar Logic ===========================
// Function definition
function handleAutocomplete(inputEl, dropdownEl, apiUrl) {
    const query = inputEl.value.trim();
    if (query.length < 2) {
        dropdownEl.innerHTML = "";
        dropdownEl.style.display = "none";
        return;
    }

    fetch(`${apiUrl}${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            dropdownEl.innerHTML = "";
            if (data.length > 0) {
                data.forEach(item => {
                    const div = document.createElement("div");
                    div.classList.add("autocomplete-item");
                    div.textContent = item.text;
                    div.addEventListener("click", () => {
                        window.location.href = item.url;
                    });
                    dropdownEl.appendChild(div);
                });
                dropdownEl.style.display = "block";
            } else {
                dropdownEl.style.display = "none";
            }
        });
}

// Attach event listener to input
const searchInput = document.querySelector("#home-search");
const searhcDropdown = document.querySelector("#home-search-dropdown");

searchInput.addEventListener("input", () => handleAutocomplete(searchInput, searhcDropdown, "/SkillPro/Helpers/search.php?q="));

// Attach event listener to input
const mobileSearchInput = document.querySelector("#mobile-home-search");
const mobilSearchDropdown = document.querySelector("#mobile-home-search-dropdown");

mobileSearchInput.addEventListener("input", () => handleAutocomplete(mobileSearchInput, mobilSearchDropdown, "/SkillPro/Helpers/search.php?q="));


// ================= Outside click handler =================
document.addEventListener("click", (e) => {
    if (!searchInput.contains(e.target) && e.target !== searchInput) {
        searhcDropdown.style.display = "none";
    }
    if (!mobileSearchInput.contains(e.target) && e.target !== mobileSearchInput) {
        mobilSearchDropdown.style.display = "none";
    }
});