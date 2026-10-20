
/* 1. Notification Banner */
const notification = document.getElementById("notification");

document.getElementById("closeNotification").addEventListener("click", function () {
    notification.hidden = true;
});

/* 2. Modal Popup */
const modal = document.getElementById("modal");

document.getElementById("openModal").addEventListener("click", function () {
    modal.hidden = false;
});

document.getElementById("closeModal").addEventListener("click", function () {
    modal.hidden = true;
});

modal.addEventListener("click", function (event) {
    if (event.target === modal) {
        modal.hidden = true;
    }
});

/* 3. Content Slider */
const slides = [
    "Study Materials",
    "Upcoming College Events",
    "Student Dashboard",
    "Assignment Management"
];

let currentSlide = 0;

const slideContent = document.getElementById("slideContent");

function showSlide() {
    slideContent.textContent = slides[currentSlide];
}

document.getElementById("nextSlide").addEventListener("click", function () {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide();
});

document.getElementById("previousSlide").addEventListener("click", function () {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    showSlide();
});

/* 4. Collapsible FAQ */
const questions = document.querySelectorAll(".faqQuestion");

questions.forEach(function (question) {
    question.addEventListener("click", function () {
        const answer = question.nextElementSibling;
        const isOpen = question.getAttribute("aria-expanded") === "true";

        answer.hidden = isOpen;
        question.setAttribute("aria-expanded", String(!isOpen));
    });
});

/* 5. Hamburger Menu */
const menuButton = document.getElementById("menuButton");
const navMenu = document.getElementById("navMenu");

menuButton.addEventListener("click", function () {
    navMenu.classList.toggle("show");

    const isOpen = navMenu.classList.contains("show");
    menuButton.setAttribute("aria-expanded", String(isOpen));
});

/* 6. Light/Dark Theme Switcher */
const themeButton = document.getElementById("themeButton");

themeButton.addEventListener("click", function () {
    document.body.classList.toggle("dark");

    if (document.body.classList.contains("dark")) {
        themeButton.textContent = "Light Mode";
    } else {
        themeButton.textContent = "Dark Mode";
    }
});
