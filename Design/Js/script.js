const revealElements = document.querySelectorAll(".fade-down, .fade-up");

const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            entry.target.classList.add("show");
        }
    });
});

revealElements.forEach((el) => observer.observe(el));

window.addEventListener("scroll", () => {
    const scrolled = window.scrollY;

    const hero = document.querySelector(".hero");
    const navbarBG = document.querySelector(".navbar-wrapper");

    if (hero) hero.style.backgroundPositionY = `${scrolled * 0.35}px`;

    if (navbarBG) navbarBG.style.backgroundPositionY = `${scrolled * 0.25}px`;
});

const form = document.querySelector("form");
if (form) {
    form.addEventListener("submit", (e) => {
        const inputs = form.querySelectorAll("input");
        let valid = true;

        inputs.forEach((input) => {
            if (input.value.trim() === "") valid = false;
        });

        if (!valid) {
            e.preventDefault();

            form.classList.add("shake");

            setTimeout(() => {
                form.classList.remove("shake");
            }, 500);
        }
    });
}


window.addEventListener("load", () => {
    document.body.classList.add("page-loaded");
});
