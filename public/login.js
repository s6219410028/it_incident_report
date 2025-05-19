document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const loginData = {
        username: this.username.value,
        password: this.password.value
    };
    fetch('http://192.168.10.32:8000/api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(loginData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `dashboard.html?staffId=${data.staffId}`;
            } else {
                document.getElementById('loginMessage').textContent = data.message;
            }
        })
        .catch(error => console.error('Error:', error));
});
