<?php
class Model_Filter extends ORM {

    const CLOTH_BIG_TYPE = 1954; // фильтр По больщому типу в одежде, по нему распадаются подкатегории одежды
    const TOYS_BIG_TYPE = 2228; // фильтр По больщому типу в игрушках
    const MUMS_BIG_TYPE = 2099; // фильтр Категорий для всё для мам
    const CARE_BIG_TYPE = 2102; // фильтр Категорий для Косметики
    const FEED_BIG_TYPE = 2086; // фильтр Вид (средства для кормления)
    const HEALTH_BIG_TYPE = 1985; // фильтр Подкатегория в Здоровье малыша

    const PURE_SOSTAV = 2068; // фильтр состава в пюре

    const STROLLER_WEIGHT = 2329;
    const STROLLER_SHASSI = 2324;

    const WEIGHT = 100; // искусственный фильтр по весу в подгузах
    const TASTE = 101; // искусственный фильтр  - только выбранные вкусы

    protected $_table_name = 'z_filter';

    protected  $_has_many = [
        'values' => ['model' => 'filter_value', 'foreign_key' => 'filter_id'],
    ];

    protected $_belongs_to = [
        'section' => ['model' => 'section', 'foreign_key' => 'section_id'],
    ];

    protected $_table_columns = [
        'id' => '', 'code' => '', 'name' => '', 'sort' => '', 'section_id' => '',  'bind_to' => ''
    ];

    /**
     * Зависимости вложенных фильтров от брендов или значений фильтров
     * @var array
     */
    private static $_show_on = [ // fid => brand_id | value_id
        // подгузники
        2200 => 51566, // коллекция памперс
        2203 => 51516, // коллекция либеро
        2244 => 51496, // коллекция хагис
        // чай
        2092 => 20009, // фрукты
        2245 => 20007, // травы
        2246 => 20008, // ягоды
        // молочная продукция
        2247 => 20070, // фрукты
        2248 => 20041, // ягоды
        2249 => 20043, // овощи
        // пюре
        2070 => 20132, // мясо
        2073 => 20136, // птица
        2075 => 20139, // фрукты
        2225 => 20140, // рыба
        2072 => 20137, // овощи
        2272 => 20142, // ягоды
        // каши
        2268 => 20173, // фрукты
        2059 => 20179, // овощи
        2270 => 20174, // ягоды
        2269 => 20176, // травы
        // соки и напитки
        2084 => 20286, // фрукты
        2079 => 20292, // овощи
        2085 => 20287, // ягоды
        //  для пап
        2122 => 193286, // коллекция gilette
        2280 => 20380, // ср-ва для бритья - до и после
        2276 => 20357, // число кассет
        2278 => 20361, // тип дезодортанта
        2281 => 20363, // тип шампунь
        // косметика и уход
        2295 => 20526, // тип зубной щетки
        2299 => 20555, // число бритв в кассете
        2298 => 20545, // прокладки
        2297 => 20549, // вид бритвы
        2104 => 20432, // тип спа-ухода
        // хозтовары и инвентарь
        2289 => 20704, // слои полотенец
        2287 => 20703, // слои т.бумаги
        2288 => 20702, // швабры тип
        2285 => 20696, // перчатки размер
        // детская бакалея
        2317 => 20725, //вид десерта
        2316 => 20727, //вид диет пит
        // ежедневный уход
        2000 => 16905, // вкус зубной пасты
        // принадлежности для купания
        2007 => 21054, // вид аксессуаров
    ];

    /**
     * Список значений фильтров которые надо в поисковых запросах не смешивать с другими (логика AND)
     * @var array
     */
    private static $_and_val = [
        // пюре
        20138, // без крахмала
        20135, // однокомпонент
        20134, // без соли
        20141, // органик
        20133, // без сахара
        20143, // халяль
        // каши
        20149, // низкоаллергенная
        20150, // без молока
        20152, // без глютена
        20151, // без сахара
        20153, // без соли
        20162, // органик
    ];

    /**
     * Фильтры вкусов в пюре, нужны для фильтра "только выбранные вкусы"
     */
    public static $_taste = [
        2070, // мясо
        2073, // птица
        2075, // фрукты
        2225, // рыба
        2072, // овощи
        2272, // ягоды
    ];

    /**
     * Порядок брендов в категории порожденной значением фильтра
     */
    public static $_brand_order = [
        // для ванны и душа - CAMAY, Kokubo, Marna Cosmetics, Naomi, Sodasan, Weleda, Морская сказка
        20422 => [193281, 193307, 192914, 193277, 192780, 74341, 192738],
        // уход за лицом - Cettua, FREEMAN, Japan Gals, Naomi, Olay, Roland, Weleda
        20423 => [193260, 193263, 183758, 193277, 193291, 193433, 74341],
        // уход за волосами - Cocopalm, Head & Shoulders, Lion, Naomi, Reveur, Voloute, Weleda
        20428 => [192714, 193287, 51520, 193277, 193434, 193435, 74341],
        // уход за руками - Cettua, Daiichi, ECOVER, Frosch, Mama Com.fort, Naomi, Saraya, Weleda
        20426 => [193260, 51454, 76140, 193258, 160871, 193277, 51593, 74341],
        // уход за ногами - CAMAY, KAI, Kotex, Mama Com.fort, Procter & Gamble, Sanosan, Satin Care, Venus
        20427 => [193281, 193438, 51511, 160871, 51576, 51592, 193292, 193293],
    ];

    /**
     * Интервалы для Веса колясок
     */
    public static $_stroller_weight = [
        1 => [
            'min' => 1,
            'max' => 6,
            'name' => 'до 6 кг',
        ],
        2 => [
            'min' => 6,
            'max' => 8,
            'name' => 'от 6 до 8 кг',
        ],
        3 => [
            'min' => 8,
            'max' => 10,
            'name' => 'от 8 до 10 кг',
        ],
        4 => [
            'min' => 10,
            'max' => 15,
            'name' => 'от 10 до 15 кг',
        ],
        5 => [
            'min' => 15,
            'max' => 100,
            'name' => 'от 15 кг',
        ]
    ];

    /**
     * Интервалы для Шасси колясок
     */
    public static $_stroller_shassi = [
        1 => [
            'min' => 1,
            'max' => 40,
            'name' => 'до 40 см',
        ],
        2 => [
            'min' => 41,
            'max' => 50,
            'name' => 'с 41 до 50 см',
        ],
        3 => [
            'min' => 51,
            'max' => 60,
            'name' => 'с 51 до 60 см',
        ],
        4 => [
            'min' => 61,
            'max' => 100,
            'name' => 'от 61 см',
        ],
    ];

    /**
     * @param int $fid
     * @return int id фильтра или бренда к которому привязан запрошенный фильтр
     */
    static function binded_to($fid)
    {
        if ( ! empty(self::$_show_on[$fid])) return self::$_show_on[$fid];
        return FALSE;
    }

    /**
     * Проверить значение фильтра на тип and
     * @param $id
     * @return bool
     */
    static function and_val($id)
    {
        return in_array($id, self::$_and_val);
    }

    /**
     * Большие фильтры - скрываются из меню, по некоторым есть эмуляция 3-го уровня меню
     */
    static function big($fid)
    {
        return in_array($fid, [self::CLOTH_BIG_TYPE, self::TOYS_BIG_TYPE, self::MUMS_BIG_TYPE, self::CARE_BIG_TYPE, self::FEED_BIG_TYPE, self::HEALTH_BIG_TYPE]);
    }

    /**
     * фильтры вкусов в пюре - нужно для "только выбранные вкусы"
     */
    static function taste($id)
    {
        return in_array($id, self::$_taste);
    }

    /**
     * фильтры вкусов в пюре - нужно для "только выбранные вкусы"
     */
    static function taste_on($params)
    {
        if (empty($params['f'])) return FALSE;

        $taste = 0;
        foreach($params['f'] as $fid => $vals) {
            if (self::taste($fid)) {
                $taste += count($vals);
            }
        }
        return $taste > 1;
    }
}
