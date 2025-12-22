<?php
// Run this file once to create a default avatar
$width = 200;
$height = 200;

$image = imagecreatetruecolor($width, $height);

// Colors
$bg = imagecolorallocate($image, 99, 102, 241); // Primary color
$text = imagecolorallocate($image, 255, 255, 255); // White

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg);

// Add text
$text_content = '👤';
$font_size = 80;
imagettftext($image, $font_size, 0, 60, 140, $text, __DIR__ . '/arial.ttf', $text_content);

// Save
imagepng($image, __DIR__ . '/default-avatar.png');
imagedestroy($image);

echo "Default avatar created! ";
?>