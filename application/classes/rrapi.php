<?php

/**
 * Class rrapi - работа с RetailRocket через api
 */
class rrapi
{
    const URL = 'http://api.retailrocket.ru/api/1.0/Recomendation/';
    const KEY = '55cb27211e994737e4ab339b';

    /**
     * @param $func
     * @param string $url
     * @return bool|mixed|string
     * @throws Cache_Exception
     * @throws Kohana_Exception
     * @throws View_Exception
     */
    private static function _query($func, $url = "")
    {
        $return = FALSE;
        $key = $func.$url;
        $cached = Cache::instance()->get($key);
        if ( ! empty($cached)) $return = json_decode($cached, TRUE);
        if ($return == FALSE) {
            $url = self::URL.$func.'/'.self::KEY.'/'.$url;
            $json = file_get_contents($url);
            if ( ! empty($json)) {
                $rr = json_decode($json);
                if ( ! empty($rr)) { // отсеиваем не в наличии

                    $return = DB::select('id')
                        ->from('z_good')
                        ->where('id', 'IN', $rr)
                        ->where('show', '=', 1)
                        ->where('active', '=', 1)
                        ->where('price', '>', 1)
                        ->where('qty', '!=', 0)
                        ->execute()
                        ->as_array('id', 'id');

                    $return = Arr::extract($return, $rr); // порядок как в рр
                }
            }

            Cache::instance()->set($key, json_encode($return));
        }

        return $return;
    }

    /**
     * отписка пользователя от retailrocket
     * @param $email
     * @return bool|string
     */
    static function unsubscribe($email)
    {
        if ( ! Valid::email($email)) return FALSE;
        $query = [
            'email'     => $email,
            'apiKey'    => '55cb27211e994737e4ab339c',
        ];
        return file_get_contents("http://api.retailrocket.ru/api/1.0/partner/".self::KEY."/unsubscribe/?".http_build_query($query));
    }

    static function CategoryToItems($cat_id)
    {
        return self::_query(__FUNCTION__, $cat_id);
    }

    function UpSellItemToItems($item_id)
    {
        return self::_query(__FUNCTION__, $item_id);
    }

    function CrossSellItemToItems($item_ids)
    {
        return self::_query(__FUNCTION__, implode(',', array_slice($item_ids, 0, 20)));
    }

    function RelatedItems($item_ids)
    {
        return self::_query(__FUNCTION__, implode(',', array_slice($item_ids, 0, 20)));
    }

    function SearchToItems($query)
    {
        return self::_query(__FUNCTION__, '?keyword='.urlencode($query));
    }

    function PersonalRecommendation($user_id)
    {
        return self::_query(__FUNCTION__, '?rrUserId='.$user_id);
    }

    function ItemsToMain()
    {
        return self::_query(__FUNCTION__);
    }

    static function tracking()
    {
        return View::factory('smarty:retail_rocket/tracking', ['partner_id' => self::KEY]);
    }
}