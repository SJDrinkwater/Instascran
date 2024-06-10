// Function to display recipe details
function displayRecipeDetails(recipeId) {
    fetch(`../api/RecipeDetails/recipe_details.php?recipe_id=${recipeId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error fetching recipe details: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'error') {
                if (data.message === 'User not authenticated.' || data.message === 'Invalid user session.') {
                    // Redirect to logout.php if the user is not authenticated
                    window.location.href = '../users/logout.php';
                } else {
                    const errorMessage = document.createElement('p');
                    errorMessage.textContent = data.message;
                    recipeContainer.appendChild(errorMessage);
                }
                return;
            }
        
            const recipeNameElement = document.getElementById('recipe-name');
            const recipeImageElement = document.getElementById('recipe-image');
            const instructionsElement = document.getElementById('instructions');
            const createdByElement = document.getElementById('created-by');
            const userIconElement = document.getElementById('user-icon');
            const ratingsElement = document.getElementById('recipe-ratings');
            const ingredientsList = document.getElementById('ingredients-list');
            const commentsList = document.getElementById('comments-list');

            // Check if data indicates no recipe found
            if (typeof data === 'string' && data.startsWith("No recipe found")) {
                const noRecipeMessage = document.createElement('p');
                noRecipeMessage.textContent = data;
                noRecipeMessage.classList.add('error');
                recipeNameElement.appendChild(noRecipeMessage);
                return;
            }

            recipeNameElement.textContent = data.recipe_name;
            recipeImageElement.src = data.recipe_img;
            instructionsElement.textContent = data.instructions;
            createdByElement.textContent = "Created by: " + data.user_name;
            userIconElement.src = data.user_icon;

            // Display rating
            const ratingSection = document.createElement('div');
            ratingSection.classList.add('rating');
            const filledStar = '<i class="fas fa-star filled"></i>';
            const emptyStar = '<i class="fas fa-star"></i>';
            for (let i = 1; i <= 5; i++) {
                if (i <= data.average_rating) {
                    ratingSection.innerHTML += filledStar;
                } else {
                    ratingSection.innerHTML += emptyStar;
                }
            }
            recipeNameElement.appendChild(ratingSection);

            // Populate ingredients
            data.ingredients.forEach(ingredient => {
                const ingredientDiv = document.createElement('div');
                ingredientDiv.classList.add('ingredient');
                ingredientDiv.textContent = `${ingredient.product_name}: ${ingredient.n_ingredients} ${ingredient.product_measurement}`;
                ingredientsList.appendChild(ingredientDiv);
            });
            
            // Populate comments and check ownership
            data.comments.forEach(comment => {
                const commentDiv = document.createElement('div');
                commentDiv.classList.add('comment');
                commentDiv.dataset.commentId = comment.comment_id; 
                commentDiv.innerHTML = `
                    <div class="comment-content">
                        <p class="comment-user">${comment.user_name}</p>
                        <p class="comment-time">${comment.created_at}</p>
                    </div>
                    <p class="comment-text">${comment.comment}</p>
                `;
                commentsList.appendChild(commentDiv);
                checkCommentOwnership(comment.comment_id); // Check and add delete button if then comment is owned
            });

        })
        .catch(error => {
            const recipeDetailsContainer = document.getElementById('recipe-details-container');
            const errorMessage = document.createElement('p');
            errorMessage.textContent = error.message; 
            errorMessage.classList.add('error');
            recipeDetailsContainer.appendChild(errorMessage);
        });
}

function checkCommentOwnership(commentId) {
    // Retrieve token session
    const token = "<?php echo isset($_SESSION['token']) ? $_SESSION['token'] : ''; ?>"; 
    fetch(`../api/RecipeDetails/check_comment.php?comment_id=${commentId}&token=${token}`)
        .then(response => response.json())
        .then(isOwner => {
            if (isOwner) {
                // If the user owns the comment, add delete button
                const commentDiv = document.querySelector(`.comment[data-comment-id="${commentId}"]`);
                const deleteButton = document.createElement('button');
                deleteButton.classList.add('delete-button');
                deleteButton.innerHTML = '<i class="fas fa-trash-alt"></i>';
                deleteButton.onclick = () => deleteComment(commentId); // Call deleteComment function when clicked
                commentDiv.appendChild(deleteButton);
            }
        });
}

// Function to delete comment
function deleteComment(commentId) {
    // Send a POST request to delete_comment.php
    fetch('../api/RecipeDetails/delete_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `delete_comment=true&comment_id=${commentId}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to delete comment');
        }
        return response.text();
    })
    .then(data => {
        console.log(data); // Log the response message
        const commentDiv = document.querySelector(`.comment[data-comment-id="${commentId}"]`);
        if (commentDiv) {
            commentDiv.remove(); // Remove the deleted comment from the DOM
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Handle errors 
    });
}
    // Function to submit a new comment
function submitComment(event, recipeId) {
    event.preventDefault(); // Prevent the default form submission
    const commentTextarea = document.getElementById('comment');
    const comment = commentTextarea.value.trim(); // Trim leading and trailing whitespace

    if (comment === '') {
        alert('Please enter a comment.'); // Display an alert if the comment is empty
        return;
    }

    // Send a POST request to add_comment.php
    fetch('../api/RecipeDetails/recipe_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `submit_comment=true&recipe_id=${recipeId}&comment=${encodeURIComponent(comment)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to submit comment');
        }
        return response.text();
    })
    .then(data => {
        console.log(data); // Log the response message
        
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        // Handle errors
    });
}

function addRating(event, recipeId) {
    event.preventDefault(); // Prevent the default form submission
    const ratingSelect = document.getElementById('rating');
    const rating = ratingSelect.value; // This gets the selected value from the dropdown


    // Send a POST request to add_rating.php
    fetch('../api/RecipeDetails/add_rating.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `submit_rating=true&recipe_id=${recipeId}&rating=${encodeURIComponent(rating)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to submit rating');
        }
        return response.text();
    })
    .then(data => {
        console.log(data); // Log the response message
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        // Handle errors 
    });
}


const urlParams = new URLSearchParams(window.location.search);
const recipeId = urlParams.get('recipe_id');

if (recipeId) {
    displayRecipeDetails(recipeId);
} else {
    const recipeNameElement = document.getElementById('recipe-name');
    const noRecipeMessage = document.createElement('p');
    noRecipeMessage.textContent = "No recipe ID provided.";
    noRecipeMessage.classList.add('error');
    recipeNameElement.appendChild(noRecipeMessage);
}
