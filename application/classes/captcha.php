<?php

class Captcha {

    const SIZE = 24;
    const LEN = 6;
    const POOL = '0123456789';
    const BG_COLOR = '#ffffff';
    const STROKE_N = 4;
    
    function __construct()
    {
        $width = self::SIZE * self::LEN * 0.9;
        $height = self::SIZE * 2;

        Session::instance()->set('captcha', $captcha = Text::random(self::POOL, self::LEN));
        $font = APPPATH.'config/font.ttf';

        $image = new Imagick();
        $draw = new ImagickDraw(); 
        $image->newImage($width, $height, new ImagickPixel(self::BG_COLOR));
        
        $draw->setFont($font);
        $draw->setFontSize(32);

        $captcha = '';
        
        for ($i = 0; $i < self::LEN; $i++) {
            $x = $i * 21;
            $y = 39 + mt_rand(-9,9);
            $angle = mt_rand(-15,15);
            $n = mt_rand(0,9);
            $captcha .= $n;
            $image->annotateImage($draw, $x, $y, $angle, $n);
        }
        
        $draw->setStrokeColor(new ImagickPixel( self::BG_COLOR));
        $draw->setStrokeWidth  (1.6);
        $draw->setStrokeDashArray (array( 0, 0,0,0));
        for ($i = 0; $i < 9; $i++) {
            $draw->line(
                    0,
                    mt_rand(0, intval($height / self::STROKE_N) * $i),
                    $width,
                    mt_rand(0, intval($height /  self::STROKE_N) * $i));   
        }

        $image->drawImage( $draw );
        
        Cookie::set('captcha', self::hash($captcha));

        $image->setImageFormat('png');  
        header('Content-type: image/png');  
        echo $image;
        exit();
    }

    /**
     * @static Проверка капчи
     * @param $string
     * @return bool
     */
    public static function check($string)
    {
        if (strlen(trim($string)) < 3) return FALSE;

        return Cookie::get('captcha') === self::hash($string);
    }

    private static function hash($value)
    {
        return md5(Cookie::$salt . $value);
    }
}