function confirmDelete(recipeId) {
    if (confirm("Are you sure you want to delete this recipe?")) {
        deleteRecipe(recipeId);
    }
}

// Function to delete a recipe
function deleteRecipe(recipeId) {
    fetch('../api/MyRecipes/my_recipes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            recipe_id: recipeId
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(`Error: ${data.error}`);
        } else {
            alert("Recipe deleted successfully!");
            fetchRecipes(); // Refresh recipes list
        }
    })
    .catch(error => {
        console.error('Error deleting recipe:', error);
        alert("Error deleting recipe. Please try again later.");
    });
}