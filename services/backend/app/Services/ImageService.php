<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 11/10/16
 * Time: 1:48 PM
 */

namespace app\Services;

class ImageService
{
    public function getImageBackgroundColor($imageFile)
    {
        $image = $this->createImageFromFile($imageFile);
        $width = imagesx($image);
        $height = imagesy($image);

        $level = 32;
        $size = 4;
        $new = imagecreate($size, $size);
        imagecopyresampled($new, $image, 0, 0, 0, 0, $size, $size, $width, $height);
        $w = imagesx($new);
        $h = imagesy($new);
        $color = ["r" => 0, "g" => 0, "b" => 0];
        $multi = 0;
        foreach ([0, $w-1] as $i) {
            foreach ([0, $h-1] as $j) {
                $multi ++;
                $meanColor = imagecolorat($new, $i, $j);
                $c = imagecolorsforindex($new, $meanColor);
                $color["r"] += $c["red"];
                $color["g"] += $c["green"];
                $color["b"] += $c["blue"];
            }
        }
        foreach ($color as &$value) {
            $value = floor($value / $multi);
        }
        $greater = max($color["r"], $color["g"], $color["b"], 1);
        $rates = [$color["r"] / $greater, $color["g"] / $greater, $color["b"] / $greater];
        $darker = [
            "r" => $level * $rates[0],
            "g" => $level * $rates[1],
            "b" => $level * $rates[2]
        ];
        return $this->getImageColorHex($darker);
    }

    private function createImageFromFile($filename)
    {
        switch (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return \imagecreatefromjpeg($filename);

            case 'png':
                return \imagecreatefrompng($filename);

            case 'gif':
                return \imagecreatefromgif($filename);

            default:
                throw new \InvalidArgumentException('File "'.$filename.'" is not valid jpg, png or gif image.');
        }
    }

    private function getImageColorHex($array)
    {
        return sprintf("#%02x%02x%02x", $array["r"], $array["g"], $array["b"]);
    }
}
