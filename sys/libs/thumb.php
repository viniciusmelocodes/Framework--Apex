<?
class thumb_lib
{
        function get_scaled($path, $req_W, $req_H, $quality=85, $monochrome=false){
                if ( !file_exists($path) || is_dir($path) ) {
                        $path = 'resources/media/default_image.jpg';
                }
                $ext = explode('.',$path);
                $ext = array_pop($ext);

                $thumb_path = $path.'.thumbs/';

                if(!is_dir($thumb_path)){
                        mkdir($thumb_path);
                }
                
                $thumb_path = $thumb_path.$req_W.'x'.$req_H.'.scaled_to_fit.jpg';


                if(!file_exists ($thumb_path)){
                        switch(strtolower($ext)){
                                case"jpg":
                                        $image = imagecreatefromjpeg($path);
                                        break;
                                case"png":
                                        $image = imagecreatefrompng($path);
                                        break;
                                case"gif":
                                        $image = imagecreatefromgif($path);
                                        break;
                        }


                        // Target dimensions
                        $max_width = $req_W;
                        $max_height = $req_H;
                        
                        // Get current dimensions
                        $old_width  = imagesx($image);
                        $old_height = imagesy($image);
                        
                        // Calculate the scaling we need to do to fit the image inside our frame
                        $scale      = min($max_width/$old_width, $max_height/$old_height);
                        
                        // Get the new dimensions
                        $new_width  = ceil($scale*$old_width);
                        $new_height = ceil($scale*$old_height);
                        
                        // Create new empty image
                        $new = imagecreatetruecolor($new_width, $new_height);
                        
                        // Resize old image into new
                        imagecopyresampled($new, $image, 
                            0, 0, 0, 0, 
                            $new_width, $new_height, $old_width, $old_height);
                        imagejpeg($new, $thumb_path, $quality);
                }
                return $thumb_path;

        }
        
        // resize the original image to the requested proportions according to the bigget
        // width / height, then cropping the new image from the center 
        // according to the requested sizes and saving the image
        // credit: Zion Ben Yaakov
        function get($path,$req_W, $req_H, $quality=85, $monochrome=false){
                //return $path;
                if ( !file_exists($path) || is_dir($path) ) {
                        $path = 'resources/media/default_image.jpg';
                }
                
                $ext = explode('.',$path);
                $ext = array_pop($ext);
                
                $thumb_path = $path.'.thumbs/';

                if(!is_dir($thumb_path)){
                        mkdir($thumb_path);
                }
                
                $monochrome_suffix = ($monochrome)?'_bw':'';
                
                $thumb_path = $thumb_path.$req_W.'x'.$req_H.$monochrome_suffix.'.jpg';
                //return $thumb_path;

                if(!file_exists ($thumb_path)){
                        switch(strtolower($ext)){
                                case"jpg":
                                        $image = imagecreatefromjpeg($path);
                                        break;
                                case"png":
                                        $image = imagecreatefrompng($path);
                                        break;
                                case"gif":
                                        $image = imagecreatefromgif($path);
                                        break;
                        }
                        $src_W = imageSX($image);
                        $src_H = imageSY($image);
                        
                        $ratio_orig = $src_W / $src_H;
                        
                        $target_H = $req_H;
                        $target_W = $req_W;
                        
                        if ($req_W/$req_H > $ratio_orig)
                        {
                           $target_H = $req_W / $ratio_orig;
                        }
                        else
                        {
                            $target_W = $req_H * $ratio_orig;   
                        }
                        
                        $target_W = floor($target_W);
                        $target_H = floor($target_H);
                        
                        $target_X = 0;
                        $target_Y = 0;
                            
                        $tn = imagecreatetruecolor($target_W, $target_H);
                        $transparent = imagecolorallocatealpha($tn, 255, 255, 255, 0);
                        imagefilledrectangle($tn, 0, 0, $target_W, $target_H, $transparent);
                        
                        imagecopyresampled($tn, $image, $target_X, $target_Y, 0, 0, $target_W, $target_H, $src_W, $src_H);
                        imagejpeg($tn, $thumb_path, $quality);
                        
                        $target_X = ($target_W - $req_W)/2;
                        $target_Y = ($target_H - $req_H)/2;
                        $tn = imagecreatetruecolor($req_W, $req_H);
                        $image = imagecreatefromjpeg($thumb_path);
                        if($monochrome){
                                imagefilter($image, IMG_FILTER_GRAYSCALE);
                        }
                        imagecopyresampled($tn, $image, 0, 0, $target_X, $target_Y, $req_W, $req_H, $req_W, $req_H);
                        
                        imagejpeg($tn, $thumb_path, $quality);
                }
                return $thumb_path;
        }
}