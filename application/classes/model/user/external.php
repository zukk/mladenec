<?php
/**
 * Model for process external account (social nets).
 * 
 * @package     mladenec.ru
 * @subpackage  model
 * @category    social
 * @author      iFabrik <input@ifabrik.ru>
 * @version     $Id$
 */
class Model_User_External extends ORM
{
    /**
     * List of supported sources.
     * @static
     */
    public static $arSupportedSource = array('vk.com');

    /**
     * Configuration of the current domain.
     * @static
     */
    public static $hostnameConfig = array();

    /**
     * Result of last call detect()
     * @static
     */
    protected static $lastSource = array();

    /**
     * Table name for model.
     * @static
     */
    protected static $model_table_name = 'z_user_external';

    /**
     * {@inheritdoc}
     */
    protected $_table_name = 'z_user_external';

    /**
     * {@inheritdoc}
     */
    protected $_table_columns = array(
        'id'            => '', 
        'source'        => '', 
        'source_id'     => '',
        'source_data'   => '',
        'user_id'       => 0
    );

    /**
     * {@inheritdoc}
     */
    protected  $_has_one = array('user' => array('model' => 'user', 'foreign_key' => 'id'));

    /**
     * Retrieve method name for source.
     * 
     * @param string $sSource Source name.
     * @return string
     */
    protected static function getSourceMethodPostfix($sSource)
    {
        return ucfirst(Inflector::camelize(str_replace(array('.', '-'), '_', $sSource)));
    }

    /**
     * Defining user access through a social network.
     * 
     * @static
     * @param array|null $arSource List of soical networks to detect.
     * @return string|null
     */
    public static function detectSource($arSource = null)
    {
        // Fix sources.
        if (empty($arSource)) $arSource = self::$arSupportedSource;
        if ( ! is_array($arSource)) $arSource = array($arSource);

        // Define list of sources to detect.
        $arToDetect = array_intersect($arSource, self::$arSupportedSource);

        if ( ! count($arToDetect)) return false;

        // Fetch configuration for domain.
        $arConfigDomains = Kohana::$config->load('domains')->as_array();
        $host = empty($_SERVER['HTTP_HOST']) ? 'default' : $_SERVER['HTTP_HOST'];
        $arDomainConfig = array_filter($arConfigDomains, function($domain) use ($host) { return ($domain['host'] === $host); });

        if ( ! count($arDomainConfig)) return false;
        self::$hostnameConfig = array_shift($arDomainConfig);

        $arResult = array();
        foreach($arSource as $source)
        {
            // Define method name for call.
            $methodName = 'detect' . self::getSourceMethodPostfix($source);

            // If method for detect the source is not found - set source to false.
            if (! method_exists(__CLASS__, $methodName)) { $arResult[$source] = false; continue; }

            // Call detect method for current source.
            $arResult[$source] = call_user_func(array(__CLASS__, $methodName));
        }

        $arLastGoodResult = array_keys(array_filter($arResult));
        self::$lastSource = (count($arLastGoodResult) ? array_shift($arLastGoodResult) : null);

        return self::$lastSource;
    }

    /**
     * Check exists records
     * of external account and create new if not found it.
     *
     * @param Model_User|null $user Current user.
     * @throws Exception
     * @return array
     */
    public static function getAccountInfo($user = null)
    {
        if ( ! Model_User_External::detectSource())
        {
            try {
                $account = unserialize(Session::instance()->get('external'));
            } catch (Session_Exception $e) {
                return NULL;
            }
            if (empty($account['updated_at'])) return null;
            //if (300 < (time() - $account['updated_at'])) return null;

            return $account;
        }

        // Check last source.
        if (null === self::$lastSource) {
            throw new Exception("Source is not detected!");
        }

        // Define method name for call external api.
        $methodName = 'fetchProfile' . self::getSourceMethodPostfix(self::$lastSource);

        // If method for detect the source is not found - set source to false.
        if (! method_exists(__CLASS__, $methodName)) { 
            throw new Exception(sprintf('Method "%s" is not found!', $methodName));
        }

        // Call detect method for current source.
        $account = call_user_func(array(__CLASS__, $methodName));

        // Add extended inforamtion.
        $account['source'] = self::$lastSource;
        $account['updated_at'] = time();

        // If user is logged and account is not linked.
        if (empty($account['user_id']) && !empty($user))
        {
            return self::linkageUserAccount(self::$lastSource, $account, $user->id);
        }
        // If account already linked to another user.
        elseif(! empty($account['user_id']) && $user && $account['user_id'] != $user->id)
        {
            // ???
            $user->logout();

            $user = new Model_User;
            $account['user'] = $user->where('id', '=', $account['user_id'])->find();
        }
        // If account already linked to another user.
        elseif(! empty($account['user_id']) && empty($user))
        {
            $user = new Model_User;
            $account['user'] = $user->where('id', '=', $account['user_id'])->find();
        }

        Session::instance()->set('external', serialize($account))->write();

        return $account;
    }

    /**
     * Link external account with user.
     *
     * @param string $sSource
     * @param array $account
     * @param integer $iUserId
     * @throws Exception
     * @return array
     */
    public static function linkageUserAccount($sSource, array &$account, $iUserId)
    {
        // Define user column.
        $account['user'] = null;

        // If user id is null - break.
        if (0 >= (int) $iUserId) return $account;

        // Filter source.
        $arFiltered = array_intersect(array($sSource), self::$arSupportedSource);

        // Check filtered accounts.
        if (! count($arFiltered)) {
            throw new Exception(sprintf('Source "%s" is not supported!', $sSource));
        }

        // Update external account data.
        $affected_rows = DB::update(self::$model_table_name)
                            ->set(array('user_id' => $iUserId))
                            ->where_open()
                                ->where('id', '=', $account['local'])
                                ->where('source', '=', $sSource)
                                ->where('source_id', '=', $account['external'])
                            ->where_close()
                            ->execute();

        if ($affected_rows)
        {
            $user = new Model_User;
            $account['user'] = $user->where('id', '=', $iUserId)->find();
        }

        return $account;
    }

    /**
     * Detect request through a vk.com
     * 
     * @return boolean
     */
    protected static function detectVkCom()
    {
        if (! isset($_GET['auth_key']) || ! isset($_GET['viewer_id'])) return false;

        // Define variables for check signature.
        $appId = @self::$hostnameConfig['app_id'] ?: null;
        $appSecret = @self::$hostnameConfig['app_secret'] ?: null;

        if (null === $appId || null === $appSecret) return false;

        // Return result for check auth_key.
        return ( strlen($_GET['auth_key']) && $_GET['auth_key'] === md5($appId .'_'. $_GET['viewer_id'] .'_'. $appSecret));
    }

    /**
     * Request a profile from social network
     * vk.com and create new account record.
     *
     * @throws Exception
     * @return array
     */
    protected static function fetchProfileVkCom()
    {
        require_once( dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'VKontakte.php');

        // Init Api.
        $vkontakte = new VKontakte();
        $vkontakte->setTestMode((@self::$hostnameConfig['app_test_mode'] ?: false));
        $account = @array_shift($vkontakte->api('getProfiles', array('fields' => 'contacts', 'uids' => $vkontakte->getViewerId())));

        if (! $account) {
            throw new Exception(sprintf('Cannot fetch profile for "%s"', self::$lastSource));
        }

        // Define method name for call external api.
        $methodName = 'prepareAccountData' . self::getSourceMethodPostfix(self::$lastSource);

        // If method for detect the source is not found - set source to false.
        if (! method_exists(__CLASS__, $methodName)) { 
            throw new Exception(sprintf('Method "%s" is not found!', $methodName));
        }

        // Fetch account user.
        $external = DB::select()->from(self::$model_table_name)
                        ->where_open()
                        ->where('source', '=', self::$lastSource)
                        ->where('source_id', '=', $account['uid'])
                        ->where_close()
                        ->execute()
                        ->current();

        // Fetch account user.
        if (! $external )
        {
            list($external_id, $affected_rows) = DB::insert(self::$model_table_name)
                                                ->columns(array('source', 'source_id', 'source_data'))
                                                ->values(array(self::$lastSource, $account['uid'], serialize($account)))
                                                ->execute();

            return array(
                    'local'     => $external_id,
                    'external'  => $account['uid'],
                    'user_id'   => null,
                    'info'      => call_user_func(array(__CLASS__, $methodName), $account)
            );
        }

        return array(
            'local'     => $external['id'],
            'external'  => $external['source_id'],
            'user_id'   => $external['user_id'],
            'info'      => call_user_func(array(__CLASS__, $methodName), unserialize($external['source_data']))
        );
    }

    /**
     * Prepare account data of social network to standardized output.
     *
     * @param array $account
     * @return array
     */
    protected static function prepareAccountDataVkCom(array $account)
    {
        return array(
            'name'    => $account['first_name'],
            'last_name'     => $account['last_name'],
            'phone'         => (! empty($account['mobile_phone']) ? $account['mobile_phone'] : $account['home_phone'])
        );
    }
}
