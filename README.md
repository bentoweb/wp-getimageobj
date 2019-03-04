# wp-getimageobj
v0.9
WordPress Custom function : generate images from medias and posts

$img_obj = getImageObj($id,$imgW,$imgH,$qual,$defaultimg);

$id : Media image or post ID
$imgW (optionnal) : image width
$imgH (optionnal) : image height
$qual (optionnal) : quality for jpeg (default=90%)
$defaultimg (optionnal) : id for default image if requested not exists (not fully usable yet)