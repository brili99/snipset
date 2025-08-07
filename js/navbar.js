fetch('/components/navbar.html')
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load navbar');
        }
        return response.text();
    })
    .then(html => {
        document.body.insertAdjacentHTML('afterbegin', html);
    })
    .catch(error => {
        console.error('Error loading navbar:', error);
    });
