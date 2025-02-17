document.getElementById('reviewForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const name = document.getElementById('name').value;
    const review = document.getElementById('review').value;

    fetch('/api/reviews', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name, review })
    })
    .then(response => response.json())
    .then(data => {
        displayReviews(data);
        document.getElementById('reviewForm').reset();
    })
    .catch(error => console.error('Error:', error));
});

function displayReviews(reviews) {
    const reviewsContainer = document.getElementById('reviews');
    reviewsContainer.innerHTML = '';
    reviews.forEach(r => {
        const reviewDiv = document.createElement('div');
        reviewDiv.classList.add('review');
        reviewDiv.innerHTML = `<strong>${r.name}</strong><p>${r.review}</p>`;
        reviewsContainer.appendChild(reviewDiv);
    });
}

// Fetch existing reviews on page load
window.onload = function() {
    fetch('/api/reviews')
        .then(response => response.json())
        .then(data => displayReviews(data))
        .catch(error => console.error('Error:', error));
};