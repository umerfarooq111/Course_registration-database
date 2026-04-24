const loginTab = document.getElementById('login-tab');
const registerTab = document.getElementById('register-tab');
const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const messageDiv = document.getElementById('message');

loginTab.addEventListener('click', () => switchTab('login'));
registerTab.addEventListener('click', () => switchTab('register'));

loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    await handleAuth('login', { email, password });
});

registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = document.getElementById('register-name').value;
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;
    await handleAuth('register', { name, email, password });
});

async function handleAuth(type, data) {
    try {
        const response = await fetch(`php/auth/${type}.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (response.ok) {
            showMessage(result.message || 'Success', 'success');
            if (type === 'login') window.location.href = 'dashboard.html';
        } else {
            showMessage(result.error || 'Error', 'error');
        }
    } catch (err) {
        showMessage('Network error', 'error');
    }
}

function switchTab(tab) {
    if (tab === 'login') {
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
        loginForm.classList.add('active');
        registerForm.classList.remove('active');
    } else {
        registerTab.classList.add('active');
        loginTab.classList.remove('active');
        registerForm.classList.add('active');
        loginForm.classList.remove('active');
    }
    messageDiv.textContent = '';
}

function showMessage(msg, type) {
    messageDiv.textContent = msg;
    messageDiv.className = type;
}
