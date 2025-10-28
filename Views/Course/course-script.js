
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

// Search and Filter section Drop down menu

// ============================================================== drop down logic ==============================
// custom dropdowns for form
const Dropdowns = document.querySelectorAll(".dropdown");
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
                hiddenInput.dispatchEvent(new Event('change'));
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

document.addEventListener("DOMContentLoaded", () => {
    const courseCards = document.querySelectorAll(".course-card");
    const searchInput = document.getElementById("search-text-course-name"); // make sure id exists
    const dropdownIds = ["course-category", "course-branch", "course-duration", "course-instructor"];

    function filterCourses() {
        const searchText = searchInput.value.toLowerCase();
        const category   = document.getElementById("course-category").value.toLowerCase();
        const branch     = document.getElementById("course-branch").value.toLowerCase();
        const duration   = document.getElementById("course-duration").value.toLowerCase();
        const instructor = document.getElementById("course-instructor").value.toLowerCase();

        courseCards.forEach(card => {
            const cardName    = (card.dataset.name || "").toLowerCase();
            const cardCategory= (card.dataset.category || "").toLowerCase();
            const cardBranch  = (card.dataset.branch || "").toLowerCase();
            const cardDuration= (card.dataset.duration || "").toLowerCase();
            const cardDurationType= (card.dataset.durationtype || "").toLowerCase();
            const cardInstructor  = (card.dataset.instructor || "").toLowerCase();
            const cardDurationFull = `${cardDuration} ${cardDurationType}`.trim();

            let match = true;
            if (searchText && !cardName.includes(searchText)) match = false;
            if (category && category !== "all" && !cardCategory.includes(category)) match = false;
            if (branch && branch !== "all" && !cardBranch.includes(branch)) match = false;
            if (instructor && instructor !== "all" && !cardInstructor.includes(instructor)) match = false;
            if (duration && duration !== "all" && !cardDurationFull.includes(duration)) match = false;

            card.style.display = match ? "block" : "none";
        });
    }

    // Search box event
    searchInput.addEventListener("keyup", filterCourses);

    // Dropdown change event
    dropdownIds.forEach(id => {
        const input = document.getElementById(id);
        if (input) input.addEventListener("change", filterCourses);
    });

    // Run on page load
    filterCourses();
});


// ========================================== view more course section logic =================
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("course-modal");
    const modalContent = document.querySelector(".modal-content");
    const closeBtn = document.querySelector(".close-btn");

    // Open modal when "View More" clicked
    document.querySelectorAll(".view-more").forEach(button => {
        button.addEventListener("click", (e) => {
            const card = e.target.closest(".course-card");
            const detail = JSON.parse(card.dataset.coursedetail);

            const modulesList = (detail.modules || "Not Available")
                .split(",")
                .map(m => `<li>${m.trim()}</li>`)
                .join("");

            // Example: build details from dataset or card
            const detailsHtml = `
                <h2>${detail.name}</h2>
                <p><b>Category:</b> ${detail.category}</p>
                <p><b>Duration:</b> ${detail.duration} ${detail.duration_type}</p>
                <p><b>Branches:</b> ${detail.branches}</p>
                <p><b>Instructors:</b> ${detail.instructors}</p>
                <p><b>Modules:</b></p>
                <ul>${modulesList}</ul>
                <p><b>Fee:</b> ${detail.fee}</p>
                <p><b>About:</b> ${detail.about}</p>
            `;
            document.getElementById("course-details").innerHTML = detailsHtml;

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