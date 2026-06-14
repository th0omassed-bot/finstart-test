const menuBtn = document.getElementById("menuBtn");
const nav = document.getElementById("nav");

if (menuBtn && nav) {
  menuBtn.addEventListener("click", () => {
    nav.classList.toggle("open");
  });

  const navLinks = nav.querySelectorAll("a");

  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      nav.classList.remove("open");
    });
  });
}

const params = new URLSearchParams(window.location.search);
const successAlert = document.getElementById("successAlert");

if (params.get("success") === "1" && successAlert) {
  successAlert.classList.add("show");
}

const quizDrawer = document.getElementById("quizDrawer");
const quizOverlay = document.getElementById("quizDrawerOverlay");
const quizOpenButtons = document.querySelectorAll("[data-open-quiz]");
const quizCloseButtons = document.querySelectorAll("[data-close-quiz]");

function openQuizDrawer() {
  if (!quizDrawer || !quizOverlay) return;
  quizDrawer.classList.add("open");
  quizOverlay.classList.add("open");
  document.body.classList.add("no-scroll");
}

function closeQuizDrawer() {
  if (!quizDrawer || !quizOverlay) return;
  quizDrawer.classList.remove("open");
  quizOverlay.classList.remove("open");
  document.body.classList.remove("no-scroll");
}

quizOpenButtons.forEach((button) => {
  button.addEventListener("click", openQuizDrawer);
});

quizCloseButtons.forEach((button) => {
  button.addEventListener("click", closeQuizDrawer);
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeQuizDrawer();
  }
});


const revealElements = document.querySelectorAll(".reveal");

if ("IntersectionObserver" in window && revealElements.length > 0) {
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("is-visible");
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  revealElements.forEach((element) => revealObserver.observe(element));
} else {
  revealElements.forEach((element) => element.classList.add("is-visible"));
}
