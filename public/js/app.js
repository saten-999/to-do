document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
                event.preventDefault();
                return;
            }

            // Forms with an API endpoint are submitted via fetch() instead of a normal page post.
            if (form.dataset.apiUrl) {
                event.preventDefault();
                submitViaApi(form);
                return;
            }

            // Simple loading state: disable the submit button so it can't be clicked twice.
            var submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                window.setTimeout(function () {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Please wait...';
                }, 0);
            }
        });
    });
});

function submitViaApi(form) {
    var submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
    }

    fetch(form.dataset.apiUrl, {
        method: form.dataset.apiMethod || 'POST',
        headers: { Accept: 'application/json' },
    })
        .then(function (response) {
            if (!response.ok) {
                return response.json().then(function (body) {
                    throw new Error(body.message || 'Request failed with status ' + response.status);
                });
            }

            if (form.dataset.apiRedirect) {
                window.location.href = form.dataset.apiRedirect;
            } else {
                window.location.reload();
            }
        })
        .catch(function (error) {
            window.alert(error.message);
            if (submitButton) {
                submitButton.disabled = false;
            }
        });
}
