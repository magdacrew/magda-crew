document.addEventListener("DOMContentLoaded", () => {
    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.getElementById("sidebar");

    if(menuToggle && sidebar) {
        menuToggle.addEventListener("click", () => {
            if (sidebar.style.width === "0px" || sidebar.style.width === "") {
                sidebar.style.width = "250px";
                sidebar.style.overflow = "visible";
            } else {
                sidebar.style.width = "0px";
                sidebar.style.overflow = "hidden";
            }
        });
    }
});