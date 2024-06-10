<?php
// Directory where images are stored
$imageDirectory = "../../assets/profile_icons/";

// Get all files from the directory
$images = scandir($imageDirectory);

// Calculate the number of images per column
$imagesPerColumn = 2;

// Display images in four columns
for ($col = 0; $col <= 4; $col++) {
    echo '<div class="column">';
    // Display images for the current column
    for ($i = $col * $imagesPerColumn; $i < min(($col + 1) * $imagesPerColumn, count($images)); $i++) {
        if ($images[$i] != "." && $images[$i] != "..") {
            $imageUrl = 'assets/profile_icons/'.$images[$i]; // Construct the image URL
            echo '<label><input type="radio" name="user_icon" value="' . '../'.$imageUrl . '">';
            echo '<img src="' . $imageUrl . '" alt="' . $images[$i] . '" onclick="selectImage(this)"></label>';
        }
    }
    echo '</div>';
}

?>
