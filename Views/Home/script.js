
const navBar2 = document.querySelector(".nav-bar-2");
const hamBurgerMenu = document.querySelector(".hamburger-menu");
const mobileNavBar = document.querySelector(".mobile-nav-bar");

const highlightSectionCard = document.querySelectorAll(".highlight-section-card");
const courseCard = document.querySelectorAll(".home-courses-section .course-card");
const instructorCard = document.querySelectorAll(".instructor-preview-section .instructor-card");
const eventCard = document.querySelectorAll(".events-section .event-card");

const eventSection = document.querySelector(".events-and-notices-section .events-section");
const noticeSection = document.querySelector(".events-and-notices-section .notices-section");

const faqItems = document.querySelectorAll(".faq-section .faq-item");


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
        instructorCard.forEach((element)=>{element.classList.remove("light")});
        eventCard.forEach((element)=>{element.classList.remove("light")});
        eventSection.classList.remove("light");
        noticeSection.classList.remove("light");
    } else {
        document.body.classList.remove("dark");
        navBar2.classList.remove("dark");
        themeIcon.className = "fa-solid fa-moon";
        localStorage.setItem("theme", "light");
        highlightSectionCard.forEach((element) => {
            element.classList.add("light");
        });
        courseCard.forEach((element)=>{element.classList.add("light")});
        instructorCard.forEach((element)=>{element.classList.add("light")});
        eventCard.forEach((element)=>{element.classList.add("light")});
        eventSection.classList.add("light");
        noticeSection.classList.add("light");
    }
};

// Toggle menu on hamburger click
hamBurgerMenu.addEventListener("click", (e) => {
    e.stopPropagation(); // prevent the document click from firing
    hamBurgerMenu.classList.toggle("active");
    mobileNavBar.classList.toggle("active");
});

// Close menu if clicked outside
document.addEventListener("click", (e) => {
    if (!hamBurgerMenu.contains(e.target) && !mobileNavBar.contains(e.target)) {
        hamBurgerMenu.classList.remove("active");
        mobileNavBar.classList.remove("active");
    }
});

// FAQ Accordion
faqItems.forEach(item => {
    const button = item.querySelector(".faq-question");
    button.addEventListener("click", () => {
        item.classList.toggle("active");

        // Close other open FAQs
        faqItems.forEach(otherItem => {
            if (otherItem !== item) {
                otherItem.classList.remove("active");
            }
        });
    });
});


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
        if (element.tagName.toLowerCase() === "INPUT" || element.tagName.toLowerCase() === "textarea") { // if element is input
            element.placeholder = data[lang][key];
        }
        else { // element is normal text
            element.textContent = data[lang][key]; 
        }
        // Save user choiced language
        localStorage.setItem("language", lang);
    });
};

// COurse Home Page SLider
const courseSliderHomePage = new Swiper(".course-list-swiper", {
    slidesPerView: 3,
    spaceBetween: 20,
    loop: true,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    breakpoints: {
        0: {
        slidesPerView: 1,
        },
        768: {
        slidesPerView: 2,
        },
        1024: {
        slidesPerView: 3,
        },
    },
});


// Events Home Page SLider(Swiper)
const eventSliderHomePage = new Swiper(".events-and-notices-section .events-section .event-list", {
    slidesPerView: 2,
    spaceBetween: 20,
    loop: true,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    breakpoints: {
        0: {
        slidesPerView: 1,
        },
        768: {
        slidesPerView: 2,
        },
        1024: {
        slidesPerView: 2,
        },
    },
});


// Student Sucess Stories SLider(Swiper)
const studentSuccessStorySlider = new Swiper(".student-success-stories-section .story-container", {
    slidesPerView: 1,
    spaceBetween: 20,
    loop: true,
    autoplay: true,
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    breakpoints: {
        0: {
        slidesPerView: 1,
        },
        768: {
        slidesPerView: 1,
        },
        1024: {
        slidesPerView: 1,
        },
    },
});

//  On page refresh or loading
window.onload = () => {
    let savedLang = localStorage.getItem("language") || "en";
    setLang(savedLang);
    let savedTheme = localStorage.getItem("theme") || "light";
    setTheme(savedTheme);
}

// Force reload on back/forward navigation
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});


// ============================== inquiry form logic and validation
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector(".inquiry-form");
    const nameInput = document.getElementById("inquiry-name");
    const emailInput = document.getElementById("inquiry-email");
    const courseInput = document.getElementById("inquiry-course");
    const messageInput = document.getElementById("inquiry_message");

    form.addEventListener("submit", function(e) {
        let errors = [];

        // Name validation
        if (nameInput.value.trim().length < 3) {
            errors.push("Full name must be at least 3 characters.");
        }

        // Email validation
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailInput.value.trim())) {
            errors.push("Enter a valid email address.");
        }

        // Course validation
        if (!courseInput.value.trim()) {
            errors.push("Please select a course.");
        }

        // Message validation
        if (messageInput.value.trim().length < 10) {
            errors.push("Message must be at least 10 characters.");
        }

        // If errors → stop submission
        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join("\n"));
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



// =================== bot settings ===================
const toggleBtn = document.getElementById("chat-bot-toggle");
const chatContainer = document.querySelector(".chat-container");
const chatMessages = document.getElementById("chatMessages");
const userInput = document.getElementById("userInput");

// Toggle open/close
toggleBtn.addEventListener("click", () => {
  chatContainer.style.display =
    chatContainer.style.display === "flex" ? "none" : "flex";
});

// Add message to chat
function addMessage(content, sender) {
  const msg = document.createElement("div");
  msg.className = `message ${sender}`;
  msg.textContent = content;
  chatMessages.appendChild(msg);
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Send message to AI
async function sendMessage() {
  const text = userInput.value.trim();
  if (!text) return;

  addMessage(text, "user");
  userInput.value = "";

  // Show typing...
  addMessage("Typing...", "bot");
  const typingMsg = chatMessages.lastChild;

  try {
    const res = await fetch("https://openrouter.ai/api/v1/chat/completions", {
      method: "POST",
      headers: {
        "Authorization": "Bearer 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",
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        "model": "nousresearch/deephermes-3-llama-3-8b-preview:free",
        "messages": [
            {
            "role": "system",
            "content": `
                        You are a friendly and professional AI chatbot for SkillPro Institute, a TVEC registered vocational training institute in Sri Lanka. Your Name is "Sam AI" from SkillPro Instititue and developped by Ludshathan Nadanesan.

                        Here is important info to use when answering:
                        - Institute branches: Colombo, Kandy, Matara
                        - Courses offered: ICT, Plumbing, Welding, Hotel Management
                        - Students can register online, select course mode (Online/On-site), and enroll in courses.
                        - Students should open an account before enrollment.
                        - Course info, fees, schedules, and notices should be referenced.
                        - Events include workshops, exams, seminars, job fairs.
                        - Education is key for empowerment and TVET programs support employability.

                        When students ask about enrollments, guide them:
                        "To enroll, first create a student account on our portal, then select the course you want, choose the mode (Online/On-site), and submit your enrollment request."

                        Always answer professionally, politely, and include guidance links if applicable:
                        📚 Courses: http://localhost/SkillPro/Views/Course/course.php"
                        👨‍🏫 Instructors: http://localhost/SkillPro/Views/InstructorHome/instructor-home.php\n"
                        📝 Enroll: http://localhost/SkillPro/Views/Course/course.php"
                        📩 Inquiry: http://localhost/SkillPro/Views/Home/index.php#inquiry" 
                        📩 faq: http://localhost/SkillPro/Views/Home/index.php#faq" 
                        📩 aboutus: http://localhost/SkillPro/Views/Home/index.php#about-us`
            },
            { "role": "user", "content": text }
        ]
      })
    });

    const data = await res.json();
    typingMsg.remove();

    // Check API response
    let reply = "Sorry, I could not get a response.";
    if (data?.choices?.length > 0) {
      reply = data.choices[0].message?.content || reply;
    }

    addMessage(reply, "bot");
  } catch (err) {
    typingMsg.remove();
    addMessage("Error: Could not connect to AI server.", "bot");
    console.error(err);
  }
}

// Enter key send
userInput.addEventListener("keypress", function(e) {
  if (e.key === "Enter") sendMessage();
});



