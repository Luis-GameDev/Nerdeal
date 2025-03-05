document.querySelector("form").addEventListener("submit", function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (password !== confirmPassword) {
        e.preventDefault();  
        alert("Die Passwörter stimmen nicht überein.");
    }
});
