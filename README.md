# wp-getimageobj
## v0.9.3
## WordPress Custom function : generate images from medias and posts

`$img_obj = getImageObj($id,$imgW,$imgH,$qual,$defaultimg);`

* $id : Media image ID or post ID
* $imgW (optionnal) : image width (px)
* $imgH (optionnal) : image height (px)
* $qual (optionnal) : % quality for jpeg (default=90)
* $defaultimg (optionnal) : false, or id for default image if the requested not exists, or true for random default img