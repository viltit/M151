<?php

    /* 
    Class to handle file uploads.
    */
    class ImageUploader {
        private $url;

        /* 
        Constructor takes the $_FILE-Array and handles all validations.
        It accepts .jpg and .png files
        (TODO: Add parameter with an array of file types to accepts)

        -parameter $name: The file name we want to give this image
        */
        public function __construct(String $name, String $relPath) {
            if ($_FILES['image']['error'] != 0) {
                $error = $_FILES['image']['error'] != 0;
                if ($error == 2) {
                    $message = "Your image exceeds the maximum size.";
                }
                else {
                    $message = "Error while uploading file.";
                }
                throw new InvalidArgumentException($message);
            }
            
            //check if we have a jpeg or png
            $type = exif_imagetype($_FILES['image']['tmp_name']);
            if ($type != IMAGETYPE_JPEG && $type != IMAGETYPE_PNG) {
                throw new InvalidArgumentException(
                    "The uploaded file is must be a jpg or a png."
                );
            }

            //check if image size is at least 100px in width and 50px in height
            list($whith, $height, $type, $attr) = getimagesize($_FILES['userfile']['tmp_name']);
            if ($width < 100 || $height < 50) {
                //TODO: Custom error ?
                throw new InvalidArgumentException("Image must have a width of at least 100px and a height of at least 50px.");    
            }

            //name the file 
            move_uploaded_file($_FILES['image']['tmp_name'], $relPath.$name);
        }

    }

?>