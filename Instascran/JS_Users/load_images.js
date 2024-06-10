   // Function to fetch image options and insert into the image container
   function loadImages() {
    fetch('api/users/get_image_icons.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('image-container').innerHTML = data;
        })
        .catch(error => console.error('Error fetching images:', error));
}

// Call the function to load images when the page is loaded
window.addEventListener('DOMContentLoaded', loadImages);