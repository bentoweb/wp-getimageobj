# wp-getimageobj
## v0.9.3
## WordPress Custom function : generate images from medias and posts

`$img_obj = getImageObj($id,$imgW,$imgH,$qual,$defaultimg);`

* $id : Media image ID or post ID
* $imgW (optionnal) : image width in px (0 for auto)
* $imgH (optionnal) : image height in px (0 for auto)
* $qual (optionnal) : % quality for jpeg (default=90)
* $defaultimg (optionnal) : false, or id for default image if the requested not exists, or true for random default img
* Replace Tinify_API_KEY with your key from https://tinypng.com/ (optionnal)

### PHP object returned

* $img_obj is a default WP post type from medias ($img_obj->ID, $img_obj->post_title, etc.)
* $post->src for created img src (you can use post_title for alt)

### Generated files

* A folder 'images' is created in the web root if not exists, for all generated images. 
* SEO : The media title is used for the file name

### Example

I have a image in the media library. 800x600px. PNG. Title : "This is Post Title of the media !". ID 26, featured image of a post with ID 12.

`$img_obj = getImageObj(12,0,300,75,18);`

I will find in ./images a file named "this-is-post-title-of-the-media-0-300-75-18-o.png". 300px height, 400px width.
"-o" if I have a valid Tinify_API_KEY

I can write `<img src="<?php echo $img_obj->src ?>" alt="<?php echo $img_obj->post_title ?>">`

