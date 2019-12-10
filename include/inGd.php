<?php
header("Content-type: image/png");

// Define variables
$Values=array(50,90,30,155,50,40,320,50,40,86,240,128,650,540,320);
$imgWidth=500;
$imgHeight=200;
$grid=25;
$graphspacing=0.05;

//Creation of new array with hight adjusted values
while (list($key, $val) = each($Values)) 
    {if($val>$max){$max=$val;}}

for ($i=0; $i<count($Values); $i++){
$graphValues[$i] = $Values[$i] * (($imgHeight*(1-$graphspacing))/$max);
}
// Create image and define colors

$image=imagecreate($imgWidth, $imgHeight);
$colorWhite=imagecolorallocate($image, 255, 255, 255);
$colorGrey=imagecolorallocate($image, 192, 192, 192);
$colorBlue=imagecolorallocate($image, 0, 0, 255);

// Create border around image
imageline($image, 0, 0, 0, $imgHeight, $colorGrey);
imageline($image, 0, 0, $imgWidth, 0, $colorGrey);
imageline($image, $imgWidth-1, 0, $imgWidth-1, $imgHeight-1, $colorGrey);
imageline($image, 0, $imgHeight-1, $imgWidth-1, $imgHeight-1, $colorGrey);

// Create grid
for ($i=1; $i<($imgWidth/$grid); $i++)
    {imageline($image, $i*$grid, 0, $i*$grid, $imgHeight, $colorGrey);}
for ($i=1; $i<($imgHeight/$grid); $i++)
    {imageline($image, 0, $i*$grid, $imgWidth, $i*$grid, $colorGrey);}

// Create line graph
if($imgWidth/$grid>count($graphValues)){$space=$grid;}
else{$space = $imgWidth/(count($graphValues)-1);}

for ($i=0; $i<count($graphValues)-1; $i++)
    {imageline($image, $i*$space, ($imgHeight-$graphValues[$i]), ($i+1)*$space, ($imgHeight-$graphValues[$i+1]), $colorBlue);}

// Output graph and clear image from memory
imagepng($image);
imagedestroy($image);
?>