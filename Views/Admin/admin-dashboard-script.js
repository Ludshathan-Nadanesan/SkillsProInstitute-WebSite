
// ================================================= ham burger menu & side bar logic ====================
// hamburger menu 
const toggleHamburgerManu = document.getElementById("menu-toggle");
// side bar
const sidebar = document.getElementById("sidebar");

// event listener for hamburger menu  
toggleHamburgerManu.addEventListener("click", () => {
    sidebar.classList.toggle("active");

    if (toggleHamburgerManu.querySelector("i").classList.contains("fa-bars"))
    {
        toggleHamburgerManu.querySelector("i").classList.remove("fa-bars");
        toggleHamburgerManu.querySelector("i").classList.add("fa-xmark");
    }
    else {
        toggleHamburgerManu.querySelector("i").classList.remove("fa-xmark");
        toggleHamburgerManu.querySelector("i").classList.add("fa-bars");
    }
});


// =============================================== = mini profile icon menu logic ========================
// profile image icon
const profileImg = document.querySelector(".header-nav .profile-mini img");
// mini profile icon settings 
const profileSettings = document.querySelector(".header-nav .profile-mini .profile-settings");

// event listener for mini profile icon setttings
profileImg.addEventListener("click", (e) => {
    e.stopPropagation(); // prevent body click from closing immediately
    profileSettings.classList.toggle("active");
});

// Hide when clicking outside of the mini profile icon settings
document.addEventListener("click", (e) => {
    if (!profileImg.contains(e.target) && !profileSettings.contains(e.target)) {
        profileSettings.classList.remove("active");
    }
});


// ================================================ theme selection logic ===============================
// theme selection icon
const themeIcon = document.getElementById("theme-icon");
//  event listener for theme selection icon
themeIcon.addEventListener("click", () => {
    let currentTheme = localStorage.getItem("theme") || "light";
    let newTheme = currentTheme === "light" ? "dark" : "light";
    setTheme(newTheme);
});

// fucntion for set theme
function setTheme(theme) {
    if (theme === "dark") {
        document.body.classList.add("dark");
        themeIcon.className = "fa-solid fa-sun";
        localStorage.setItem("theme", "dark");
    } else {
        document.body.classList.remove("dark");
        themeIcon.className = "fa-solid fa-moon";
        localStorage.setItem("theme", "light");
    }
};


// ================================================ On page refresh or loading =========================
window.onload = () => {
    let savedTheme = localStorage.getItem("theme") || "light";
    setTheme(savedTheme);
}

// ================================================ Force reload on back/forward navigation ===========
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});


// ================================================ side bar buttons & side bar btn releted section ===
// Sidebar Buttons
const sideBarBtns = document.querySelectorAll(".sidebar button[data-target]");
// sub menu buttons of the side bar buttons 
const subMenuToggles = document.querySelectorAll(".submenu-toggle");
// sections in main 
const divs = document.querySelectorAll(".main > div");


// if exists, show last section; else dashboard by default
const lastSection = sessionStorage.getItem("activeSection");

if (lastSection && document.querySelector(`.main .${lastSection}`)) {
    document.querySelector(`.main .${lastSection}`).classList.add("active");
    document.querySelector(`.sidebar button[data-target="${lastSection}"]`).classList.add("active");
} else {
    // default → show dashboard
    document.querySelector(".main .dashboard").classList.add("active");
    document.querySelector('.sidebar button[data-target="dashboard"]').classList.add("active");
}


// Handle submenu toggle
subMenuToggles.forEach(toggle => {
    toggle.addEventListener("click", () => {
        const parentLi = toggle.closest(".has-submenu");
        const submenu = parentLi.querySelector(".sub");
        submenu.classList.toggle("active");
    });
});

// Handle content switching
sideBarBtns.forEach(btn => {
    btn.addEventListener("click", () => {
        // hide all sections
        divs.forEach(div => div.classList.remove("active"));

        // remove active from all buttons
        sideBarBtns.forEach(b => b.classList.remove("active"));

        // get target section
        const target = btn.getAttribute("data-target");
        document.querySelector(`.main .${target}`).classList.add("active");

        // Save last active section in sessionStorage
        sessionStorage.setItem("activeSection", target);

        // highlight clicked button
        btn.classList.add("active");
    });
});


// ======================================================= provile view button from mini profile ======
// view profile button from the mini profile icon settongs
const viewProfileBtn=document.querySelector(".header-nav .profile-mini button");
viewProfileBtn.addEventListener("click", ()=>{
    divs.forEach((div)=>{div.classList.remove("active");})
    sideBarBtns.forEach(b => b.classList.remove("active"));
    sideBarBtns.forEach((btn)=>{
        if(btn.getAttribute("data-target") === "profile")
        {
            btn.classList.add("active");
            document.querySelector(".main .profile").classList.add("active");
        }
    })
    viewProfileBtn.parentElement.classList.remove("active");
});



// ============================================================== change password form logic ==============================
//  Password Strength Suggestion 
const oldPassword = document.querySelector(".profile #old-password");
const passwordInput = document.querySelector(".profile #new-password");
const confirmPasswordInput = document.querySelector(".profile #confirm-password");
const strengthIndicator = document.querySelector(".profile #strength-indicator");

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
    
    strengthIndicator.textContent = `Password Strength: ${strength}`;
    strengthIndicator.style.color = color;
}); 


// change password form
const changePasswordform = document.getElementById("change-password-form");

changePasswordform.addEventListener("submit", (e)=> {
    // Basic validations
    if (oldPassword.value.length === 0 || passwordInput.value.length === 0 || confirmPasswordInput.value.length === 0) {
        hasError = true;
        alert("All fields are required.");
        e.preventDefault();
        return;
    } 

    if (passwordInput.value !== confirmPasswordInput.value) {
        hasError = true;
        alert("New password and confirm password do not match!");
        e.preventDefault();
        return;
    }
    
    // Password pattern check (same as registration)
    const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;
    if (!passwordPattern.test(passwordInput.value.trim())) {
        hasError = true;
        alert("Password must be at least 8 characters, include uppercase, number, and special character.");
        e.preventDefault();
        return;
    }
});

// Profile Show Password
const profileShowPassword = document.querySelectorAll(".profile .profile-item .toggle-password");

profileShowPassword.forEach((ele,index)=>{
    ele.addEventListener("click", ()=>{
        if (index === 0)
        {
            let type = oldPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            oldPassword.setAttribute('type', type);
        } else if (index === 1) {
            let type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        } else if (index === 2) {
            let type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
        }

        // Toggle the eye icon
        if(ele.classList.contains("fa-eye-slash"))
        {
            ele.classList.remove("fa-eye-slash");
            ele.classList.add("fa-eye");
        }
        else {
            ele.classList.remove("fa-eye");
            ele.classList.add("fa-eye-slash");
        }
    });
});


// ============================================================== drop down logic ==============================
// custom dropdowns for form
const Dropdowns = document.querySelectorAll(".main .dropdown");
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


// ============================================== Add Course Section ========================================
// add new category text
const addNewCtgryTxt = document.querySelector(".main .add-course #add-course-form #add-course-category");
const addNewCtgryInput = document.querySelector(".main .add-course #add-course-form #new-course-category");

addNewCtgryInput.style.display = "none";

addNewCtgryTxt.addEventListener("click", ()=> {
    if (addNewCtgryInput.style.display === "none") {
        addNewCtgryInput.style.display = "block";
    } else {
        addNewCtgryInput.style.display = "none";
    }
})

// validate inputs 
// add new course form 
const addCourseForm = document.getElementById("add-course-form");
// event listener for add new course form
addCourseForm.addEventListener("submit", function(e) {
    // e.preventDefault();

    // erors
    let errors = [];

    // Course name
    let name = addCourseForm.querySelector("#name").value.trim();
    if (name === "" || name.length < 3) {
        errors.push("Course name must be at least 3 characters.");
    }

    // Category
    let category = addCourseForm.querySelector("#course-category").value.trim();
    let newCategory = addNewCtgryInput.value.trim();
    if (category === "" && newCategory === "") {
        errors.push("Please select a category or add a new one.");
    }

    // Duration
    let duration = addCourseForm.querySelector("#duration").value.trim();
    let durationType = addCourseForm.querySelector("#duration-type").value.trim();
    if (duration === "" || isNaN(duration) || duration <= 0) {
        errors.push("Duration must be a positive number.");
    }
    if (durationType === "" || !durationType === "Month" || !durationType === "Year") {
        errors.push("Please select duration type (Year/Month).");
    }

    // About
    let about = addCourseForm.querySelector("#about-course").value.trim();
    if (about === "" || about.length < 10)  {
        errors.push("About section must be at least 10 characters.");
    }

    // Branches
    let branchCheckboxes = addCourseForm.querySelectorAll("input[name='branches[]']");
    let checked = false;

    branchCheckboxes.forEach(cb => {
        if (cb.checked) {
            checked = true;
        }
    });

    if (!checked) {
        errors.push("Please select at least one branch.");
    }

    // final image check
    if (courseImgInput.files.length === 0) {
        errors.push("Please upload a course image.");
    }

    // Fee validation
    let fee = addCourseForm.querySelector(".main .add-course #add-course-form #course-fee").value.trim();

    if (fee === "" || isNaN(fee) || Number(fee) < 0) {
        errors.push("Please enter a valid fee.");
    } else {
        // check max 2 decimal places
        if (!/^\d+(\.\d{1,2})?$/.test(fee)) {
            errors.push("Fee can have maximum 2 decimal places.");
        }
    }

    // If errors, stop submit
    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join("\n"));
    }
});

// image preview + validation (outside submit)
const courseImgInput = document.querySelector(".main .add-course #add-course-form #course-img");
const previewImg = document.querySelector(".main .add-course #add-course-form #course-img-preview");

courseImgInput.addEventListener("change", function() {
    const file = this.files[0];
    if (file) {
        const allowedTypes = ['image/jpeg', 'image/png']; // jpeg + png enough
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!allowedTypes.includes(file.type)) {
            alert("Please upload a valid image file (jpg, jpeg, png).");
            this.value = "";
            previewImg.src = "";
            previewImg.style.display = "none";
            return;
        }

        if (file.size > maxSize) {
            alert("Image size must be less than 2MB.");
            this.value = "";
            previewImg.src = "";
            previewImg.style.display = "none";
            return;
        }

        // ✅ preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = "block";
        };
        reader.readAsDataURL(file);
    } else {
        previewImg.src = "";
        previewImg.style.display = "none";
    }
});


//  ================================================= manage course section =================
//  filter logic
document.addEventListener("DOMContentLoaded", () => {
    const courseSearchInput = document.getElementById("search-course");
    const courseFilterCategory = document.getElementById("filter-category");
    const courseFilterBranch = document.getElementById("filter-branch");
    const courseTable = document.getElementById("courses-table");
    if (!courseTable) return;
    const courseTablerows = courseTable.querySelectorAll("tbody tr");

    function filterCourseTable() {
        const searchText = courseSearchInput.value.toLowerCase();
        const categoryText = courseFilterCategory.value.toLowerCase();
        const branchText = courseFilterBranch.value.toLowerCase();

        courseTablerows.forEach(row => {
            const courseName = row.cells[0].innerText.toLowerCase();
            const courseCat = row.cells[1].innerText.toLowerCase();
            const courseBr = row.cells[5].innerText.toLowerCase();

            let match = true;

            if (searchText && !courseName.includes(searchText)) match = false;
            if (categoryText && categoryText !== "all" && courseCat !== categoryText) match = false;
            if (branchText && branchText !== "all" && !courseBr.includes(branchText)) match = false;

            row.style.display = match ? "" : "none";
        });
    }

    courseSearchInput.addEventListener("keyup", filterCourseTable);

    document.querySelectorAll(".dropdown").forEach(dropdown => {
        // const hiddenInput = document.getElementById(dropdown.dataset.input);
        dropdown.querySelectorAll(".dropdown-options li").forEach(option => {
            option.addEventListener("click", () => {
                // hiddenInput.value = option.dataset.value;
                filterCourseTable();
            });
        });
    });
});


// ======================================================== Edit course section ===================

// add new category text
const addNewCtgryTxtFromEditCourse = document.querySelector(".main #edit-course-div #add-category");
const addNewCtgryInputFromEditCourse = document.querySelector(".main #edit-course-div #edit-new-category");

addNewCtgryInputFromEditCourse.style.display = "none";

addNewCtgryTxtFromEditCourse.addEventListener("click", ()=> {
    if (addNewCtgryInputFromEditCourse.style.display === "none") {
        addNewCtgryInputFromEditCourse.style.display = "block";
    } else {
        addNewCtgryInputFromEditCourse.style.display = "none";
    }
})

document.addEventListener("DOMContentLoaded", () => {
    const courseEditButtons = document.querySelectorAll(".main .manage-courses  .table-container .edit-course-btn");
    const courseEditPopup = document.getElementById("edit-course-div");
    const courseEditcloseBtn = courseEditPopup.querySelector(".main #edit-course-div .close-btn");
    const courseEditForm = document.getElementById("edit-course-form");

    courseEditButtons.forEach((btn, index) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();

            // find the row for this button
            const row = btn.closest("tr");
            
            // read values from the table row
            const id = row.getAttribute("data-courseId");
            const name = row.cells[0].innerText;
            const category = row.cells[1].innerText;
            const duration = row.cells[2].innerText;
            const durationType = row.cells[3].innerText;
            const fee = row.cells[4].innerText.replace(/,/g, ""); // remove commas
            const branches = row.cells[5].innerText.split(",").map(b => b.trim());
                                    // assuming stored as CSV
                                    
            const about = row.cells[6].innerText;
            const image = row.cells[7];

            // populate form inputs
            // course id input hidden
            courseEditForm.querySelector("#edit-course-id").value = id;
            // course name
            courseEditForm.querySelector("#edit-name").value = name;
            // course duration
            courseEditForm.querySelector("#edit-duration").value = duration;
            // course duration type
            courseEditForm.querySelector(".dropdown-selected #type").textContent = durationType;
            courseEditForm.querySelector("#edit-duration-type").value = durationType;
            // course fee
            courseEditForm.querySelector("#edit-fee").value = fee;
            // course about
            courseEditForm.querySelector("#edit-about").value = about;
            // category
            courseEditForm.querySelector(".dropdown-selected #category").textContent = category;
            courseEditForm.querySelector("#edit-course-category").value = category;
            // branches checkboxes
            courseEditForm.querySelectorAll("input[name='edit_branches[]']").forEach((cb) => {
                cb.checked = branches.includes(cb.value);
            });
            // course image
            courseEditForm.querySelector("#course-img-preview").src = image.querySelector("a").getAttribute("href");

            // Show popup
            courseEditPopup.style.display = "block";
        });
    });

    // Close popup
    courseEditcloseBtn.addEventListener("click", () => {
        courseEditPopup.style.display = "none";
    });

    // Close when clicking outside
    window.addEventListener("click", (e) => {
        if (e.target === !courseEditPopup) {
            courseEditPopup.style.display = "flex";
        }
    });

    
    // image preview + validation (outside submit)
    const courseImgInput = courseEditForm.querySelector("#edit-course-img");
    const previewImg = courseEditForm.querySelector("#course-img-preview");

    courseImgInput.addEventListener("change", function() {
        const file = this.files[0];
        if (file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg']; // jpeg + png enough
            const maxSize = 2 * 1024 * 1024; // 2MB

            if (!allowedTypes.includes(file.type)) {
                alert("Please upload a valid image file (jpg, jpeg, png).");
                this.value = "";
                previewImg.src = "";
                previewImg.style.display = "none";
                return;
            }

            if (file.size > maxSize) {
                alert("Image size must be less than 2MB.");
                this.value = "";
                previewImg.src = "";
                previewImg.style.display = "none";
                return;
            }

            // ✅ preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.style.display = "block";
            };
            reader.readAsDataURL(file);
        }
    });
});


// ===================================================== Add Instructor Section ==============
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector(".main .add-instructor form");
    const passwordInput = form.querySelector("#instructor-password");
    const passwordIndicator = form.querySelector("#instructor-password-indicator");
    const togglePassword = form.querySelector("#toggle-password");
    const emailInput = form.querySelector("#instructor-email");
    const nameInput = form.querySelector("#instructor-name");
    const mobileInput = form.querySelector("#instructor_mobile_number");
    const bioInput = form.querySelector("#instructor-bio");
    const specializationInput = form.querySelector("#instructor-specialization");
    const addressInput = form.querySelector("#instructor-address");
    const branchInput = form.querySelector("#instructor-branch");
    const imageInput = form.querySelector("#instructor-image_path");
    const gender = form.querySelector("#instructor-gender");

    const namePattern = /^[a-zA-Z\s]{3,50}$/;
    const mobilePattern = /^[0-9]{10}$/;
    const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;

    // Password strength check
    passwordInput.addEventListener("input", function () {
        const password = passwordInput.value;

        if (passwordPattern.test(password)) {
            passwordIndicator.textContent = "Strong password ✅";
            passwordIndicator.style.color = "green";
        } else {
            passwordIndicator.textContent = "Weak password ❌ (Need 8+ chars, 1 uppercase, 1 number, 1 special)";
            passwordIndicator.style.color = "red";
        }
    });

    // Show / Hide password
    togglePassword.addEventListener("click", function () {
        passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        if (togglePassword.classList.contains("fa-eye")) {
            togglePassword.classList.remove("fa-eye");
            togglePassword.classList.add("fa-eye-slash");
        } else {
            togglePassword.classList.remove("fa-eye-slash");
            togglePassword.classList.add("fa-eye");   
        }

    });

    // Form submit validation
    form.addEventListener("submit", function (e) {
        let errors = [];

        if (!namePattern.test(nameInput.value.trim())) {
            errors.push("Name must be 3-50 letters only");
        }

        if (!(gender.value == "Male" || gender.value == "Female")) {
            errors.push("Select a valid gender");
        }

        if (!mobilePattern.test(mobileInput.value.trim())) {
            errors.push("Mobile number must be exactly 10 digits");
        }

        if (bioInput.value.trim().length < 10) {
            errors.push("Bio must be at least 10 characters");
        }

        if (!specializationInput.value.trim()) {
            errors.push("Specialization is required");
        }

        if (!addressInput.value.trim()) {
            errors.push("Address is required");
        }

        if (!branchInput.value.trim()) {
            errors.push("Branch selection is required");
        }

        if (!emailInput.value.includes("@")) {
            errors.push("Invalid email address");
        }

        if (!passwordPattern.test(passwordInput.value)) {
            errors.push("Password is too weak");
        }

        if (imageInput.files.length === 0) {
            errors.push("Profile picture is required");
        }

        if (errors.length > 0) {
            e.preventDefault();
            alert("Please fix these errors:\n- " + errors.join("\n- "));
        }
    });
});



//  ================================================= manage course section =================
//  filter logic
document.addEventListener("DOMContentLoaded", () => {
    const instructorSearchInput = document.getElementById("search-instructors");
    const instructorFilterBranch = document.getElementById("filter-instructor-branch");
    const instructorTable = document.getElementById("instrutors-table");
    if (!instructorTable) return;
    const instructorTablerows = instructorTable.querySelectorAll("tbody tr");

    function filterInstructorTable() {
        const searchText = instructorSearchInput.value.toLowerCase();
        const branchText = instructorFilterBranch.value.toLowerCase();

        instructorTablerows.forEach(row => {
            const instructorName = row.cells[0].innerText.toLowerCase();
            const instructorBr = row.cells[6].innerText.toLowerCase();

            let match = true;

            if (searchText && !instructorName.includes(searchText)) match = false;
            if (branchText && branchText !== "all" && !instructorBr.includes(branchText)) match = false;

            row.style.display = match ? "" : "none";
        });
    }

    instructorSearchInput.addEventListener("keyup", filterInstructorTable);

    document.querySelectorAll(".dropdown").forEach(dropdown => {
        // const hiddenInput = document.getElementById(dropdown.dataset.input);
        dropdown.querySelectorAll(".dropdown-options li").forEach(option => {
            option.addEventListener("click", () => {
                // hiddenInput.value = option.dataset.value;
                filterInstructorTable();
            });
        });
    });
});


// ======================================================== Edit Instructor section ===================
document.addEventListener("DOMContentLoaded", () => {
    const instructorEditButtons = document.querySelectorAll(".main .manage-instructor .table-container .edit-instructor-btn");
    const instructorEditPopup = document.querySelector(".edit-instructor-div");
    const instructorEditcloseBtn = instructorEditPopup.querySelector(".close-btn");
    const instructorEditForm = document.getElementById("edit-instructor-form");
    const changeInstructorProfilePicture = instructorEditForm.querySelector("#change-instructor-image");

    changeInstructorProfilePicture.addEventListener("click", ()=>{
        if(instructorEditForm.querySelector("#edit-instructor-image").style.visibility == "hidden") {
            instructorEditForm.querySelector("#edit-instructor-image").style.visibility = "visible";
        } else {
            instructorEditForm.querySelector("#edit-instructor-image").style.visibility = "hidden";
        }
    });

    instructorEditButtons.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();

            // find the row for this button
            const row = btn.closest("tr");
            
            // read values from the table row
            const id = row.getAttribute("data-instructorId");
            const insUsrId = row.getAttribute("data-instructorUserId");
            const name = row.cells[0].innerText;
            const email = row.cells[1].innerText;
            const mobilenumber = row.cells[2].innerText;
            const bio = row.cells[3].innerText;
            const specialization = row.cells[4].innerText;
            const address = row.cells[5].innerText;                                    
            const branch = row.cells[6].innerText;
            const gender = row.cells[7].innerText;
            const image = row.cells[8];

            // populate form inputs
            // instructor user id input hidden
            instructorEditForm.querySelector("#edit-instructor-user-id").value = insUsrId;
            // instructor id input hidden
            instructorEditForm.querySelector("#edit-instructor-table-id").value = id;
            // instructor name
            instructorEditForm.querySelector("#edit-instructor-name").value = name;
            // instructor email
            instructorEditForm.querySelector("#edit-instructor-email").value = email;
            // instructor mobile
            instructorEditForm.querySelector("#edit-instructor-mobile").value = mobilenumber;
            // instructor bio
            instructorEditForm.querySelector("#edit-instructor-bio").value = bio;
            // instructor specialization
            instructorEditForm.querySelector("#edit-instructor-specialization").value = specialization;
            // instructor specialization
            instructorEditForm.querySelector("#edit-instructor-address").value = address;
            // instructor gender
            instructorEditForm.querySelector("#edit-instructor-gender-span").textContent = gender;
            instructorEditForm.querySelector("#edit-instructor-gender").value = gender;
            // instructor branch
            instructorEditForm.querySelector("#edit-instructor-branch-span").textContent = branch;
            instructorEditForm.querySelector("#edit-instructor-branch").value = branch;
            
            // instructor gender
            instructorEditForm.querySelector("#edit-instructor-gender").value = gender;

            // Show popup
            instructorEditPopup.style.display = "block";
        });
    });

    // Close popup
    instructorEditcloseBtn.addEventListener("click", () => {
        instructorEditPopup.style.display = "none";
    });

    // Close when clicking outside
    window.addEventListener("click", (e) => {
        if (e.target === !instructorEditPopup) {
            instructorEditPopup.style.display = "flex";
        }
    });

    // image validation
    const courseImgInput = instructorEditForm.querySelector("#edit-instructor-image");

    courseImgInput.addEventListener("change", function() {
        const file = this.files[0];
        if (file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg']; // jpeg + png enough
            const maxSize = 2 * 1024 * 1024; // 2MB

            if (!allowedTypes.includes(file.type)) {
                alert("Please upload a valid image file (jpg, jpeg, png).");
                this.value = "";
                return;
            }

            if (file.size > maxSize) {
                alert("Image size must be less than 2MB.");
                this.value = "";
                return;
            }
        }
    });
});

// ===================================================== Add Modules Section ===========================
const addCModules= document.getElementById("add-course-modules");
addCModules.addEventListener("submit", function(e) {
    let isValid = true;
    let errors = [];

    // Course selection
    const courseId = addCModules.querySelector("#module-course-id").value.trim();
    if (!courseId) {
        isValid = false;
        errors.push("Please select a course.");
    }

    // Module Name
    const moduleName = addCModules.querySelector("#add-course-module-name").value.trim();
    if (moduleName.length < 3) {
        isValid = false;
        errors.push("Module name must be at least 3 characters.");
    }

    // Duration (must be number)
    const duration = addCModules.querySelector("#add-course-module-duration").value.trim();
    if (!/^[0-9]+$/.test(duration) || parseInt(duration) <= 0) {
        isValid = false;
        errors.push("Total Session must be a positive number.");
    }

    // Materials (only PDF, DOCX, PPT, ZIP)
    const fileInput = addCModules.querySelector("#add-course-materials");
    if (fileInput.files.length === 0) {
        isValid = false;
        errors.push("Please upload module material.");
    } else {
        const allowedTypes = [
            "application/pdf",
            "application/zip",
            "application/x-zip-compressed"
        ];
        if (!allowedTypes.includes(fileInput.files[0].type)) {
            isValid = false;
            errors.push("Invalid file type. Allowed: PDF, ZIP.");
        }
        if (fileInput.files[0].size > 5 * 1024 * 1024) { // 5MB limit
            isValid = false;
            errors.push("File size must be less than 5MB.");
        }
    }

    // Show errors
    if (!isValid) {
        e.preventDefault();
        alert(errors.join("\n"));
    }
});


// ===================== Assign Instructor to Course Modules =====================
const assignInstructorModuleForm = document.querySelector("#assign-instructor-to-module-form"); // form

const assignModuleCourseId = assignInstructorModuleForm.querySelector("#assign-module-course-id"); // hidden input for course selection
assignModuleCourseId.addEventListener("change", () => {    
    fetchBranches(assignModuleCourseId.value);
    fetchModules(assignModuleCourseId.value);
});

const assignModuleCourseBranch = assignInstructorModuleForm.querySelector("#assign-course-module-branch"); // hidden input for branch
assignModuleCourseBranch.addEventListener("change", () => {    
    fetchActiveBatches(assignModuleCourseId.value, assignModuleCourseBranch.value);
    fetchInstructors(assignModuleCourseBranch.value);
});


// Populate Dropdowns for Assign Module Form 
function populateDropdown(dropdown, data, defaultText = "Select") {
    const options = dropdown.querySelector(".dropdown-options");
    const selected = dropdown.querySelector(".dropdown-selected");
    const inputName = dropdown.getAttribute("data-input");
    const hiddenInput = document.getElementById(inputName);

    // Clear existing options
    options.innerHTML = "";

    if (!data || data.length === 0) {
        options.innerHTML = `<li>No options</li>`;
        selected.querySelector("span").textContent = defaultText;
        if (hiddenInput) hiddenInput.value = "";
        return;
    }

    data.forEach(item => {
        const li = document.createElement("li");

        // Detect key names based on form dropdown type
        let value, text;

        if ('id' in item && 'name' in item && 'start_date' in item && 'end_date' in item) { // Batch dropdown
            value = item.id;
            text  = item.name + '(' + item.start_date + ' - ' + item.end_date + ')';
        } else if ('id' in item && 'full_name' in item) { // Instructor dropdown
            value = item.id;
            text  = item.full_name;
        } else if ('id' in item && 'name' in item) {  // Module dropdown
            value = item.id;
            text  = item.name;
        } else if ('branch' in item) {       // Branch dropdown
            value = item.branch;
            text  = item.branch;
        }

        li.dataset.value = value;
        li.textContent = text;

        // Add click listener to li (works with your current dropdown JS)
        li.addEventListener("click", (e) => {
            e.stopPropagation();
            selected.innerHTML = `<span>${text}</span><i class='fa-solid fa-caret-down'></i>`;
            if (hiddenInput) {
                hiddenInput.value = value;
                hiddenInput.dispatchEvent(new Event('change'));
            }
            options.style.display = "none";
            selected.classList.remove("active");
        });

        options.appendChild(li);
    });

    // Reset dropdown selected text
    selected.querySelector("span").textContent = defaultText;
    if (hiddenInput) hiddenInput.value = "";
}

// AJAX fetch functions with proper action parameter
function fetchBranches(courseId) {
    fetch(`ajaxCourseController.php?action=getBranchesByCourse&courseId=${courseId}`)
        // .then(res => res.json())
        .then(res => res.text()) // <- change to text first
        .then(text => {
            // console.log("RAW BRANCH RESPONSE:", text); // 👀 check what PHP really sends
            return JSON.parse(text); // try convert manually
        })
        .then(data => populateDropdown(assignInstructorModuleForm.querySelector("[data-input='assign-course-module-branch']"), data, "Select Branch"));
}

function fetchModules(courseId) {
    fetch(`ajaxCourseController.php?action=getModulesByCourse&courseId=${courseId}`)
        .then(res => res.text()) // read as text
        .then(text => {
            // console.log("RAW MODULES RESPONSE:", text); // 👀 see what you really got
            if (!text) return []; // empty response → return empty array
            return JSON.parse(text);
        })
        .then(data => populateDropdown(assignInstructorModuleForm.querySelector("[data-input='assign-module-course-moduleId']"), data, "Select Course Module"));
}

function fetchActiveBatches(courseId, branch) {
    fetch(`ajaxCourseController.php?action=getActiveBatchesByCourseAndBranch&courseId=${courseId}&branch=${branch}`)
        .then(res => res.text()) // read as text
        .then(text => {
            // console.log("RAW BATCHES RESPONSE:", text); // 👀 see what you really got
            if (!text) return []; // empty response → return empty array
            return JSON.parse(text);
        })
        .then(data => populateDropdown(assignInstructorModuleForm.querySelector("[data-input='assign-module-course-batchId']"), data, "Select Batch"));
}

function fetchInstructors(branch) {
    fetch(`ajaxCourseController.php?action=getInstructorsByBranch&branch=${branch}`)
        .then(res => res.text()) // read as text
        .then(text => {
            // console.log("RAW INSTRUCTORS RESPONSE:", text); // 👀 see what you really got
            if (!text) return []; // empty response → return empty array
            return JSON.parse(text);
        })
        .then(data => populateDropdown(
            assignInstructorModuleForm.querySelector("[data-input='assign-module-course-instructorId']"),
            data,
            "Select Instructor"
        ));
}


//  Form Validation
assignInstructorModuleForm.addEventListener("submit", function (e) {
    const requiredFields = [
        "assign-module-course-id",
        "assign-course-module-branch",
        "assign-module-course-moduleId",
        "assign-module-course-batchId",
        "assign-module-course-instructorId"
    ];

    let isValid = true;
    let messages = [];

    requiredFields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (!input || input.value.trim() === "") {
            isValid = false;
            messages.push(`${fieldId.replace(/-/g, " ")} is required`);
        }
    });

    if (!isValid) {
        e.preventDefault(); // stop form submit
        alert("Please fill all required fields:\n\n" + messages.join("\n"));
    }
});

// ======================================== Manage Module Section ====================
document.addEventListener("DOMContentLoaded", ()=> {
    const manageModuleSearchInput = document.querySelector(".main .manage-modules .filters #manage-modules-search-input");// imput
    const manageModulefilterCourse = document.querySelector(".main .manage-modules .filters #manage-search-module-course"); // course
    const manageModuleFilterBranch = document.querySelector(".main .manage-modules .filters #manage-search-course-module-branch"); // branch

    const manageModuleTable = document.querySelector(".main .manage-modules .table-container #course-module-table"); // table
    const manageModuleTableRows = manageModuleTable.querySelectorAll("tbody tr"); // all rows from the table

    // search input filter
    manageModuleSearchInput.addEventListener("keyup", filterManageModuleTable);

    // dropdown filters
    document.querySelectorAll(".main .manage-modules .filters .dropdown").forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.dataset.input);

        // dropdown filters 
        dropdown.querySelectorAll(".dropdown-options li").forEach(option => {
            option.addEventListener("click", () => {
                // update hidden input
                hiddenInput.value = option.dataset.value;

                // update visible text
                dropdown.querySelector(".dropdown-selected span").textContent = option.textContent;

                // highlight selected option
                dropdown.querySelectorAll("li").forEach(li => li.classList.remove("active"));
                option.classList.add("active");

                // run filter
                filterManageModuleTable();
            });
        });
    });

    // function for filter
    function filterManageModuleTable() {
        const searchText = manageModuleSearchInput.value.toLowerCase();
        const courseText = manageModulefilterCourse.value.toLowerCase();
        const branchText = manageModuleFilterBranch.value.toLowerCase();

        let anyMatch = false;

        manageModuleTableRows.forEach(row => {
            const moduleName = row.cells[0].innerText.toLowerCase();
            const courseName = row.cells[1].innerText.toLowerCase();
            const branch = row.cells[2].innerText.toLowerCase();
            const instructorName = row.cells[6].innerText.toLowerCase();

            let match = true;

            if (searchText && !(
                moduleName.includes(searchText) || 
                courseName.includes(searchText) ||
                instructorName.includes(searchText))) match = false;

            if (courseText && courseText !== "all" && !courseName.includes(courseText)) match = false;

            if (branchText && branchText !== "all" && !branch.includes(branchText)) match = false;

            if (match) {
                row.style.display = "";
                anyMatch = true;
            } else {
                row.style.display = "none";
            }
        });

        // show "No Data Found" row if nothing matches
        let noDataRow = document.querySelector("#no-data-row");
        if (!anyMatch) {
            if (!noDataRow) {
                noDataRow = document.createElement("tr");
                noDataRow.id = "no-data-row";
                noDataRow.innerHTML = `<td colspan="8" style="text-align: center;">No Data Found</td>`;
                manageModuleTable.querySelector("tbody").appendChild(noDataRow);
            }
        } else {
            if (noDataRow) noDataRow.remove();
        }
    }
});



//  ======================================= Create Batches Section =====================

const createBatchForm = document.querySelector(".main .create-batches #create-batch-form");
const createBatchCourseId = createBatchForm.querySelector("#create-batches-course-id");
const createStartDateInput = createBatchForm.querySelector("#create-batch-start-date");
const createEndDateInput = createBatchForm.querySelector("#add-batch-end-date");

let createBatchCourseDuration = 0;
let createBatchCourseDurationType = "";

createBatchCourseId.addEventListener("change", ()=> {
    createStartDateInput.value = "";
    createEndDateInput.value = "";
    createBatchCourseDuration = 0;
    createBatchCourseDurationType = "";
    fetchBranchesForBatch(createBatchCourseId.value);
    fetchCourseDuration(createBatchCourseId.value);

});

//  Calculate duration for end date
createStartDateInput.addEventListener("change", () => {
    if (!createStartDateInput.value) return;

    let startDate = new Date(createStartDateInput.value);

    if (createBatchCourseDurationType === "Month") {
        startDate.setMonth(startDate.getMonth() + createBatchCourseDuration);
    } else if (createBatchCourseDurationType === "Year") {
        startDate.setFullYear(startDate.getFullYear() + createBatchCourseDuration);
    }

    // Convert back to YYYY-MM-DD format for date input
    let year = startDate.getFullYear();
    let month = String(startDate.getMonth() + 1).padStart(2, "0");
    let day = String(startDate.getDate()).padStart(2, "0");

    createEndDateInput.value = `${year}-${month}-${day}`;
});

// AJAX fetch functions with proper action parameter
function fetchBranchesForBatch(courseId) {
    fetch(`ajaxCourseController.php?action=getBranchesByCourse&courseId=${courseId}`)
        // .then(res => res.json())
        .then(res => res.text()) // <- change to text first
        .then(text => {
            // console.log("RAW BRANCH RESPONSE:", text); // 👀 check what PHP really sends
            return JSON.parse(text); // try convert manually
        })
        .then(data => {
            populateDropdown(createBatchForm.querySelector("[data-input='create-batches-course-branch']"), data, "Select Branch");
        }
        );
}

function fetchCourseDuration(courseId) {
    fetch(`ajaxCourseController.php?action=getCourseById&courseId=${courseId}`)
        // .then(res => res.json())
        .then(res => res.text()) // <- change to text first
        .then(text => {
            // console.log("RAW BRANCH RESPONSE:", text); // 👀 check what PHP really sends
            return JSON.parse(text); // try convert manually
        })
        .then(data => {
            createBatchCourseDuration = data.duration;
            createBatchCourseDurationType = data.duration_type;
        }
        );
}

// validating form
createBatchForm.addEventListener("submit", (e)=> {
    let messages = [];
    if (!createBatchCourseId.value) messages.push("Please select a course.");
    if (!createBatchForm.querySelector("#create-batches-course-branch").value) messages.push("Please select a branch.");
    if (!createBatchForm.querySelector("#create-batch-name").value) messages.push("Please enter batch name.");
    if (!createStartDateInput.value) messages.push("Please enter start date.");
    if (!createEndDateInput.value) messages.push("Invalid end date.");

    if (messages.length > 0) {
        e.preventDefault();
        alert(messages.join("\n"));
    }
});

// =================================== Add Student to Batch Section ===========================
const addStudentToBatchForm = document.querySelector(".main .add-student-to-batch #add-student-to-batch-form"); // form
const addStudentToBatchCourseId = addStudentToBatchForm.querySelector("#add-student-to-batch-course-id");
const addStudentToBatchBranch = addStudentToBatchForm.querySelector("#add-student-to-batch-course-branch");
const addStudentToBatchBatchId = addStudentToBatchForm.querySelector("#add-student-to-batch-course-batch-id");

addStudentToBatchCourseId.addEventListener("change", ()=> {
    fetchBranchesForAddStudentToBatch(addStudentToBatchCourseId.value);
});

addStudentToBatchBranch.addEventListener("change", ()=> {
    fetchBatchesForAddStduentToBatch(addStudentToBatchCourseId.value, addStudentToBatchBranch.value);
});

// validating form
addStudentToBatchForm.addEventListener("submit", (e)=> {
    let messages = [];
    if (!addStudentToBatchCourseId.value) messages.push("Please select a course.");
    if (!addStudentToBatchBranch.value) messages.push("Please select a branch.");
    if (!addStudentToBatchBatchId.value) messages.push("Please select batch.");
    if (!addStudentToBatchForm.querySelector("#add-student-to-batch-student-email").value) messages.push("Please enter student email.");

    if (messages.length > 0) {
        e.preventDefault();
        alert(messages.join("\n"));
    }
});


// AJAX fetch functions with proper action parameter
function fetchBranchesForAddStudentToBatch(courseId) {
    fetch(`ajaxCourseController.php?action=getBranchesByCourse&courseId=${courseId}`)
        // .then(res => res.json())
        .then(res => res.text()) // <- change to text first
        .then(text => {
            // console.log("RAW BRANCH RESPONSE:", text); // 👀 check what PHP really sends
            return JSON.parse(text); // try convert manually
        })
        .then(data => {
            populateDropdown(addStudentToBatchForm.querySelector("[data-input='add-student-to-batch-course-branch']"), data, "Select Branch");
        });
}

function fetchBatchesForAddStduentToBatch(courseId, branch) {
    fetch(`ajaxCourseController.php?action=getActiveBatchesByCourseAndBranch&courseId=${courseId}&branch=${branch}`)
        .then(res => res.text()) // read as text
        .then(text => {
            // console.log("RAW BATCHES RESPONSE:", text); // 👀 see what you really got
            if (!text) return []; // empty response → return empty array
            return JSON.parse(text);
        })
        .then(data => populateDropdown(addStudentToBatchForm.querySelector("[data-input='add-student-to-batch-course-batch-id']"), data, "Select Batch"));
}

// ==================================== Manage Batches Section ========================
document.addEventListener("DOMContentLoaded", () => { 
    const manageBacthesSearchInput = document.querySelector(".main .manage-batches .filters #manage-batches-search-batch-name"); // input
    const manageBacthesSearchCourse = document.querySelector(".main .manage-batches .filters #manage-batches-filter-course-id"); // course
    const manageBacthesSearchBatch = document.querySelector(".main .manage-batches .filters #manage-batches-filter-branch"); // branch
    const manageBacthesSearchStatus = document.querySelector(".main .manage-batches .filters #manage-batches-filter-status"); // status
    
    const manageBatchesTable = document.querySelector(".main .manage-batches .table-container #batch-details-table"); // table
    if (!manageBatchesTable) return;
    const manageBatchesTableRows = manageBatchesTable.querySelectorAll("tbody tr"); // get all rows from table

    // search input filter
    manageBacthesSearchInput.addEventListener("keyup", filterManageBatchesTable);

    // dropdown filters
    document.querySelectorAll(".main .manage-batches .filters .dropdown").forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.dataset.input);

        dropdown.querySelectorAll(".dropdown-options li").forEach(option => {
            option.addEventListener("click", () => {
                // update hidden input
                hiddenInput.value = option.dataset.value;

                // update visible text
                dropdown.querySelector(".dropdown-selected span").textContent = option.textContent;

                // highlight selected option
                dropdown.querySelectorAll("li").forEach(li => li.classList.remove("active"));
                option.classList.add("active");

                // run filter
                filterManageBatchesTable();
            });
        });
    });

    // ========= View Students Logic
    const manageBatchesTableViewStduent = manageBatchesTable.querySelectorAll(".view-students");// view student option
    const manageBacthesViewStudentDiv = document.querySelector(".main .manage-batches .view-students-batches");
    const viewStudentList = manageBacthesViewStudentDiv.querySelector("#students-list");
    const viewStudentCloseBtn = manageBacthesViewStudentDiv.querySelector("#close-students-panel");
    
    // event listener for view students
    if (manageBatchesTableViewStduent) {
        manageBatchesTableViewStduent.forEach((viewStudent) => {
            viewStudent.addEventListener("click", ()=>{
                // Parse students JSON from data attribute
                const students = JSON.parse(viewStudent.dataset.students);
                const studentBatchId = viewStudent.dataset.batchid;
                
                // div head text with batch name
                manageBacthesViewStudentDiv.querySelector(".panel-header h2").textContent = `Batch: ${viewStudent.dataset.batchname}`;
                // console.log(viewStudent.dataset.batchname);
                
                // show div
                manageBacthesViewStudentDiv.style.display = "flex";
                viewStudentList.innerHTML = "";

                if (students.length > 0) {
                    students.forEach(student => {
                        const row = document.createElement("tr");
                        row.innerHTML = `
                            <td>${student.full_name}</td>
                            <td>${student.email}</td>
                            <td>
                                <button type="submit" class="delete-student" 
                                    name="delete_student_from_batch" 
                                    data-batchid="${studentBatchId}" 
                                    data-studentid="${student.student_id}">
                                    Delete
                                </button>
                            </td>
                        `;
                        viewStudentList.appendChild(row);
                    });

                    // Attach delete listeners
                    document.querySelectorAll(".delete-student").forEach(delBtn => {
                        delBtn.addEventListener("click", () => {
                            const studentId = delBtn.dataset.studentid;
                            const batchId = delBtn.dataset.batchid;
                            console.log(studentId, batchId);

                            if (confirm("Are you sure you want to remove this student?")) {
                                // send AJAX request
                                fetch(`ajaxCourseController.php?action=deleteStduentFromBatch&batchId=${batchId}&studentId=${studentId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            delBtn.closest("tr").remove(); // remove row visually
                                            alert(data.message);
                                        } else {
                                            alert(data.message);
                                        }
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        alert("Something went wrong while deleting student");
                                    });
                            }
                        });
                    });
                } else {
                    viewStudentList.innerHTML = `<tr><td colspan="3">No students found</td></tr>`;
                }
            });

            // Close panel
            viewStudentCloseBtn.addEventListener("click", () => {
                manageBacthesViewStudentDiv.style.display = "none";
            });
        });
    }
    
    function filterManageBatchesTable() {
        const searchText = manageBacthesSearchInput.value.trim().toLowerCase();
        const courseText = manageBacthesSearchCourse.value.trim().toLowerCase();
        const branchText = manageBacthesSearchBatch.value.trim().toLowerCase();
        const statusText = manageBacthesSearchStatus.value.trim().toLowerCase();

        let anyMatch = false;
        const rows = manageBatchesTable.querySelectorAll("tbody tr");

        rows.forEach(row => {
            // skip the "no data" row if present
            if (row.id === "no-data-row") return;

            // adjust indexes if your columns differ
            const batchName = (row.cells[0]?.innerText || "").trim().toLowerCase();
            const course = (row.cells[1]?.innerText || "").trim().toLowerCase();
            const branch = (row.cells[2]?.innerText || "").trim().toLowerCase();
            const status = (row.cells[4]?.innerText || "").trim().toLowerCase();

            // 1) Apply dropdown filters first (if they fail, hide row immediately)
            if (courseText && courseText !== "all" && course !== courseText) { row.style.display = "none"; return; }
            if (branchText && branchText !== "all" && branch !== branchText) { row.style.display = "none"; return; }
            if (statusText && statusText !== "all" && status !== statusText) { row.style.display = "none"; return; }

            // 2) Search match: if no search text -> keep the row (dropdowns already applied)
            let searchMatches = true;
            if (searchText) {
            const batchMatch = batchName.includes(searchText);

            // student match from the view-students element inside the same row
            let studentMatch = false;
            const viewStudentBtn = row.querySelector(".view-students");
            if (viewStudentBtn && viewStudentBtn.dataset.students) {
                try {
                const studentsData = JSON.parse(viewStudentBtn.dataset.students);
                studentMatch = studentsData.some(stu => {
                    const name = (stu.full_name || "").toLowerCase();
                    const email = (stu.email || "").toLowerCase();
                    return name.includes(searchText) || email.includes(searchText);
                });
                } catch (err) {
                console.error("Invalid students JSON for row:", viewStudentBtn.dataset.students, err);
                }
            }

            // IMPORTANT: use OR — show if batch matches OR any student matches
            searchMatches = batchMatch || studentMatch;
            }

            if (searchMatches) {
            row.style.display = "";
            anyMatch = true;
            } else {
            row.style.display = "none";
            }
        });

        // show/hide No Data Found row
        let noDataRow = manageBatchesTable.querySelector("#no-data-row");
        if (!anyMatch) {
            if (!noDataRow) {
            noDataRow = document.createElement("tr");
            noDataRow.id = "no-data-row";
            noDataRow.innerHTML = `<td colspan="8" style="text-align:center;">No Data Found</td>`;
            manageBatchesTable.querySelector("tbody").appendChild(noDataRow);
            }
        } else {
            if (noDataRow) noDataRow.remove();
        }
        }


    // edit batch logic
    const manageBacthesEditBatchBtn = manageBatchesTable.querySelectorAll(".edit-batch-btn");
    const editBatchDiv = document.querySelector(".main .manage-batches .edit-batch-name-status");
    const editBatchForm = editBatchDiv.querySelector("#edit-batch-form");
    const editBatchFormName = editBatchForm.querySelector("#edit-batch-name");
    const editBatchFormStatus = editBatchForm.querySelector("#edit-batch-status");
    const editBatchFormStatusDropDownSpan = editBatchForm.querySelector(".dropdown .dropdown-selected span");
    const editBatchFormSaveEditBtn = editBatchForm.querySelector("#save-edit-batch");
    const editBatchFormCloseButton = editBatchDiv.querySelector("#close-edit-batch-panel");

    manageBacthesEditBatchBtn.forEach((editBtn) => {
        editBtn.addEventListener("click", ()=>{
            const row = editBtn.closest("tr");
            const name = row.cells[0].innerText;
            const status = row.cells[4].innerText;

            if (name.length > 0 && status.length > 0) {
                editBatchFormName.value = name;
                editBatchFormStatus.value = status;
                editBatchFormStatusDropDownSpan.textContent = status;
            }

            editBatchDiv.style.display = "flex";

            editBatchFormSaveEditBtn.value = editBtn.dataset.batchid;
        });

        // Close panel
        editBatchFormCloseButton.addEventListener("click", () => {
            editBatchDiv.style.display = "none";
        });

    });

    // edit batch form validation
    editBatchForm.addEventListener("submit", (e)=>{
        const name = editBatchFormName.value.trim();
        const status = editBatchFormStatus.value.trim();

        if (!name || !status) {
            alert("Please fill batch name and status");
            e.preventDefault();
            return;
        }
    });
});


// ================================== Manage Students Section ============================
document.addEventListener("DOMContentLoaded", ()=>{
    const manageStudentsSearchInput = document.querySelector(".main .manage-students .filters #manage-students-search-input"); // input
    const manageStudentsSearchGender = document.querySelector(".main .manage-students .filters #manage-stduents-gender"); // gender
    const manageStudentsSearchProvince = document.querySelector(".main .manage-students .filters #manage-stduents-province"); // province

    const manageStudentTable = document.querySelector(".main .manage-students .table-container .manage-students-table");

    if (!manageStudentTable) return;
    const manageStudentTableRows = manageStudentTable.querySelectorAll("tbody tr");

    function filterManageStudentTable() {
        const searchText = manageStudentsSearchInput.value.toLowerCase();
        const genderText = manageStudentsSearchGender.value.toLowerCase();
        const provinceText = manageStudentsSearchProvince.value.toLowerCase();

        let anyMatch = false;

        manageStudentTableRows.forEach(row => {
            // skip empty placeholder rows
            if (row.cells.length < 7) return;

            const studentName = row.cells[0].innerText.trim().toLowerCase();
            const studentEmail = row.cells[1].innerText.trim().toLowerCase();
            const studentNIC = row.cells[2].innerText.trim().toLowerCase();
            const studentMobile = row.cells[7].innerText.trim().toLowerCase();
            const gender = row.cells[4].innerText.trim().toLowerCase();
            const province = row.cells[6].innerText.trim().toLowerCase();

            let match = true;

            // search input: match in name OR email OR nic
            if (searchText && !(
                studentName.includes(searchText) ||
                studentEmail.includes(searchText) ||
                studentNIC.includes(searchText) ||
                studentMobile.includes(searchText)
            )) {
                match = false;
            }

            if (genderText && genderText !== "all" && gender !== genderText) match = false;
            if (provinceText && provinceText !== "all" && province !== provinceText) match = false;

            if (match) {
                row.style.display = "";
                anyMatch = true;
            } else {
                row.style.display = "none";
            }
        });

        // show "No Data Found" row if nothing matches
        let noDataRow = document.querySelector("#no-data-row");
        if (!anyMatch) {
            if (!noDataRow) {
                noDataRow = document.createElement("tr");
                noDataRow.id = "no-data-row";
                noDataRow.innerHTML = `<td colspan="8" style="text-align: center;">No Data Found</td>`;
                manageStudentTable.querySelector("tbody").appendChild(noDataRow);
            }
        } else {
            if (noDataRow) noDataRow.remove();
        }
    }

    // search input filter
    manageStudentsSearchInput.addEventListener("keyup", filterManageStudentTable);

    // dropdown filters
    document.querySelectorAll(".main .manage-students .filters .dropdown").forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.dataset.input);

        dropdown.querySelectorAll(".dropdown-options li").forEach(option => {
            option.addEventListener("click", () => {
                // update hidden input
                hiddenInput.value = option.dataset.value;

                // update visible text
                dropdown.querySelector(".dropdown-selected span").textContent = option.textContent;

                // highlight selected option
                dropdown.querySelectorAll("li").forEach(li => li.classList.remove("active"));
                option.classList.add("active");

                // run filter
                filterManageStudentTable();
            });
        });
    });


});


// ================================= Non stydent queries section ==================================
document.addEventListener("DOMContentLoaded", ()=>{
    const nonStduentStatus = document.querySelector(".main .non-students-queries #non-students-queries-status"); // status
    const nonStduentQueriesTable = document.querySelector(".main .non-students-queries .table-container #non-students-queries-table"); // table

    if (!nonStduentQueriesTable) return;
    const nonStduentQueriesTableRows = nonStduentQueriesTable.querySelectorAll("tbody tr"); // get all rows from table

    function filterNonStudentQueriesTable() {
        const statusText = nonStduentStatus.value.toLowerCase();

        let anyMatch = false;

        nonStduentQueriesTableRows.forEach(row => {
            if (row.cells.length < 5) return;

            // const fullName = row.cells[0].innerText.trim();
            // const email = row.cells[1].innerText.trim();
            // const courseName = row.cells[2].innerText.trim();
            const status = row.cells[5].innerText.trim().toLowerCase();

            let match = true;

            // search input: match in name OR email OR nic
            if (statusText && statusText !== "all" && status !== statusText) match = false;

            if (match) {
                row.style.display = "";
                anyMatch = true;
            } else {
                row.style.display = "none";
            }
        });

        // show "No Data Found" row if nothing matches
        let noDataRow = document.querySelector("#no-data-row");
        if (!anyMatch) {
            if (!noDataRow) {
                noDataRow = document.createElement("tr");
                noDataRow.id = "no-data-row";
                noDataRow.innerHTML = `<td colspan="8" style="text-align: center;">No Data Found</td>`;
                nonStduentQueriesTable.querySelector("tbody").appendChild(noDataRow);
            }
        } else {
            if (noDataRow) noDataRow.remove();
        }
    }

    // dropdown filters
    document.querySelectorAll(".main .non-students-queries .filters .dropdown").forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.dataset.input);

        dropdown.querySelectorAll(".dropdown-options li").forEach(option => {
            option.addEventListener("click", () => {
                // update hidden input
                hiddenInput.value = option.dataset.value;

                // update visible text
                dropdown.querySelector(".dropdown-selected span").textContent = option.textContent;

                // highlight selected option
                dropdown.querySelectorAll("li").forEach(li => li.classList.remove("active"));
                option.classList.add("active");

                // run filter
                filterNonStudentQueriesTable();
            });
        });
    });

    // Attach event listener to all Gmail buttons
    document.querySelectorAll(".open-gmail-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const email = btn.dataset.email;
            const name = btn.dataset.name;
            const course = btn.dataset.course;

            // Subject + body text
            const subject = encodeURIComponent(`Regarding your query about ${course}`);
            const body = encodeURIComponent(
                `Hello ${name},

Thank you for reaching out to SkillPro Institute. 
We have received your inquiry regarding the course: "${course}".

Our team will review your message and get back to you shortly.

Best regards,  
SkillPro Institute  
📧 skillproinstitute2025@gmail.com  
🌐 www.skillpro.lk
📞 +94 77 111 2222`
            );

            // Gmail compose URL
            const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${email}&su=${subject}&body=${body}`;

            // Open in new tab
            window.open(gmailUrl, "_blank");
        });
    });

});



// ================================= Non stydent queries section ==================================
document.addEventListener("DOMContentLoaded", ()=>{
    const stduentStatus = document.querySelector(".main .students-queries #students-queries-status"); // status
    const stduentQueriesTable = document.querySelector(".main .students-queries .table-container #students-queries-table"); // table

    if (!stduentQueriesTable) return;
    const stduentQueriesTableRows = stduentQueriesTable.querySelectorAll("tbody tr"); // get all rows from table

    function filterStudentQueriesTable() {
        const statusText = stduentStatus.value.toLowerCase();

        let anyMatch = false;

        stduentQueriesTableRows.forEach(row => {
            if (row.cells.length < 4) return;
            const status = row.cells[4].innerText.trim().toLowerCase();

            let match = true;

            // search input: match in name OR email OR nic
            if (statusText && statusText !== "all" && status !== statusText) match = false;

            if (match) {
                row.style.display = "";
                anyMatch = true;
            } else {
                row.style.display = "none";
            }
        });

        // show "No Data Found" row if nothing matches
        let noDataRow = document.querySelector("#no-data-row");
        if (!anyMatch) {
            if (!noDataRow) {
                noDataRow = document.createElement("tr");
                noDataRow.id = "no-data-row";
                noDataRow.innerHTML = `<td colspan="8" style="text-align: center;">No Data Found</td>`;
                stduentQueriesTable.querySelector("tbody").appendChild(noDataRow);
            }
        } else {
            if (noDataRow) noDataRow.remove();
        }
    }

    // dropdown filters
    document.querySelectorAll(".main .students-queries .filters .dropdown").forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.dataset.input);

        dropdown.querySelectorAll(".dropdown-options li").forEach(option => {
            option.addEventListener("click", () => {
                // update hidden input
                hiddenInput.value = option.dataset.value;

                // update visible text
                dropdown.querySelector(".dropdown-selected span").textContent = option.textContent;

                // highlight selected option
                dropdown.querySelectorAll("li").forEach(li => li.classList.remove("active"));
                option.classList.add("active");

                // run filter
                filterStudentQueriesTable();
            });
        });
    });

    // Attach event listener to all Gmail buttons
    document.querySelectorAll(".open-gmail-btn-squery").forEach(btn => {
        btn.addEventListener("click", () => {
            const email = btn.dataset.email; // recipient email
            const name = btn.dataset.name;   // recipient name
            const message = btn.dataset.message || ""; // optional inquiry message

            // Subject + body text
            const subject = encodeURIComponent(`Regarding your inquiry`);
            const body = encodeURIComponent(
    `Hello ${name},

    Thank you for reaching out to SkillPro Institute. 
    We have received your inquiry.

    ${message ? "Your message:\n" + message + "\n\n" : ""}Our team will review your message and get back to you shortly.

    Best regards,  
    SkillPro Institute  
    📧 skillproinstitute2025@gmail.com  
    🌐 www.skillpro.lk  
    📞 +94 77 111 2222`
            );

            // Gmail compose URL
            const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${email}&su=${subject}&body=${body}`;

            // Open in new tab
            window.open(gmailUrl, "_blank");
        });
    });


});


// =================================== Add time table section ============
const addnewScheduleForm = document.querySelector(".main .add-new-schedule form"); //form

// course id input
const addnewScheduleCourseId = addnewScheduleForm.querySelector("#new-schedule-course-id");

// Branch input
const addnewScheduleBranch = addnewScheduleForm.querySelector("#new-schedule-course-branch");

// batch input
const addnewScheduleCourseBatch = addnewScheduleForm.querySelector("#new-schedule-course-batch");

// module input 
const addnewScheduleCourseModuleId = addnewScheduleForm.querySelector("#new-schedule-course-module-id");

// instructor name input
const addnewScheduleInstructorName = addnewScheduleForm.querySelector("#new-schedule-course-module-instructor-name");

// instructor id input
const addnewScheduleInstructorId = addnewScheduleForm.querySelector("#new-schedule-course-module-instructor-id");

addnewScheduleCourseId.addEventListener("change", ()=> {
    fetchBranchesForAddSchedule(addnewScheduleCourseId.value);
    fetchModulesForAddSchedule(addnewScheduleCourseId.value);
});

addnewScheduleBranch.addEventListener("change", ()=> {
    fetchBatchesForAddSchedule(addnewScheduleCourseId.value, addnewScheduleBranch.value);
});

// Attach events
addnewScheduleCourseBatch.addEventListener("change", fetchInstructorForAddSchedule);
addnewScheduleCourseModuleId.addEventListener("change", fetchInstructorForAddSchedule);

// AJAX fetch functions with proper action parameter
function fetchBranchesForAddSchedule(courseId) {
    fetch(`ajaxCourseController.php?action=getBranchesByCourse&courseId=${courseId}`)
        // .then(res => res.json())
        .then(res => res.text()) // <- change to text first
        .then(text => {
            // console.log("RAW BRANCH RESPONSE:", text); // 👀 check what PHP really sends
            return JSON.parse(text); // try convert manually
        })
        .then(data => populateDropdown(addnewScheduleForm.querySelector("[data-input='new-schedule-course-branch']"), data, "Select Branch"));
}

function fetchModulesForAddSchedule(courseId) {
    fetch(`ajaxCourseController.php?action=getModulesByCourse&courseId=${courseId}`)
        .then(res => res.text()) // read as text
        .then(text => {
            // console.log("RAW MODULES RESPONSE:", text); // 👀 see what you really got
            if (!text) return []; // empty response → return empty array
            return JSON.parse(text);
        })
        .then(data => populateDropdown(addnewScheduleForm.querySelector("[data-input='new-schedule-course-module-id']"), data, "Select Course Module"));
}

function fetchBatchesForAddSchedule(courseId, branch) {
    fetch(`ajaxCourseController.php?action=getActiveBatchesByCourseAndBranch&courseId=${courseId}&branch=${branch}`)
        .then(res => res.text()) // read as text
        .then(text => {
            // console.log("RAW BATCHES RESPONSE:", text); // 👀 see what you really got
            if (!text) return []; // empty response → return empty array
            return JSON.parse(text);
        })
        .then(data => populateDropdown(addnewScheduleForm.querySelector("[data-input='new-schedule-course-batch']"), data, "Select Batch"));
}

// Common handler
function fetchInstructorForAddSchedule() {
    const batchValue  = addnewScheduleCourseBatch.value.trim();
    const moduleValue = addnewScheduleCourseModuleId.value.trim();
    const branchValue = addnewScheduleBranch.value.trim();

    if (batchValue && moduleValue) {
        fetch(`ajaxCourseController.php?action=getInstructorsByModuleBatchBranch&batchId=${batchValue}&moduleId=${moduleValue}&branch=${encodeURIComponent(branchValue)}`)
        .then(res => res.text()) // <- change to text first
        .then(text => {
            // console.log("RAW Instructor:", text); // check what PHP really sends
            return JSON.parse(text); // try convert manually
        })
        .then(data => {
            addnewScheduleInstructorName.value = data[0].instructor_name;
            addnewScheduleInstructorId.value = data[0].instructor_id;
        });
    }
}

// form handling
document.addEventListener("DOMContentLoaded", () => {
    addnewScheduleForm.addEventListener("submit", (e) => {
        let isValid = true;
        let messages = [];

        const courseId = addnewScheduleCourseId.value.trim();
        const branch = addnewScheduleBranch.value.trim();
        const batch = addnewScheduleCourseBatch.value.trim();
        const moduleId = addnewScheduleCourseModuleId.value.trim();
        const instructorId = addnewScheduleInstructorId.value.trim();
        const location = addnewScheduleForm.querySelector("#new-schedule-course-class-location").value.trim();
        const date = addnewScheduleForm.querySelector("#new-schedule-course-class-date").value.trim();
        const startTime = addnewScheduleForm.querySelector("#new-schedule-course-class-start-time").value.trim();
        const endTime = addnewScheduleForm.querySelector("#new-schedule-course-class-end-time").value.trim();

        if (!courseId) {
            isValid = false;
            messages.push("Please select a course.");
        }

        if (!branch) {
            isValid = false;
            messages.push("Please select a branch.");
        }

        if (!batch) {
            isValid = false;
            messages.push("Please select a batch.");
        }

        if (!moduleId) {
            isValid = false;
            messages.push("Please select a course module.");
        }

        if (!instructorId) {
            isValid = false;
            messages.push("No instructor assigned for this module.");
        }

        if (!location) {
            isValid = false;
            messages.push("Please enter class location.");
        }

        if (!date) {
            isValid = false;
            messages.push("Please select a date.");
        } else {
            // Date check: no past dates
            const today = new Date().toISOString().split("T")[0];
            if (date < today) {
                isValid = false;
                messages.push("Date cannot be in the past.");
            }
        }

        if (!startTime) {
            isValid = false;
            messages.push("Please select a start time.");
        }

        if (!endTime) {
            isValid = false;
            messages.push("Please select a end time.");
        }
        
        if (startTime >= endTime) {
            isValid = false;
            messages.push("End time must be after the start time.");
        }

        if (!isValid) {
            e.preventDefault();
            alert(messages.join("\n"));
        }
    });
});


// =================================== Manage Time Table Setion ===============
// Search input
const manageTimeTableSearchInput = document.querySelector(".main .manage-timetable .filters #manage-timetable-search-input");

// course input
const manageTimeTableCourseInput = document.querySelector(".main .manage-timetable .filters #manage-timetable-filter-course");

// branch input
const manageTimeTableBranchInput = document.querySelector(".main .manage-timetable .filters #manage-timetable-filter-branch");

// status input
const manageTimeTableBranchStatus = document.querySelector(".main .manage-timetable .filters #manage-timetable-filter-status");

// date input
const manageTimeTableDateInput = document.querySelector(".main .manage-timetable .filters #manage-timetable-filter-date");

// table
const manageTimeTable = document.querySelector(".main .manage-timetable .table-container #time-schedule-table");


document.addEventListener("DOMContentLoaded", ()=> {
    if (!manageTimeTable) return;
    // rows
    const manageTimeTableRows = manageTimeTable.querySelectorAll("tbody tr");

    function filterTimeTable() {
        const searchText = manageTimeTableSearchInput.value.toLowerCase();
        const courseText = manageTimeTableCourseInput.value.toLowerCase();
        const branchText = manageTimeTableBranchInput.value.toLowerCase();
        const dateText = manageTimeTableDateInput.value.toLowerCase();
        const statusText = manageTimeTableBranchStatus.value.toLowerCase();

        let anyMatch = false;

        manageTimeTableRows.forEach(row => {
            // skip empty placeholder rows
            if (row.cells.length < 7) return;

            const course = row.cells[0].innerText.trim().toLowerCase();
            const branch = row.cells[1].innerText.trim().toLowerCase();
            const batch = row.cells[2].innerText.trim().toLowerCase();
            const module = row.cells[3].innerText.trim().toLowerCase();
            const instructor = row.cells[4].innerText.trim().toLowerCase();
            const date = row.cells[5].innerText.trim().toLowerCase();
            const status = row.cells[8].innerText.trim().toLowerCase();

            let match = true;

            // search input: match in name OR email OR nic
            if (searchText && !(
                batch.includes(searchText) ||
                module.includes(searchText) ||
                instructor.includes(searchText)
            )) {
                match = false;
            }

            if (courseText && courseText !== "all" && course !== courseText) match = false;
            if (branchText && branchText !== "all" && branch !== branchText) match = false;
            if (dateText && dateText !== "all" && date !== dateText) match = false;
            if (statusText && statusText !== "all" && status !== statusText) match = false;

            if (match) {
                row.style.display = "";
                anyMatch = true;
            } else {
                row.style.display = "none";
            }
        });

        // show "No Data Found" row if nothing matches
        let noDataRow = document.querySelector("#no-data-row");
        if (!anyMatch) {
            if (!noDataRow) {
                noDataRow = document.createElement("tr");
                noDataRow.id = "no-data-row";
                noDataRow.innerHTML = `<td colspan="8" style="text-align: center;">No Data Found</td>`;
                manageTimeTable.querySelector("tbody").appendChild(noDataRow);
            }
        } else {
            if (noDataRow) noDataRow.remove();
        }
    }

    // search input filter
    manageTimeTableSearchInput.addEventListener("keyup", filterTimeTable);
    
    // date input filter
    manageTimeTableDateInput.addEventListener("change", filterTimeTable);

    // dropdown filters
    document.querySelectorAll(".main .manage-timetable .filters .dropdown").forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.dataset.input);

        dropdown.querySelectorAll(".dropdown-options li").forEach(option => {
            option.addEventListener("click", () => {
                // update hidden input
                hiddenInput.value = option.dataset.value;

                // update visible text
                dropdown.querySelector(".dropdown-selected span").textContent = option.textContent;

                // highlight selected option
                dropdown.querySelectorAll("li").forEach(li => li.classList.remove("active"));
                option.classList.add("active");

                // run filter
                filterTimeTable();
            });
        });
    });
});


// =================================== Add Notice Section =========================
document.addEventListener("DOMContentLoaded", function () {
    const addNoticeForm = document.querySelector(".main .add-notice form");

    addNoticeForm.addEventListener("submit", function (e) {
        let title = addNoticeForm.querySelector("#add-notice-title").value.trim();
        let content = addNoticeForm.querySelector("#add-notice-content").value.trim();
        let audience = addNoticeForm.querySelector("#add-notice-audience").value.trim();
        let branch = addNoticeForm.querySelector("#add-notice-branch").value.trim();
        let startDate = addNoticeForm.querySelector("#add-notice-start-date").value;
        let endDate = addNoticeForm.querySelector("#add-notice-end-date").value;

        // Get today's date (yyyy-mm-dd)
        let today = new Date();
        today.setHours(0, 0, 0, 0); // ignore time
        let todayStr = today.toISOString().split("T")[0];

        // Validation checks
        if (!title) {
            alert("Title is required");
            e.preventDefault();
            return;
        }

        if (!content) {
            alert("Content is required");
            e.preventDefault();
            return;
        }

        if (!audience) {
            alert("Please select an Audience");
            e.preventDefault();
            return;
        }

        if (!branch) {
            alert("Please select a Branch");
            e.preventDefault();
            return;
        }

        if (!startDate || !endDate) {
            alert("Please select both Start and End dates");
            e.preventDefault();
            return;
        }

        if (startDate < todayStr) {
            alert("Start Date cannot be in the past");
            e.preventDefault();
            return;
        }

        if (endDate < todayStr) {
            alert("End Date cannot be in the past");
            e.preventDefault();
            return;
        }
    });
});

// =================================== Manage Notice Section =========================
// Search input
const manageNoticesSearchinput = document.querySelector(".main .manage-notices .filters #manage-notices-search-name");

// branch input
const manageNoticesBranch = document.querySelector(".main .manage-notices .filters #manage-notices-filter-branch");

// date input
const manageNoticesDate = document.querySelector(".main .manage-notices .filters #manage-notices-filter-date");

// status input
const manageNoticesStatus = document.querySelector(".main .manage-notices .filters #manage-notices-filter-status");

// table
const manageNoticesTable = document.querySelector(".main .manage-notices .table-container #notices-table");


document.addEventListener("DOMContentLoaded", ()=> {
    if (!manageNoticesTable) return;
    // rows
    const manageNoticesTableRows = manageNoticesTable.querySelectorAll("tbody tr");

    function fiilterNotices() {
        const searchText = manageNoticesSearchinput.value.toLowerCase();
        const branchText = manageNoticesBranch.value.toLowerCase();
        const dateText = manageNoticesDate.value.toLowerCase();
        const statusText = manageNoticesStatus.value.toLowerCase();

        let anyMatch = false;

        manageNoticesTableRows.forEach(row => {
            // skip empty placeholder rows
            if (row.cells.length < 7) return;

            const title = row.cells[0].innerText.trim().toLowerCase();
            const content = row.cells[1].innerText.trim().toLowerCase();
            const branch = row.cells[3].innerText.trim().toLowerCase();
            const sDate = row.cells[4].innerText.trim().toLowerCase();
            const status = row.cells[7].innerText.trim().toLowerCase();
            
            let match = true;

            console.log(title.includes(searchText));

            // search input
            if (searchText && !(
                title.includes(searchText) ||
                content.includes(searchText)
            )) {
                match = false;
            }

            if (branchText && branchText !== "all" && branch !== branchText) match = false;
            if (dateText && dateText !== "all" && sDate !== dateText) match = false;
            if (statusText && statusText !== "all" && status !== statusText) match = false;

            if (match) {
                row.style.display = "";
                anyMatch = true;
            } else {
                row.style.display = "none";
            }
        });

        // show "No Data Found" row if nothing matches
        let noDataRow = document.querySelector("#no-data-row");
        if (!anyMatch) {
            if (!noDataRow) {
                noDataRow = document.createElement("tr");
                noDataRow.id = "no-data-row";
                noDataRow.innerHTML = `<td colspan="8" style="text-align: center;">No Data Found</td>`;
                manageNoticesTable.querySelector("tbody").appendChild(noDataRow);
            }
        } else {
            if (noDataRow) noDataRow.remove();
        }
    }

    // search input filter
    manageNoticesSearchinput.addEventListener("keyup", fiilterNotices);
    
    // date input filter
    manageNoticesDate.addEventListener("change", fiilterNotices);

    // dropdown filters
    document.querySelectorAll(".main .manage-notices .filters .dropdown").forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.dataset.input);

        dropdown.querySelectorAll(".dropdown-options li").forEach(option => {
            option.addEventListener("click", () => {
                // update hidden input
                hiddenInput.value = option.dataset.value;

                // update visible text
                dropdown.querySelector(".dropdown-selected span").textContent = option.textContent;

                // highlight selected option
                dropdown.querySelectorAll("li").forEach(li => li.classList.remove("active"));
                option.classList.add("active");

                // run filter
                fiilterNotices();
            });
        });
    });
});


// =================================== Add Event Section =========================
document.addEventListener("DOMContentLoaded", function () {
    const addEventForm = document.querySelector(".main .add-event form");

    addEventForm.addEventListener("submit", function (e) {
        let title = addEventForm.querySelector("#add-event-title").value.trim();
        let description = addEventForm.querySelector("#add-event-description").value.trim();
        let branch = addEventForm.querySelector("#add-event-branch").value.trim();
        let startDate = addEventForm.querySelector("#add-event-start-date-time").value;
        let endDate = addEventForm.querySelector("#add-event-end-date-time").value;
        let imageFile = addEventForm.querySelector("#add-event-image").files[0];

        let errors = [];

        // Title validation
        if (!title) errors.push("Title is required.");

        // Description validation
        if (!description) errors.push("Description is required.");

        // Branch validation
        if (!branch) errors.push("Please select a Branch.");

        // Date validation
        if (!startDate || !endDate) {
            errors.push("Start and End Date/Time are required.");
        } else {
            let now = new Date();
            let start = new Date(startDate);
            let end = new Date(endDate);

            if (start < now) {
                errors.push("Start Date/Time cannot be in the past.");
            }
            if (end <= start) {
                errors.push("End Date/Time must be later than Start Date/Time.");
            }
        }

        // Image validation (optional)
        if (imageFile) {
            let allowedTypes = ["image/jpeg", "image/png", "image/jpg"];
            if (!allowedTypes.includes(imageFile.type)) {
                errors.push("Invalid image type. Only JPG, JPEG, PNG allowed.");
            }
            if (imageFile.size > 5 * 1024 * 1024) {
                errors.push("Image size must be less than 5MB.");
            }
        }

        // Show errors
        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join("\n"));
        }
    });
});

// =================================== Manage Event Section =========================
// Search input
const manageEventsSearchInut = document.querySelector(".main .manage-events .filters #manage-events-search-name");

// branch input
const manageEventsFilterBranch = document.querySelector(".main .manage-events .filters #manage-events-filter-branch");

// date input
const manageEventsFilterDate = document.querySelector(".main .manage-events .filters #manage-events-filter-date");

// status input
const manageEventsFilterStatus = document.querySelector(".main .manage-events .filters #manage-events-filter-status");

// table
const manageEventstable = document.querySelector(".main .manage-events .table-container #events-table");

document.addEventListener("DOMContentLoaded", ()=> {
    if (!manageEventstable) return;
    // rows
    const manageEventstableRows = manageEventstable.querySelectorAll("tbody tr");

    function fiilterEvents() {
        const searchText = manageEventsSearchInut.value.toLowerCase();
        const branchText = manageEventsFilterBranch.value.toLowerCase();
        const dateText = manageEventsFilterDate.value.toLowerCase();
        const statusText = manageEventsFilterStatus.value.toLowerCase();

        let anyMatch = false;

        manageEventstableRows.forEach(row => {
            // skip empty placeholder rows
            if (row.cells.length < 7) return;

            const title = row.cells[0].innerText.trim().toLowerCase();
            const description = row.cells[1].innerText.trim().toLowerCase();
            const branch = row.cells[3].innerText.trim().toLowerCase();
            const sDate = row.cells[4].innerText.trim().toLowerCase();
            const status = row.cells[7].innerText.trim().toLowerCase();

            console.log(title);
            console.log(description);
            console.log(branch);
            console.log(sDate);
            console.log(status);
            
            let match = true;

            // search input: match in name OR email OR nic
            if (searchText && !(
                title.includes(searchText) ||
                description.includes(searchText)
            )) {
                match = false;
            }

            if (branchText && branchText !== "all" && branch !== branchText) match = false;
            if (dateText && dateText !== "all" && sDate.includes(dateText)) match = false;
            if (statusText && statusText !== "all" && status !== statusText) match = false;

            if (match) {
                row.style.display = "";
                anyMatch = true;
            } else {
                row.style.display = "none";
            }
        });

        // show "No Data Found" row if nothing matches
        let noDataRow = document.querySelector("#no-data-row");
        if (!anyMatch) {
            if (!noDataRow) {
                noDataRow = document.createElement("tr");
                noDataRow.id = "no-data-row";
                noDataRow.innerHTML = `<td colspan="8" style="text-align: center;">No Data Found</td>`;
                manageEventstable.querySelector("tbody").appendChild(noDataRow);
            }
        } else {
            if (noDataRow) noDataRow.remove();
        }
    }

    // search input filter
    manageEventsSearchInut.addEventListener("keyup", fiilterEvents);
    
    // date input filter
    manageEventsFilterDate.addEventListener("change", fiilterEvents);

    // dropdown filters
    document.querySelectorAll(".main .manage-events .filters .dropdown").forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.dataset.input);

        dropdown.querySelectorAll(".dropdown-options li").forEach(option => {
            option.addEventListener("click", () => {
                // update hidden input
                hiddenInput.value = option.dataset.value;

                // update visible text
                dropdown.querySelector(".dropdown-selected span").textContent = option.textContent;

                // highlight selected option
                dropdown.querySelectorAll("li").forEach(li => li.classList.remove("active"));
                option.classList.add("active");

                // run filter
                fiilterEvents();
            });
        });
    });
});








// =================================== Student Enrollments Section =========================
document.addEventListener("DOMContentLoaded", () => {
    const studentRegistrationSearchInput = document.querySelector(".main .student-enrollments .filters #enroll-student-name-nic-email"); // input
    const studentRegistrationSearchCourse = document.querySelector(".main .student-enrollments .filters #student-enrolment-filter-course"); // course hidden input
    const studentRegistrationSearchBranch = document.querySelector(".main .student-enrollments .filters #student-enrolment-filter-branch"); // branch hidden input
    const studentRegistrationSearchStatus = document.querySelector(".main .student-enrollments .filters #student-enrolment-filter-status"); // status hidden input

    const studentRegistrationTable = document.querySelector(".main .student-enrollments .table-container #student-enrollment-table");
    if (!studentRegistrationTable) return;
    const studentRegistrationTablerows = studentRegistrationTable.querySelectorAll("tbody tr");

    function filterStudentRegistrationTable() {
        const searchText = studentRegistrationSearchInput.value.toLowerCase();
        const courseText = studentRegistrationSearchCourse.value.toLowerCase();
        const branchText = studentRegistrationSearchBranch.value.toLowerCase();
        const statusText = studentRegistrationSearchStatus.value.toLowerCase();

        let anyMatch = false;

        studentRegistrationTablerows.forEach(row => {
            // skip empty placeholder rows
            if (row.cells.length < 7) return;

            const studentName = row.cells[0].innerText.trim().toLowerCase();
            const studentEmail = row.cells[1].innerText.trim().toLowerCase();
            const studentNIC = row.cells[2].innerText.trim().toLowerCase();
            const course = row.cells[3].innerText.trim().toLowerCase();
            const branch = row.cells[4].innerText.trim().toLowerCase();
            const status = row.cells[6].innerText.trim().toLowerCase();

            let match = true;

            // search input: match in name OR email OR nic
            if (searchText && !(
                studentName.includes(searchText) ||
                studentEmail.includes(searchText) ||
                studentNIC.includes(searchText)
            )) {
                match = false;
            }

            if (courseText && courseText !== "all" && course !== courseText) match = false;
            if (branchText && branchText !== "all" && branch !== branchText) match = false;
            if (statusText && statusText !== "all" && status !== statusText) match = false;

            if (match) {
                row.style.display = "";
                anyMatch = true;
            } else {
                row.style.display = "none";
            }
        });

        // show "No Data Found" row if nothing matches
        let noDataRow = document.querySelector("#no-data-row");
        if (!anyMatch) {
            if (!noDataRow) {
                noDataRow = document.createElement("tr");
                noDataRow.id = "no-data-row";
                noDataRow.innerHTML = `<td colspan="8" style="text-align: center;">No Data Found</td>`;
                studentRegistrationTable.querySelector("tbody").appendChild(noDataRow);
            }
        } else {
            if (noDataRow) noDataRow.remove();
        }
    }

    // search input filter
    studentRegistrationSearchInput.addEventListener("keyup", filterStudentRegistrationTable);

    // dropdown filters
    document.querySelectorAll(".main .student-enrollments .filters .dropdown").forEach(dropdown => {
        const hiddenInput = document.getElementById(dropdown.dataset.input);

        dropdown.querySelectorAll(".dropdown-options li").forEach(option => {
            option.addEventListener("click", () => {
                // update hidden input
                hiddenInput.value = option.dataset.value;

                // update visible text
                dropdown.querySelector(".dropdown-selected span").textContent = option.textContent;

                // highlight selected option
                dropdown.querySelectorAll("li").forEach(li => li.classList.remove("active"));
                option.classList.add("active");

                // run filter
                filterStudentRegistrationTable();
            });
        });
    });
});
