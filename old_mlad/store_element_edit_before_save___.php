<?
function BXIBlockAfterSave($arFields)
{
    //��������� ����������
    $description = array();
    $value = array();
    $property_value_id = array();

    $res = CIBlockElement::GetByID($arFields['ID']);

    if ($obRes = $res->GetNextElement()):
        $ar_prop = $obRes->GetProperty("IMG1600");
        //$ar_prop_artikul = $obRes->GetProperty("ARTIKUL");
    endif;

    //echo "<pre>"; print_r($ar_prop); echo "</pre>"; die();


    //if(empty($ar_prop_artikul["VALUE"])):
    //$ELEMENT_ID = $arFields['ID'];
    //$PROPERTY_CODE = "ARTIKUL";
    //CIBlockElement::SetPropertyValueCode($ELEMENT_ID, $PROPERTY_CODE, $ELEMENT_ID);
    //endif;

    $i = 0;

    foreach ($ar_prop["DESCRIPTION"] as $index => $val):

        $i++;
        $description[$index] = $val;
        if (empty($val)) $description[$index] = $i * 10;

    endforeach;

    asort($description); //������������� ������ ��������

    $i = 0;

    foreach ($description as $key => $val):

        $property_value_id[$i] = $ar_prop["PROPERTY_VALUE_ID"][$key];
        $value[$i] = $ar_prop["VALUE"][$key];
        $description_sort[$i] = $ar_prop["DESCRIPTION"][$key];
        $i++;

    endforeach;

    //��������� ������� ���� �� 1600 � ��������� �������� ����������
    $i = 0;
    $new_images = array();
    $new_images_path = array();

    foreach ($value as $index => $id):

        $img_path = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . CFile::GetPath($id);
        $img_orig_name = substr($img_path, strrpos($img_path, "/") + 1);

        $width = 1600;
        $height = 1600;

        list($width_orig, $height_orig) = getimagesize($img_path);

        if ($width && ($width_orig < $height_orig)) {
            if ($height < $height_orig) {
                $width = ($height / $height_orig) * $width_orig;
            } else {
                $width = $width_orig;
                $height = $height_orig;
            }
        } else {
            if ($width < $width_orig) {
                $height = ($width / $width_orig) * $height_orig;
            } else {
                $height = $height_orig;
                $width = $width_orig;
            }
        }
        $image_p = imagecreatetruecolor($width, $height);

        $image = imagecreatefromjpeg($img_path);

        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
        $new_img_path = tempnam("/tmp", "BIG") . ".jpg";


        imagejpeg($image_p, $new_img_path, 90);
        /* creatWaterMarkFile($new_img_path); Loki0 */


        $new_images[] = CFile::MakeFileArray($new_img_path);
        $new_images_path[] = $new_img_path;

        $key = $property_value_id[$index];
        $descr = $description_sort[$index];
        if (empty($descr)) $descr = ($i + 1) * 10;

        $PROPERTY_VALUE_[$key] = array("name" => "", "type" => "", "tmp_name" => "", "error" => "4", "size" => "0", "del" => "Y");
        $PROPERTY_VALUE_[$i]["VALUE"] = CFile::MakeFileArray($new_img_path);
        $PROPERTY_VALUE_[$i]["VALUE"]["name"] = $img_orig_name;
        $PROPERTY_VALUE_[$i]["DESCRIPTION"] = "$descr";
        ksort($PROPERTY_VALUE_);

        $i++;

    endforeach;

    //echo "<pre>"; print_r($PROPERTY_VALUE_); echo "</pre>";
    //echo "<br>========================";
    //echo "<pre>"; print_r($PROPERTY_VALUE_); echo "</pre>"; die();

    $ELEMENT_ID = $arFields['ID'];
    $PROPERTY_CODE = "IMG1600";

    //echo "$ELEMENT_ID<br>$PROPERTY_CODE<br>"; echo "<pre>"; print_r($PROPERTY_VALUE_); echo "<pre>"; die();

    CIBlockElement::SetPropertyValueCode($ELEMENT_ID, $PROPERTY_CODE, $PROPERTY_VALUE_);

    foreach ($new_images_path as $path):
        @unlink($path);
    endforeach;

    //�������� ���������
    $res = CIBlockElement::GetByID($arFields['ID']);
    if ($obRes = $res->GetNextElement()) {
        $new_images = array();
        $old_images = array();
        $new_images_path = array();
        $ar_res = $obRes->GetFields();
        $ar_prop = $obRes->GetProperty("IMG1600");

        /* 1600 */

        $ar_prop_1600_id = $obRes->GetProperty("IMG1600");
        $ar_prop_1600_id = $ar_prop_1600_id["PROPERTY_VALUE_ID"];

        $new_images1600 = array();
        $old_images1600 = array();
        $new_images_path1600 = array();
        /*  */

        $ar_prop_200_id = $obRes->GetProperty("IMG255");
        $ar_prop_200_id = $ar_prop_200_id["PROPERTY_VALUE_ID"];


        $ar_prop_70_id = $obRes->GetProperty("IMG70");
        $ar_prop_70_id = $ar_prop_70_id["PROPERTY_VALUE_ID"];

        $new_images70 = array();
        $old_images70 = array();
        $new_images_path70 = array();


        $i = 0;
        foreach ($ar_prop["VALUE"] as $key => $img):
            //������ ������
            if ($i == 0):

                $img_path = $_SERVER['DOCUMENT_ROOT'] . CFile::GetPath($img);
                $img_orig_name = substr($img_path, strrpos($img_path, "/") + 1);
                $width = 128;
                $height = 128;
                list($width_orig, $height_orig) = getimagesize($img_path);

                if ($width && ($width_orig < $height_orig))
                    $width = ($height / $height_orig) * $width_orig;
                else
                    $height = ($width / $width_orig) * $height_orig;

                $image_p = imagecreatetruecolor($width, $height);
                $image = imagecreatefromjpeg($img_path);
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
                $new_img_path = tempnam("/tmp", "FOO") . ".jpg";
                imagejpeg($image_p, $new_img_path, 99);

                $add_array = Array('PREVIEW_PICTURE' => CFile::MakeFileArray($new_img_path));
                $add_array["PREVIEW_PICTURE"]["name"] = $img_orig_name;
                $be = new CIBlockElement();
                $be->Update($ELEMENT_ID, $add_array);

                @unlink($new_img_path);

                @unlink($new_img_path);

            endif;

            $img_path = $_SERVER['DOCUMENT_ROOT'] . CFile::GetPath($img);
            $img_orig_name = substr($img_path, strrpos($img_path, "/") + 1);

            $width = 255;
            $height = 255;

            list($width_orig, $height_orig) = getimagesize($img_path);
            if ($width && ($width_orig < $height_orig))
                $width = ($height / $height_orig) * $width_orig;
            else
                $height = ($width / $width_orig) * $height_orig;

            $image_p = imagecreatetruecolor($width, $height);
            $image = imagecreatefromjpeg($img_path);

            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

            $new_img_path = tempnam("/tmp", "FOO") . ".jpg";

            imagejpeg($image_p, $new_img_path, 99);
            creatWaterMarkFile($new_img_path, 14);
            /* loki1 */
            $new_images[$i] = CFile::MakeFileArray($new_img_path);
            $new_images[$i]["name"] = $img_orig_name;
            $new_images_path[] = $new_img_path;


            $width = 70;
            $height = 70;

            list($width_orig, $height_orig) = getimagesize($img_path);
            if ($width && ($width_orig < $height_orig))
                $width = ($height / $height_orig) * $width_orig;
            else
                $height = ($width / $width_orig) * $height_orig;

            $image_p = imagecreatetruecolor($width, $height);
            $image = imagecreatefromjpeg($img_path);

            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

            $new_img_path = tempnam("/tmp", "FOO") . ".jpg";

            imagejpeg($image_p, $new_img_path, 95);

            $new_images70[$i] = CFile::MakeFileArray($new_img_path);
            $new_images70[$i]["name"] = $img_orig_name;
            $new_images_path70[] = $new_img_path;


            /* ���� ��� ���������� 1600  */

            $width = 1600;
            $height = 1600;

            list($width_orig, $height_orig) = getimagesize($img_path);
            if ($width && ($width_orig < $height_orig))
                $width = ($height / $height_orig) * $width_orig;
            else
                $height = ($width / $width_orig) * $height_orig;

            $image_p = imagecreatetruecolor($width, $height);
            $image = imagecreatefromjpeg($img_path);

            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);

            $new_img_path = tempnam("/tmp", "FOO") . ".jpg";

            imagejpeg($image_p, $new_img_path, 95);
            creatWaterMarkFile($new_img_path, 60);

            $new_images1600[$i] = CFile::MakeFileArray($new_img_path);
            $new_images1600[$i]["name"] = $img_orig_name;
            $new_images_path1600[] = $new_img_path;
            /*  ���� ��� ���������� 1600 */
            $i++;

        endforeach;

        //echo "<pre>"; print_r($new_images70); echo "</pre>"; die();

        $ELEMENT_ID = $arFields['ID'];
        $IBLOCK_ID = $arFields['IBLOCK_ID'];

        //255
        $PROPERTY_CODE = "IMG255";
        $PROPERTY_VALUE = $new_images;

        foreach ($ar_prop_200_id as $p):
            $PROPERTY_VALUE[$p] = array("name" => "", "type" => "", "tmp_name" => "", "error" => "4", "size" => "0", "del" => "Y");
        endforeach;

        CIBlockElement::SetPropertyValueCode($ELEMENT_ID, $PROPERTY_CODE, $PROPERTY_VALUE);

        foreach ($new_images_path as $path):
            @unlink($path);
        endforeach;

        //70
        $PROPERTY_CODE = "IMG70";
        $PROPERTY_VALUE = $new_images70;

        foreach ($ar_prop_70_id as $p):
            $PROPERTY_VALUE[$p] = array("name" => "", "type" => "", "tmp_name" => "", "error" => "4", "size" => "0", "del" => "Y");
        endforeach;

        CIBlockElement::SetPropertyValueCode($ELEMENT_ID, $PROPERTY_CODE, $PROPERTY_VALUE);

        foreach ($new_images_path70 as $path):
            @unlink($path);
        endforeach;


        /* 1600 */
        $PROPERTY_CODE = "IMG1600";
        $PROPERTY_VALUE = $new_images1600;

        foreach ($ar_prop_1600_id as $p):
            $PROPERTY_VALUE[$p] = array("name" => "", "type" => "", "tmp_name" => "", "error" => "4", "size" => "0", "del" => "Y");
        endforeach;

        CIBlockElement::SetPropertyValueCode($ELEMENT_ID, $PROPERTY_CODE, $PROPERTY_VALUE);

        foreach ($new_images_path1600 as $path):
            @unlink($path);
        endforeach;

    }
//die();
}

class watermark3
{

    # given two images, return a blended watermarked image
    function create_watermark($main_img_obj, $watermark_img_obj, $alpha_level = 100)
    {
        $alpha_level /= 100;    # convert 0-100 (%) alpha to decimal

        # calculate our images dimensions
        $main_img_obj_w = imagesx($main_img_obj);
        $main_img_obj_h = imagesy($main_img_obj);
        $watermark_img_obj_w = imagesx($watermark_img_obj);
        $watermark_img_obj_h = imagesy($watermark_img_obj);

        # determine center position coordinates
        $main_img_obj_min_x = floor(($main_img_obj_w / 2) - ($watermark_img_obj_w / 2));
        $main_img_obj_max_x = ceil(($main_img_obj_w / 2) + ($watermark_img_obj_w / 2));
        $main_img_obj_min_y = floor(($main_img_obj_h / 2) - ($watermark_img_obj_h / 2));
        $main_img_obj_max_y = ceil(($main_img_obj_h / 2) + ($watermark_img_obj_h / 2));

        # create new image to hold merged changes
        $return_img = imagecreatetruecolor($main_img_obj_w, $main_img_obj_h);

        # walk through main image
        for ($y = 0; $y < $main_img_obj_h; $y++) {
            for ($x = 0; $x < $main_img_obj_w; $x++) {
                $return_color = NULL;

                # determine the correct pixel location within our watermark
                $watermark_x = $x - $main_img_obj_min_x;
                $watermark_y = $y - $main_img_obj_min_y;

                # fetch color information for both of our images
                $main_rgb = imagecolorsforindex($main_img_obj, imagecolorat($main_img_obj, $x, $y));

                # if our watermark has a non-transparent value at this pixel intersection
                # and we're still within the bounds of the watermark image
                if ($watermark_x >= 0 && $watermark_x < $watermark_img_obj_w &&
                    $watermark_y >= 0 && $watermark_y < $watermark_img_obj_h
                ) {
                    $watermark_rbg = imagecolorsforindex($watermark_img_obj, imagecolorat($watermark_img_obj, $watermark_x, $watermark_y));

                    # using image alpha, and user specified alpha, calculate average
                    $watermark_alpha = round(((127 - $watermark_rbg['alpha']) / 127), 2);
                    $watermark_alpha = $watermark_alpha * $alpha_level;

                    # calculate the color 'average' between the two - taking into account the specified alpha level
                    $avg_red = $this->_get_ave_color($main_rgb['red'], $watermark_rbg['red'], $watermark_alpha);
                    $avg_green = $this->_get_ave_color($main_rgb['green'], $watermark_rbg['green'], $watermark_alpha);
                    $avg_blue = $this->_get_ave_color($main_rgb['blue'], $watermark_rbg['blue'], $watermark_alpha);

                    # calculate a color index value using the average RGB values we've determined
                    $return_color = $this->_get_image_color($return_img, $avg_red, $avg_green, $avg_blue);

                    # if we're not dealing with an average color here, then let's just copy over the main color
                } else {
                    $return_color = imagecolorat($main_img_obj, $x, $y);

                } # END if watermark

                # draw the appropriate color onto the return image
                imagesetpixel($return_img, $x, $y, $return_color);

            } # END for each X pixel
        } # END for each Y pixel

        # return the resulting, watermarked image for display
        return $return_img;

    } # END create_watermark()

    # average two colors given an alpha
    function _get_ave_color($color_a, $color_b, $alpha_level)
    {
        return round((($color_a * (1 - $alpha_level)) + ($color_b * $alpha_level)));
    } # END _get_ave_color()

    # return closest pallette-color match for RGB values
    function _get_image_color($im, $r, $g, $b)
    {
        $c = imagecolorexact($im, $r, $g, $b);
        if ($c != -1) return $c;
        $c = imagecolorallocate($im, $r, $g, $b);
        if ($c != -1) return $c;
        return imagecolorclosest($im, $r, $g, $b);
    } # EBD _get_image_color()

} # END watermark API

function creatWaterMarkFile($img, $fsize)
{

    $text = '��������.ru';
    $font = $_SERVER['DOCUMENT_ROOT'] . '/images/system/font.ttf';

    $font_size = $fsize;
    $watermark = array();
    $image = new Imagick($img);
    $image->setImageFormat("jpg");
    $draw = new ImagickDraw();
    $draw->setGravity(Imagick::GRAVITY_CENTER);
    $draw->setFont($font);
    $draw->setFontSize($font_size);

    $textColor = new ImagickPixel("#ccc");
    $draw->setFillColor($textColor);
    $draw->setFillOpacity(0.5);
    $im = new imagick();
    $properties = $im->queryFontMetrics($draw, $text);
    $watermark['w'] = intval($properties["textWidth"] + 5);
    $watermark['h'] = intval($properties["textHeight"] + 280);
    $im->newImage($watermark['w'], $watermark['h'], new ImagickPixel("transparent"));
    $im->setImageFormat("jpg");
    $im->annotateImage($draw, 0, 0, -45, $text);

    $watermark = $im->clone();
//  $watermark->setImageBackgroundColor($textColor);
//  $watermark->shadowImage(80, 2, 2, 2);

//  $watermark->compositeImage($im, Imagick::COMPOSITE_OVER, 0, 0);


    for ($i = -200; $i <= 1600; $i = $i + 400) {
        $image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 0, $i);
        $image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 400, $i);
        $image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 800, $i);
        $image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 1200, $i);
        $image->compositeImage($watermark, Imagick::COMPOSITE_OVER, 1600, $i);
    }

    $image->writeImage($img);
}

?>