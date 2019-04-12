<?php 
/*
(c) Benoit Cyrulik
2019 - MIT License
https://github.com/bentoweb/wp-getimageobj
v0.9.3
*/


/*
COMPOSER :
  "repositories": [
    {
      "type": "composer",
      "url": "https://packagist.org"
    }
  ],
  "require": {
    "tinify/tinify": "^1.5.2"
  },
*/

try {
  \Tinify\setKey('Tinify_API_KEY');
} catch() {
  //-
}


//----------------- Crée ou récupère une image formatée à la demande
/*
    Exemple :
    $monImageUrl = genImageFromPost(7,800,600,60,true);
    Retourne l'url d'une image (générée si besoin) à partir du média dont l'ID est 7, ou de l'image à la une du post ayant l'ID 7, en 800px de largeur, 600px de largeur (croppée si besoin), avec une qualité jpg de 60%. Si l'identifiant demandé n'existe pas (ou étant un post n'ayant pas d'image à la une), une image par défaut provenant de picsum.photos est utilisée.
    Exemples :
    Si l'image à la une du post 7 est le fichier "Mon_Image.jpg" ayant l'ID 35,
    le fichier généré sera "/images/mon-image-800-600-60-35.jpg".
    Par contre, si dans Médias le titre de l'image est "Mon Titre SEO",
    le nom du fichier sera "mon-titre-seo-800-600-60-35.jpg".
    Si aucune taille n'est précisée, l'image sera sortie à sa taille d'origine.

    $idPost : ID du post ayant l"image à la une
    $idImage : ID de l'image à produire
    $imgW : Largeur contrainte (laisser 0 pour ne pas contraindre)
    $imgH : Hauteur contrainte (laisser 0 pour ne pas contraindre)
    $qual : Qualité de compression JPG
    $default : Si l'ID n'est pas un média ou si c'est un post n'ayant pas d'image à la une :
    - false : retourne false.
    - true : Retourne une image aléatoire depuis picsum.photos
    - [id de média] : retourne le média spécifié s'il existe, ou false.

*/



function getImageObj($idPost,$imgW=0,$imgH=0,$qual=90,$default=false) {
  /*
      Si c'est une image, retourne un objet post media + l'url d'une image générée dans '->scr'
      Si c'est un post avec thumbnail, retourne la même chose pour le thumbnail
      Si c'est un SVG, retourne un objet post media + la source du svg dans '->scr'
  */
  $thispost = get_post($idPost);
  // pr($thispost);
  if (is_object($thispost)) {
    if ($thispost->post_type=='attachment') {
      $thispost->src = genImage($thispost->ID,$imgW,$imgH,$qual,$default);
      return $thispost;
    } else {
      $post_thumbnail_id = get_post_thumbnail_id($idPost);
      if (!empty($post_thumbnail_id)) {
        $imgpost = get_post($post_thumbnail_id);
        $imgpost->src = genImage($imgpost->ID,$imgW,$imgH,$qual,$default);
        return $imgpost;
      } else {
        if ($default==false) {
          return false;
        } else {
          if (is_int($default)) {
            $imgpost = get_post($default);
            if (is_object($imgpost)) {
              $imgpost->src = genImage($imgpost->ID,$imgW,$imgH,$qual,$default);
              return $imgpost;
            } else {
              return false;
            }
          } else {
            $imgpost = get_post($post_thumbnail_id);
            $imgpost->src = picsum($imgW,$imgH);
          }
          return $imgpost;
        }
      }
    }
  } else {
    // Not an object
    return false;
  }
}



function genImage($idImage,$imgW=0,$imgH=0,$qual=90,$default=false) {

  if (!empty($idImage)){

    $attImage = wp_get_attachment_image_src( $idImage, 'full' );
    if (empty($attImage)) {
      return false;
    }

    if (empty($imgW) && empty($imgH)) {
      $imgW = $attImage[1];
      $imgH = $attImage[2];
    }

    $decalageX = 0;
    $decalageY = 0;

    $info = pathinfo($attImage[0]);
    $domain = baseurl(get_bloginfo('url'));
    $basename = $info['filename'];
    $extension = $info['extension'];
    $imagepost = get_post($idImage);
    if (empty($imagepost->post_title)) {
      $seotitle = alias($basename);
    } else {
      $seotitle = alias($imagepost->post_title);
    }

    //------ SVG
    if ($extension=='svg') {
      $svgpath = basepath().rw_remove_root($attImage[0]);
      $iconfile = new DOMDocument();
      $iconfile->load($svgpath);
      foreach ($iconfile->getElementsByTagName('title') as $titletag) {
        $titletag->parentNode->removeChild($titletag);
      }
      // security
      foreach ($iconfile->getElementsByTagName('script') as $poisontag) {
        $poisontag->parentNode->removeChild($poisontag);
      }
      return $iconfile->saveHTML($iconfile->getElementsByTagName('svg')[0]);
      // todo : duplicate SVG file to folder "images"

    } else if ($extension=='png') {
      $ext = '.png';
      $qualtxt = '';

    } else {
      $ext = '.jpg';
      $qualtxt = '-'.$qual;
    }

    $largeurImgBig = $attImage[1];
    $hauteurImgBig = $attImage[2];

    // Si seule la largeur est contrainte, on adapte la hauteur proportionnellement
    if($imgW==0){
      $hauteurFinale = $imgH;
      $largeurFinale = floor($hauteurFinale/$hauteurImgBig*$largeurImgBig);
      $hauteurImgSmall = $hauteurFinale;
      $largeurImgSmall = $largeurFinale;
    }
    // Si seule la hauteur est contrainte, on adapte la largeur proportionnellement
    if($imgH==0){
      $largeurFinale = $imgW;
      $hauteurFinale = floor($largeurFinale/$largeurImgBig*$hauteurImgBig);
      $hauteurImgSmall = $hauteurFinale;
      $largeurImgSmall = $largeurFinale;
    }
    // Si largeur et hauteur sont contraintes, on remplit et on crop
    if($imgH!=0 && $imgW!=0){
      $RatioW = $imgW / $largeurImgBig;
      $RatioH = $imgH / $hauteurImgBig;
      $largeurFinale = $imgW;
      $hauteurFinale = $imgH;
      if($RatioW>$RatioH){
        // adapter sur la largeur
        $hauteurImgSmall = floor($hauteurImgBig/$largeurImgBig*$imgW);
        $largeurImgSmall = $largeurFinale;
        $decalageY = floor(($hauteurImgSmall-$imgH)/2);
      } else {
        // adapter sur la hauteur
        $hauteurImgSmall = $hauteurFinale;
        $largeurImgSmall = floor($largeurImgBig/$hauteurImgBig*$imgH);
        $decalageX = floor(($largeurImgSmall-$imgW)/2);
      }
    }

    $filename = $seotitle.'-'.$largeurFinale.'-'.$hauteurFinale.$qualtxt.'-'.$idImage;
    $filenameext = $seotitle.'-'.$largeurFinale.'-'.$hauteurFinale.$qualtxt.'-'.$idImage.$ext;
    $abspath = basepath();
    $imgpath = str_replace('//','/',$abspath.'/images/');

    if(!file_exists($imgpath.$filenameext)) {
      if(!file_exists($imgpath.utf8_decode($filenameext))) {

        if (!file_exists($abspath.'/images')) {
            mkdir($abspath.'/images', 0755, true);
        }

        $nouvelleImage = imagecreatetruecolor($largeurFinale, $hauteurFinale);
        $imgRelativeSrc = $abspath.rw_remove_root($attImage[0]);
        if(!file_exists($imgRelativeSrc)) {
          $imgRelativeSrc = utf8_decode($imgRelativeSrc);
        }
        if(file_exists($imgRelativeSrc)) {
          $type = exif_imagetype($imgRelativeSrc);
          $typeValid = 1;

          switch($type){
            case 1:
              $source = imagecreatefromgif($imgRelativeSrc);
              break;
            case 2:
              $source = imagecreatefromjpeg($imgRelativeSrc);
              break;
            case 3:
              $source = imagecreatefrompng($imgRelativeSrc);
              imagesavealpha($nouvelleImage, true);
              imagealphablending($nouvelleImage, false);
              $transparent = imagecolorallocatealpha($nouvelleImage, 0, 0, 0, 127);
              imagefill($nouvelleImage, 0, 0, $transparent);
              break;
            default:
              // invalide
              $typeValid = 0;
              break;
          }

          if($typeValid==1){
            imagecopyresampled($nouvelleImage, $source, -$decalageX, -$decalageY, 0, 0, $largeurImgSmall, $hauteurImgSmall, $largeurImgBig, $hauteurImgBig);

            if ($extension=='png') {
              imagepng($nouvelleImage,$abspath.'/images/'.$filenameext);
            } else {
              imagejpeg($nouvelleImage,$abspath.'/images/'.$filenameext, $qual);
            }
            // return $domain.'/images/'.$filenameext;
            return optimizeImgObj($filename,$ext);
          } else {
            return "Format d'image invalide";
          }
        } else {
          return "Erreur : aucun fichier trouvé.";
        }

      } else {
        // return $domain.'/images/'.utf8_decode($filenameext);
        return optimizeImgObj(utf8_decode($filename),$ext);
      }
    } else {
      // return $domain.'/images/'.$filenameext;
      return optimizeImgObj($filename,$ext);
    }

  }
}



function optimizeImgObj($file='optimizeImgObj empty $file',$ext='optimizeImgObj empty $ext') {
  $abspath = basepath();
  $domain = baseurl( get_bloginfo('url') );

  if (file_exists($abspath.'/images/'.$file.'-o'.$ext)) {
    return $domain.'/images/'.$file.'-o'.$ext;

  } else {

    try {
      $source = \Tinify\fromFile($abspath.'/images/'.$file.$ext);
      $source->toFile($abspath.'/images/'.$file.'-o'.$ext);
      return $domain.'/images/'.$file.'-o'.$ext;

    } catch(Exception $e) {
      return $domain.'/images/'.$file.$ext;
      
    }

  }

}



function picsum($width=false,$height=false) {
  if (!$width) {
    $width = '';
    if (!$height) { $height = '200'; }
  } else {
    $width = '/'.$width;
  }
  if (!$height) {
    $height = '';
  } else {
    $height = '/'.$height;
  }
  return 'https://picsum.photos/'.$width.$height.'/?random';
}

function rw_remove_root ($url) {
  $result = parse_url($url);
  return $result['path'];
}

function baseurl($url) {
  $result = parse_url($url);
  return $result['scheme']."://".$result['host'];
}

function basepath() {
  return str_replace($_SERVER["SCRIPT_NAME"],'',$_SERVER["SCRIPT_FILENAME"]);
}