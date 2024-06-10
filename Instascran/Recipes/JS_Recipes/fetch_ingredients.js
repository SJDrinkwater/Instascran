// Function to fetch ingredients via fetch API
function fetchIngredients() {
    fetch('../api/AddRecipe/ingredients.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json(); // Parse response as JSON
        })
        .then(data => {
            // Reference to the ingredient list container
            var ingredientListContainer = document.getElementById('ingredient_list');

            // Clear previous content
            ingredientListContainer.innerHTML = '';

            // Loop through each ingredient data and create HTML for each
            data.forEach(function (ingredient) {
                // Create a div element for the ingredient
                var ingredientDiv = document.createElement('div');
                ingredientDiv.className = 'ingredient';
                ingredientDiv.setAttribute('data-product-id', ingredient.id);

                // Create a span element for the product name
                var nameSpan = document.createElement('span');
                nameSpan.textContent = ingredient.name;

                // Create a span element for the measurement
                var measurementSpan = document.createElement('span');
                measurementSpan.className = 'measurement';
                measurementSpan.textContent = '(' + ingredient.measurement + ')';
                measurementSpan.style.display = 'none'; // Hide measurement span initially

                // Append name and measurement spans to the ingredient div
                ingredientDiv.appendChild(nameSpan);
                ingredientDiv.appendChild(measurementSpan);

                // Append the ingredient div to the ingredient list container
                ingredientListContainer.appendChild(ingredientDiv);
            });

            // Add click event listener to ingredients
            var ingredients = document.getElementsByClassName('ingredient');
            for (var i = 0; i < ingredients.length; i++) {
                ingredients[i].addEventListener('click', function () {
                    var productId = this.getAttribute('data-product-id');
                    var productName = this.getElementsByTagName('span')[0].textContent;
                    addProductToList(productName, productId);
                });
            }
        })
        .catch(error => {
            console.error('Error fetching ingredients:', error);
        });
}

// Call fetchIngredients function when the page loads
document.addEventListener('DOMContentLoaded', function () {
    fetchIngredients();
});

// Function to filter ingredients based on search query
function filterIngredients() {
    var searchInput = document.getElementById('ingredient_search');
    var filter = searchInput.value.toUpperCase();
    var ingredients = document.getElementsByClassName('ingredient');

    // Loop through all ingredients and hide those that don't match the search query
    for (var i = 0; i < ingredients.length; i++) {
        var productName = ingredients[i].getElementsByTagName('span')[0].textContent.toUpperCase();
        if (productName.indexOf(filter) > -1) {
            ingredients[i].style.display = '';
        } else {
            ingredients[i].style.display = 'none';
        }
    }
}
document.getElementById('ingredient_search').addEventListener('input', filterIngredients);
