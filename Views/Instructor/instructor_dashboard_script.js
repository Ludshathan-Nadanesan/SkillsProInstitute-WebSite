const toggleHamburgerManu = document.getElementById("menu-toggle");
const sidebar = document.getElementById("sidebar");

// hamburger menu & side bar 
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


// Get elements
const profileImg = document.querySelector(".header-nav .profile-mini img");
const profileSettings = document.querySelector(".header-nav .profile-mini .profile-settings");

// Toggle on click
profileImg.addEventListener("click", (e) => {
    e.stopPropagation(); // prevent body click from closing immediately
    profileSettings.classList.toggle("active");
});

// Hide when clicking outside
document.addEventListener("click", (e) => {
    if (!profileImg.contains(e.target) && !profileSettings.contains(e.target)) {
        profileSettings.classList.remove("active");
    }
});

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
        themeIcon.className = "fa-solid fa-sun";
        localStorage.setItem("theme", "dark");
    } else {
        document.body.classList.remove("dark");
        themeIcon.className = "fa-solid fa-moon";
        localStorage.setItem("theme", "light");
    }
};

//  On page refresh or loading
window.onload = () => {
    let savedTheme = localStorage.getItem("theme") || "light";
    setTheme(savedTheme);
}

// Force reload on back/forward navigation
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});


// Sidebar Buttons & Divs in the Main Section 
const sideBarBtns = document.querySelectorAll(".sidebar button[data-target]");
const subMenuToggles = document.querySelectorAll(".submenu-toggle");
const divs = document.querySelectorAll(".main > div");

// if exists, show last section; else dashboard by default
const lastSection = sessionStorage.getItem("activeSection");

if (lastSection && document.querySelector(`.main .${lastSection}`)) {
    document.querySelector(`.main .${lastSection}`).classList.add("active");
    document.querySelector(`.sidebar button[data-target="${lastSection}"]`).classList.add("active");
} else {
    // default → show dashboard
    document.querySelector(".main .profile").classList.add("active");
    document.querySelector('.sidebar button[data-target="profile"]').classList.add("active");
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


// provile view button from profile mini div
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





// ======================================= Province and Gender Drop down menu ======================
// custom dropdowns for form
const Dropdowns = document.querySelectorAll(".main .profile.active .form-group .dropdown");
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



// ===================================== Instructor Profile Details Edit Section =========================================== 
// Edit student profile details Logic
const editInsDtlsBtn = document.querySelector(".main .profile #instructor-details-edit #edit-details");
const insDtlsInputs = document.querySelectorAll(".main .profile #instructor-details-edit input[readonly]");
const saveInsDtlsBtn = document.querySelector(".main .profile #instructor-details-edit #save-changes");
const uploadImagebtn = document.querySelector(".main .profile .custom-file-upload");

editInsDtlsBtn.addEventListener("click", (e) => {
    e.preventDefault();
    
    // Show save button, hide edit button
    saveInsDtlsBtn.style.display = "block";
    editInsDtlsBtn.style.display = "none";
    uploadImagebtn.style.display = "block";

    // Make all inputs editable
    insDtlsInputs.forEach((input) => {
        input.removeAttribute("readonly");
        if (input.getAttribute("id") === "gender" || input.getAttribute("id") === "branch") {
            input.type = "hidden";
        }
    });

    Dropdowns.forEach((ele)=>{
        ele.style.display = "block";
    });
    
});


// instructor edit details form
const formInsEditDetils = document.getElementById('instructor-details-edit');
const insImagefileInput = document.getElementById('instructorImagefileInput');

formInsEditDetils.addEventListener('submit', function(e) {
    // e.preventDefault(); // Prevent default submission

    // Get form values
    const fullName = formInsEditDetils.querySelector('[name="full_name"]').value.trim();
    const gender = formInsEditDetils.querySelector('[name="gender"]').value;
    const address = formInsEditDetils.querySelector('[name="address"]').value.trim();
    const mobile = formInsEditDetils.querySelector('[name="mobile_number"]').value.trim();
    const bio = formInsEditDetils.querySelector('[name="bio"]').value;
    const branch = formInsEditDetils.querySelector('[name="branch"]').value;
    const special = formInsEditDetils.querySelector('[name="specialization"]').value;

    // Full Name validation: not empty, only letters and spaces
    if(fullName === '' || !/^[a-zA-Z\s]+$/.test(fullName)){
        e.preventDefault(); // Prevent default submission
        alert('Please enter a valid full name');
        return;
    }

    // Mobile Number validation: exactly 10 digits
    if(!/^\d{10}$/.test(mobile)){
        e.preventDefault(); // Prevent default submission
        alert('Please enter a valid 10-digit mobile number');
        return;
    }

    // Gender validation
    const genderValidValues = ["Male","Female"]
    if (!gender) {
        e.preventDefault(); // Prevent default submission
        alert('Please select a gender');
    } else {
        if (!genderValidValues.includes(gender)) {
            e.preventDefault(); // Prevent default submission
            alert("Please select a valid gender option");
        }
    }

    // Address validation
    if(address === ''){
        e.preventDefault(); // Prevent default submission
        alert('Please enter your address');
        return;
    }

    // Street Address validation
    if(bio === ''){
        e.preventDefault(); // Prevent default submission
        alert('Please enter your bio');
        return;
    }

    // specialization validation
    if(special === ''){
        e.preventDefault(); // Prevent default submission
        alert('Please enter your specialization');
        return;
    }

    // Province validation
    const validValues = ['Colombo', 'Kandy', 'Matara'];
    if (!branch) {
        e.preventDefault(); // Prevent default submission
        alert("Please select a branch");
    } else {
        if (!validValues.includes(branch)) {
            e.preventDefault(); // Prevent default submission
            alert("Please select a valid branch option");
        }
    }

    // Image validation
    const file = insImagefileInput.files[0]; // get selected file
    
    if(file) {
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if(!allowedTypes.includes(file.type)) {
            e.preventDefault(); // Prevent default submission
            alert('Please upload a valid image file (jpg, jpeg, png)');
            return;
        }

        const maxSize = 2 * 1024 * 1024; // 2MB
        if(file.size > maxSize) {
            e.preventDefault(); // Prevent default submission
            alert('Image size must be less than 2MB');
            return;
        }
    }

    // // All validations passed, submit form
    // this.submit();
});


// upload image logic
const insProfileImg = document.querySelector('.profile .instructor-profile-picture-container img');

insImagefileInput.addEventListener('change', function() {
    const file = this.files[0]; // get selected file
    if (file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image (jpg, jpeg, png)');
            this.value = null; // reset file input
            return;
        }

        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSize) {
            alert('Image size must be less than 2MB');
            this.value = null; // reset file input
            return;
        }

        // Create preview
        const reader = new FileReader();
        reader.onload = function(e) {
            insProfileImg.src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});




// ================================  Instructor Profile Change Password Section ==============================================
// Password Strength Suggestion
const oldPassword = document.querySelector(".main .profile #change-password-form .password-change #old-password");
const passwordInput = document.querySelector(".main .profile #change-password-form .password-change #new-password");
const confirmPasswordInput = document.querySelector(".main .profile #change-password-form .password-change #confirm-password");
const strengthIndicator = document.querySelector(".main .profile #change-password-form .password-change #strength-indicator");

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


// Handle save changes
const changePasswordform = document.getElementById("change-password-form");

changePasswordform.addEventListener("submit", (e)=> {
    // Basic validations
    if (oldPassword.value.length === 0 || passwordInput.value.length === 0 || confirmPasswordInput.value.length === 0) {
        alert("All fields are required.");
        e.preventDefault();
        return;
    } 

    if (passwordInput.value !== confirmPasswordInput.value) {
        alert("New password and confirm password do not match!");
        e.preventDefault();
        return;
    }
    
    // Password pattern check (same as registration)
    const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;
    if (!passwordPattern.test(passwordInput.value.trim())) {
        alert("Password must be at least 8 characters, include uppercase, number, and special character.");
        e.preventDefault();
        return;
    }
});

// Profile Show Password
const profileShowPassword = document.querySelectorAll(".main .profile.active .password-change .form-group .toggle-password");

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

const viewStudents = document.querySelectorAll(".view-students");
const viewStudentDiv = document.querySelector(".main .view-batches #view-students-panel");
const viewStudentList = viewStudentDiv.querySelector("#students-list");
const viewStudentCloseBtn = viewStudentDiv.querySelector("#close-students-panel");

if (viewStudents.length > 0) {
    viewStudents.forEach((viewStudent) => {
        viewStudent.addEventListener("click", () => {
            let students = [];
            try {
                students = JSON.parse(viewStudent.dataset.students || "[]");
            } catch (e) {
                console.error("Invalid student JSON", e);
            }

            // update title
            viewStudentDiv.querySelector(".panel-header h2").textContent = 
                `Batch: ${viewStudent.dataset.batchname}`;

            // show panel
            viewStudentDiv.style.display = "flex";
            viewStudentList.innerHTML = "";

            if (students.length > 0) {
                students.forEach(student => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${student.full_name}</td>
                        <td>${student.email}</td>
                        <td>${student.mobile_number}</td>
                    `;
                    viewStudentList.appendChild(row);
                });
            } else {
                viewStudentList.innerHTML = `<tr><td colspan="3">No students found</td></tr>`;
            }
        });
    });

    // close button
    viewStudentCloseBtn.addEventListener("click", () => {
        viewStudentDiv.style.display = "none";
    });
}



// ======================= download time table pdf logic =============
const downTimetablePdfBtn = document.getElementById("downloadTimeTablePDF");
if (downTimetablePdfBtn) {
    document.getElementById("downloadTimeTablePDF").addEventListener("click", () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: "landscape", unit: "pt", format: "a4" });
    
        const table = document.querySelector("#instructor-time-table");
    
        // Prepare headers
        const headers = Array.from(table.querySelectorAll("thead tr th")).map(th => th.innerText);
    
        // Prepare rows
        const data = Array.from(table.querySelectorAll("tbody tr")).map(tr =>
            Array.from(tr.querySelectorAll("td")).map(td => td.innerText)
        );
    
        // Optional: Add week info at top
        const weekText = document.querySelector(".timetable-week")?.innerText || "";
        const insNameText = "Instructor: " + document.querySelector("#instructor-tt-name").value || "";
        doc.setFontSize(12);
        doc.text(insNameText, 40, 30);
        doc.text(weekText, 40, 50);
    
        // AutoTable
        doc.autoTable({
            head: [headers],
            body: data,
            startY: 70,
            theme: "grid",
            styles: { fontSize: 10, cellPadding: 5 },
            headStyles: { fillColor: [23, 94, 166] }, // teal header
            alternateRowStyles: { fillColor: [240, 240, 240] }, // gray stripes
            margin: { left: 40, right: 40 }
        });
    
        doc.save("timetable.pdf");
    });
}
