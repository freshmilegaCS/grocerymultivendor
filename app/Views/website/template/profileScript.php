<script>
    document.querySelector('form.updateProfile').addEventListener('submit', async (event) => {
        event.preventDefault();

        const contactFName = document.getElementById('contactFName').value;
        const contactEmailInput = document.getElementById('contactEmail');
        const contactPhoneInput = document.getElementById('contactPhone');

        const contactEmail = contactEmailInput.value.trim();
        const contactPhone = contactPhoneInput.value.trim();

        const messageDiv = document.getElementById('message');

        // Validate email only if it's not readonly (i.e., editable)
        if (!contactEmailInput.readOnly && !/\S+@\S+\.\S+/.test(contactEmail)) {
            document.getElementById("emailError").textContent =
                "Please enter a valid email address.";
            document.getElementById("emailError").classList.remove("hidden");
            contactEmailInput.parentElement.classList.add("border-red-500");
            return;
        }

        // Validate phone only if it's not readonly (i.e., editable)
        if (!contactPhoneInput.readOnly && !/^\d{<?= $country['validation_no'] ?>}$/.test(contactPhone)) {
            document.getElementById("phoneError").textContent =
                "Please enter a valid phone number.";
            document.getElementById("phoneError").classList.remove("hidden");
            contactPhoneInput.parentElement.classList.add("border-red-500");
            return;
        }

        try {
            const response = await fetch('/updateProfile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    contactPhone,
                    contactFName, contactEmail
                }),
            });

            const result = await response.json();

            if (result.status === 'success') {
                console.log(result.message);
                showToast(result.message, "success");
            } else {
                console.log(result.message);
                showToast(result.message, "error");
            }
        } catch (error) {
            messageDiv.innerHTML = `<p class="text-red-500 text-sm">Error: ${error.message}</p>`;
        }
    });


    async function deleteAccount() {
        try {
            const response = await fetch('/deleteAccount', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_email: '<?= $user_email ?>'
                }),
            });

            const result = await response.json();

            // Handle success or error response
            if (result.status === 'success') {
                showToast(result.message, "success");
                location = '/logout';
            } else {
                showToast(result.message, "error");
            }
        } catch (error) {
            document.getElementById('message').innerHTML =
                `<p class="text-red-500 text-sm">Error: ${error.message}</p>`;
        }
    }
</script>

<script>
    document.getElementById('updatePasswordButton').addEventListener('click', function() {
        const password = document.getElementById('contactPassword').value;
        const confirmPassword = document.getElementById('contactConfirempassword').value;
        const passwordError = document.getElementById('passwordError');

        // Reset error message
        passwordError.style.display = 'none';
        passwordError.textContent = '';

        // Client-side validation
        if (password.length < 6) {
            passwordError.textContent = 'Password must be at least 6 characters.';
            passwordError.style.display = 'block';
            return;
        }

        if (password !== confirmPassword) {
            passwordError.textContent = 'Passwords do not match.';
            passwordError.style.display = 'block';
            return;
        }

        // Fetch API call to update the password
        fetch('/changePassword', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    password: password
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('Password updated successfully', "success");

                } else {
                    passwordError.textContent = data.message || 'An error occurred while updating the password.';
                    passwordError.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                passwordError.textContent = 'An unexpected error occurred. Please try again later.';
                passwordError.style.display = 'block';
            });
    });
</script>