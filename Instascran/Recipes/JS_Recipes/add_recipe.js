var selectedIngredients = [];

function fetchIngredients() {
    fetch('../api/AddRecipe/ingredients.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            var ingredientListContainer = document.getElementById('ingredient_list');
            ingredientListContainer.innerHTML = '';

            data.forEach(function (ingredient) {
                var ingredientDiv = document.createElement('div');
                ingredientDiv.className = 'ingredient';
                ingredientDiv.setAttribute('data-product-id', ingredient.product_id);

                var nameSpan = document.createElement('span');
                nameSpan.textContent = ingredient.product_name;

                var measurementSpan = document.createElement('span');
                measurementSpan.className = 'measurement';
                measurementSpan.textContent = '(' + ingredient.product_measurement + ')';

                ingredientDiv.appendChild(nameSpan);
                ingredientDiv.appendChild(measurementSpan);

                ingredientListContainer.appendChild(ingredientDiv);
            });

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

document.addEventListener('DOMContentLoaded', function () {
    fetchIngredients();
});

function filterIngredients() {
    var searchInput = document.getElementById('ingredient_search');
    var filter = searchInput.value.toUpperCase();
    var ingredients = document.getElementsByClassName('ingredient');

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

function removeProduct(productId) {
    var index = selectedIngredients.findIndex(function (ingredient) {
        return ingredient.productId === productId;
    });

    if (index !== -1) {
        selectedIngredients.splice(index, 1);
        var productElement = document.querySelector('.added-product[data-product-id="' + productId + '"]');
        productElement.remove();
        updateSelectedIngredients();
        updateRecipePreview();
    }
}

function addProductToList(productName, productId) {
    var existingProduct = selectedIngredients.find(function (ingredient) {
        return ingredient.productId === productId;
    });

    if (existingProduct) {
        alert('The product "' + productName + '" is already in the list.');
        var quantityInput = document.querySelector('.quantity-input[data-product-id="' + productId + '"]');
        quantityInput.focus();
    } else {
        var addedProducts = document.getElementById('added_products');
        var productItem = document.createElement('div');
        productItem.className = 'added-product';
        productItem.setAttribute('data-product-id', productId);
        productItem.textContent = productName;

        var removeIcon = document.createElement('i');
        removeIcon.className = 'fas fa-trash-alt remove-icon';
        removeIcon.addEventListener('click', function () {
            removeProduct(productId);
        });

        productItem.appendChild(removeIcon);

        var quantityInput = document.createElement('input');
        quantityInput.type = 'number';
        quantityInput.min = 1;
        quantityInput.value = 1;
        quantityInput.className = 'quantity-input';
        quantityInput.setAttribute('data-product-id', productId);
        quantityInput.addEventListener('input', function () {
            var productId = this.getAttribute('data-product-id');
            var quantity = parseInt(this.value);

            selectedIngredients.forEach(function (ingredient) {
                if (ingredient.productId === productId) {
                    ingredient.quantity = quantity;
                }
            });
            updateSelectedIngredients();
        });
        productItem.appendChild(quantityInput);

        addedProducts.appendChild(productItem);

        selectedIngredients.push({
            productId: productId,
            quantity: parseInt(quantityInput.value)
        });
        updateSelectedIngredients();
    }
}

function updateSelectedIngredients() {
    var selectedIngredientsContainer = document.getElementById('selected_ingredients');
    selectedIngredientsContainer.innerHTML = '';

    selectedIngredients.forEach(function (ingredient) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ingredients[]';
        input.value = ingredient.productId;
        selectedIngredientsContainer.appendChild(input);

        var quantityInput = document.createElement('input');
        quantityInput.type = 'hidden';
        quantityInput.name = 'quantities[]';
        quantityInput.value = ingredient.quantity;
        selectedIngredientsContainer.appendChild(quantityInput);
    });

    updateRecipePreview();
}

       
   // Function to update the recipe preview
   function updateRecipePreview() {
       // Get form inputs
       var recipeName = document.getElementById('recipe_name').value;
       var recipeImg = document.getElementById('recipe_img').value;
       var recipeKcal = document.getElementById('recipe_kcal').value;
       var mealType = document.getElementById('meal_type').value;
       var instructions = document.getElementById('instructions').value;
       var selectedIngredientsContainer = document.getElementById('selected_ingredients');
       var ingredients = [];
   
       // Loop through selected ingredients and retrieve their names, quantities, and measurements
       for (var i = 0; i < selectedIngredientsContainer.children.length; i += 2) {
           var productId = selectedIngredientsContainer.children[i].value;
           var ingredientElement = document.querySelector('.ingredient[data-product-id="' + productId + '"]');
           if (!ingredientElement) continue; 
           var ingredientName = ingredientElement.textContent.trim();
           var measurement = ingredientElement.querySelector('.measurement').textContent;
           var quantity = selectedIngredientsContainer.children[i + 1].value;
           ingredients.push(quantity + ' ' + ingredientName + ' '); 
       }
   
       // Update recipe preview elements with form inputs
       document.getElementById('preview_recipe_name').textContent = 'Name: ' + recipeName;
       document.getElementById('preview_recipe_img').innerHTML = '<img src="' + recipeImg + '" style="width: 250px; height: auto;">';
       document.getElementById('preview_recipe_kcal').textContent = 'Kcal: ' + recipeKcal;
       document.getElementById('preview_meal_type').textContent = 'Meal Type: ' + mealType;
       document.getElementById('preview_instructions').textContent = 'Instructions: ' + instructions;
       document.getElementById('preview_ingredients').innerHTML = '<ul><li>' + ingredients.join('</li><li>') + '</li></ul>';
   }
   
   
       // Listen for input events on form fields to update recipe preview
       document.getElementById('recipe_name').addEventListener('input', updateRecipePreview);
       document.getElementById('recipe_img').addEventListener('input', updateRecipePreview);
       document.getElementById('recipe_kcal').addEventListener('input', updateRecipePreview);
       document.getElementById('meal_type').addEventListener('input', updateRecipePreview);
       document.getElementById('instructions').addEventListener('input', updateRecipePreview);
       document.getElementById('ingredient_search').addEventListener('input', updateRecipePreview);
   
   