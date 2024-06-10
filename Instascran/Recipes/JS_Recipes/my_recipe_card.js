function fetchRecipes() {
    const recipeContainer = document.getElementById('recipeContainer');
    const loadingMessage = document.createElement('p');
    loadingMessage.textContent = "Loading recipes...";
    recipeContainer.appendChild(loadingMessage); // Show loading message

    fetch('../api/MyRecipes/my_recipes.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        recipeContainer.innerHTML = ''; // Clear previous recipes and loading message

        if (data.status === 'error') {
            if (data.message === 'User not authenticated.' || data.message === 'Invalid user session.') {
                // Display message indicating not logged in
                const notLoggedInMessage = document.createElement('p');
                notLoggedInMessage.textContent = "You are not logged in.";
                recipeContainer.appendChild(notLoggedInMessage);
            } else {
                const errorMessage = document.createElement('p');
                errorMessage.textContent = data.message;
                recipeContainer.appendChild(errorMessage);
            }
            return;
        }
        
        if (data.recipes.length > 0) {
            data.recipes.forEach(recipe => {
                const recipeCard = document.createElement('div');
                recipeCard.classList.add('recipe-card');

                // Construct HTML for recipe card using recipe data
                recipeCard.innerHTML = `
                    <div class="delete-icon" onclick="confirmDelete(${recipe.recipe_id})">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <a href="recipe_details.html?recipe_id=${recipe.recipe_id}" style="text-decoration: none; color: inherit;">
                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                            <img src="${recipe.user_icon}" alt="${recipe.user_name}" class="user-icon" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;">
                            <div>
                                <p style="margin: 0;">${recipe.user_name}</p>
                                <p style="margin: 0; font-size: 12px; color: #999;">${recipe.created_at}</p>
                            </div>
                        </div>
                        <p class="recipe-name">${recipe.recipe_name}</p>
                        <p class="recipe-kcal">${recipe.recipe_kcal} Kcal</p>
                        <img src="${recipe.recipe_img}" alt="${recipe.recipe_name}" class="recipe-img">
                        <div class="comments-section">
                            <!-- Comments will be dynamically added here -->
                        </div>
                        <div class="rating">
                            <!-- Ratings will be dynamically added here -->
                        </div>
                    </a>
                `;
                // Add comments
                const commentsSection = recipeCard.querySelector('.comments-section');
                if (recipe.comments.length > 0) {
                    recipe.comments.forEach(comment => {
                        const commentDiv = document.createElement('div');
                        commentDiv.classList.add('comment');
                        commentDiv.innerHTML = `
                            <div class="comment-content">
                                <p class="comment-user">${comment.user_name}</p>
                                <p class="comment-time">${comment.created_at}</p>
                            </div>
                            <p class="comment-text">${comment.comment}</p>
                        `;
                        commentsSection.appendChild(commentDiv);
                    });
                } else {
                    commentsSection.innerHTML = '<p class="no-comments">No comments yet.</p>';
                }

                // Add ratings
                const ratingSection = recipeCard.querySelector('.rating');
                const filledStar = '<i class="fas fa-star filled"></i>';
                const emptyStar = '<i class="fas fa-star"></i>';
                for (let i = 1; i <= 5; i++) {
                    if (i <= recipe.average_rating) {
                        ratingSection.innerHTML += filledStar;
                    } else {
                        ratingSection.innerHTML += emptyStar;
                    }
                }

                // Append recipe card to recipe container
                recipeContainer.appendChild(recipeCard);
            });
        } else { // Check if no recipes were found
            const noRecipesMessage = document.createElement('p');
            noRecipesMessage.textContent = "No recipes found."; // Set the text content
            recipeContainer.appendChild(noRecipesMessage);
        }
    })
    .catch(error => {
        recipeContainer.innerHTML = ''; // Clear previous content
        const errorMessage = document.createElement('p');
        errorMessage.textContent = "Error fetching recipes. Please try again later.";
        recipeContainer.appendChild(errorMessage);
        console.error('Error fetching recipes:', error);
    });
}

fetchRecipes();