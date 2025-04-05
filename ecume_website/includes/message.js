document.querySelectorAll(".approve, .reject").forEach(button => {
    button.addEventListener("click", function () {
        let userId = this.dataset.userId;
        let status = this.classList.contains("approve") ? "approve" : "reject";

        fetch("approve.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `user_id=${userId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Refresh the page or update the UI dynamically
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    });
});
