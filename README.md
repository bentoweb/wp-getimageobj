# wp-getimageobj
## v0.9.3
## WordPress Custom function : generate images from medias and posts

`$img_obj = getImageObj($id,$imgW,$imgH,$qual,$defaultimg);`

* $id : Media image ID or post ID
* $imgW (optionnal) : image width (px)
* $imgH (optionnal) : image height (px)
* $qual (optionnal) : % quality for jpeg (default=90)
* $defaultimg (optionnal) : false, or id for default image if the requested not exists, or true for random default img
* Replace Tinify_API_KEY with your key from https://tinypng.com/ (optionnal)

### PHP object returned

* $img_obj is a default WP post type from medias ($img_obj->ID, $img_obj->post_title, etc.)
* $post->src for created img src (you can use post_title for alt)

### Generated files

A folder 'images' is created in the web root if not exists, for all generated images. 