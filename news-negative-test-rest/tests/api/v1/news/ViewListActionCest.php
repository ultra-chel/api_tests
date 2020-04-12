<?php

declare(strict_types = 1);

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\news;

use Codeception\Util\HttpCode;
use Exception;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\NewsTester;
use function array_slice;
use function count;

/**
 * Класс тестирования REST API: удаление сущности "Новости".
 */
class ViewListActionCest
{
    /**
     * Заголовки, указывающие на метод.
     *
     * @var array
     */
    public static $methodHeader = [
        'name'  => 'X-HTTP-Method-Override',
        'value' => 'GET',
    ];

    /**
     * Путь до тестируемого экшена.
     *
     * @var string
     */
    public static $URI = 'v1/news';

    /**
     * Массив с типами ключей в ответах.
     *
     * @var array
     */
    protected $responseTypes = [
        'correct' => [
            'errors'  => 'array',
            'notices' => 'array',
            'data'    => [
                'list' => [
                    [
                        'id'            => 'integer',
                        'newsTypeId'    => 'integer',
                        'isActive'      => 'boolean|null',
                        'publicDate'    => 'string|null',
                        'publicTime'    => 'string|null',
                        'closeDateTime' => 'string|null',
                        'isMain'        => 'boolean|null',
                        'title'         => 'string',
                        'srcUrl'        => 'string|null',
                        'summary'       => 'string',
                        'body'          => 'string|null',
                        'createDate'    => 'string',
                        'creatorId'     => 'integer|null',
                        'updateDate'    => 'string',
                        'updaterId'     => 'integer|null',
                    ],
                ],
                'more' => 'boolean',
            ],
        ],
        'errors'  => [
            'errors'  => [
                [
                    'code'   => 'integer',
                    'title'  => 'string',
                    'detail' => 'string',
                    'data'   => 'array',
                ],
            ],
            'notices' => 'array',
            'data'    => 'array',
        ],
    ];

    /**
     * Метод для послетестовых де-инициализаций.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _after(NewsTester $i): void
    {
    }

    /**
     * Метод для предварительных инициализаций перед тестами.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @throws
     *
     * @return void
     */
    public function _before(NewsTester $i): void
    {
        $i->flushCache();
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function listNewsWithoutAuth(NewsTester $i): void
    {
        $i->wantTo('N [News]: 
        Передаем: Запрос на получение списка сущностей без авторизации.
        Ожидаем: ошибку с поянением - Доступ запрещен.');

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI);

        $i->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 403,
                    'title'  => 'Доступ запрещен',
                    'detail' => '',
                    'data'   => [],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws Exception
     *
     * @return void
     */
    public function listNewsPositive(NewsTester $i): void
    {
        $i->wantTo('P [News]: 
        Передаем: Запрос на получение списка сущностей без фильтров, лимитов и оффсетов.
        Ожидаем: список всех сущностей.');
        // Создание списка новостей.
        $i->createNews();
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI);

        $allNewsData = $i->grabManyFromNewsTable();
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $allNewsData,
                'more' => false,
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listNewsFilterNewsTypeIdCorrect(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: newsTypeId]: 
        Передаем: корректный ввод.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsData = $i->createNews();
        // Создание дополнительных новостей.
        $i->createNews();
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsTypeId' => $firstNewsData['newsTypeId'],
            ],
        ]);

        $filterNewsData = $i->grabOneFromNewsTable(['newsTypeId' => $firstNewsData['newsTypeId']]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $filterNewsData,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($filterNewsData) . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws Exception
     *
     * @return void
     */
    public function listNewsFilterNewsTypeIdNotExist(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: newsTypeId]: 
        Передаем: несуществующее значение идентификатора в таблице типов новостей.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» неверно.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsTypeId' => $i->getNotExistNewsTypeId(),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsTypeId' => 'Значение «News Type Id» неверно.',
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsFilterNewsTypeIdOverMaxPhysicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: newsTypeId]: тип int(11)
        Передаем: превышение максимального значения типа данных.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» не должно превышать 2147483647.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsTypeId' => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsTypeId' => 'Значение «News Type Id» не должно превышать 2147483647.',
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsFilterNewsTypeIdLessMinPhysicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: newsTypeId]:  тип int(11)
        Передаем: меньше минимального значение типа данных.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть не меньше -2147483648.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsTypeId' => DataTypesValueHelper::LESS_MIN_INT_SIGNED,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsTypeId' => 'Значение «News Type Id» должно быть не меньше -2147483648.',
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsFilterNewsTypeIdToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: newsTypeId]: 
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть целым числом.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsTypeId' => ['array'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsTypeId' => 'Значение «News Type Id» должно быть целым числом.',
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsFilterNewsTypeIdToString(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: newsTypeId]: 
        Передаем: значение типа string.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть целым числом.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsTypeId' => sqs('text'),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsTypeId' => 'Значение «News Type Id» должно быть целым числом.',
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsFilterNewsTypeIdToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: newsTypeId]: 
        Передаем: корректный sql запрос в строке.
        Ожидаем: ошибку с пояснением - Значение «News Type Id» должно быть целым числом.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsTypeId' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsTypeId' => 'Значение «News Type Id» должно быть целым числом.',
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterIsActiveTrue(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: IsActive]: тип tinyint(1)
        Передаем: значение 1.
        Ожидаем:  отфильтрованный список');
        $firstNewsData = $i->createNews(['isActive' => 1]);
        // Создание дополнительных новостей.
        $i->createNews();
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isActive' => 1,
            ],
        ]);

        $filterNewsData = $i->grabOneFromNewsTable(['isActive' => 1]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $filterNewsData,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($filterNewsData) . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterIsActiveOverMaxLogicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: isActive]: тип tinyint(1)
        Передаем: значение rand_int(2, 300).
        Ожидаем:  ошибку с пояснением - Значение «Is Active» должно быть равно «1» или «0».');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isActive' => random_int(2, 300),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isActive' => 'Значение «Is Active» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterIsActiveLessMinLogicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: isActive]: тип tinyint(1)
        Передаем: значение -1.
        Ожидаем:  ошибку с пояснением - Значение «Is Active» должно быть равно «1» или «0».');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isActive' => - 1,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isActive' => 'Значение «Is Active» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterIsActiveToString(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: isActive]: тип tinyint(1)
        Передаем: значение типа string.
        Ожидаем: ошибку с пояснением - Значение «Is Active» должно быть равно «1» или «0».');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isActive' => sqs('text'),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isActive' => 'Значение «Is Active» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterIsActiveToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: isActive]: тип tinyint(1)
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Is Active» должно быть равно «1» или «0».');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isActive' => ['array'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isActive' => 'Значение «Is Active» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterIsActiveToFloat(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: isActive]: тип tinyint(1)
        Передаем: значение типа float.
        Ожидаем: ошибку с пояснением - Значение «Is Active» должно быть равно «1» или «0».');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isActive' => 1.11,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isActive' => 'Значение «Is Active» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws Exception
     *
     * @return void
     */
    public function listNewsFilterPublicDateCorrect(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: publicDate]: тип date YYYY-MM-DD
        Передаем: корректная дата.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsData = $i->createNews();
        // Создание дополнительных новостей.
        $i->createNews();
        $i->createNews();
        // Формирование запроса
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicDate' => $firstNewsData['publicDate'],
            ],
        ]);
        // Проверки ответа АПИ
        $filterNewsData = $i->grabManyFromNewsTable(['publicDate' => $firstNewsData['publicDate']]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $filterNewsData,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($filterNewsData) . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterPublicDateNotExist(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: publicDate]: тип date YYYY-MM-DD
        Передаем: несуществующее в таблице значение.
        Ожидаем: пустой массив list за неимением совпадений.');
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicDate' => '2222-11-11',
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[0]');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterPublicDateToString(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: publicDate]: тип date YYYY-MM-DD
        Передаем: строку с рандомным набором ASCII символов.
        Ожидаем: ошибку с пояснением - Неверный формат значения «Public Date».');
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicDate' => sqs('text'),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicDate' => 'Неверный формат значения «Public Date».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterPublicDateToSql(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: publicDate]: тип date YYYY-MM-DD
        Передаем: корректный SQL запрос.
        Ожидаем: ошибку с пояснением - Неверный формат значения «Public Date».');
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicDate' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicDate' => 'Неверный формат значения «Public Date».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterPublicDateToInt(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: publicDate]: тип date YYYY-MM-DD
        Передаем: значение типа integer.
        Ожидаем: ошибку с пояснением - Неверный формат значения «Public Date».');
        $i->createNews(['publicDate' => '2222-01-06']);
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicDate' => 2222 - 01 - 06,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicDate' => 'Неверный формат значения «Public Date».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterPublicDateToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: publicDate]: тип date YYYY-MM-DD
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Неверный формат значения «Public Date».');
        $i->createNews(['publicDate' => '2222-01-06']);
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicDate' => ['2222-01-06'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicDate' => 'Неверный формат значения «Public Date».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws Exception
     *
     * @return void
     */
    public function listNewsFilterPublicTimeCorrect(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: publicTime]: тип time HH:MM:SS
        Передаем: корректное значение времени
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsData = $i->createNews();
        // Создание дополнительных новостей.
        $i->createNews();
        $i->createNews();
        // Формирование запроса
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicTime' => $firstNewsData['publicTime'],
            ],
        ]);
        // Проверки ответа АПИ
        $filterNewsData = $i->grabManyFromNewsTable(['publicTime' => $firstNewsData['publicTime']]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $filterNewsData,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($filterNewsData) . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterPublicTimeOverMaxHour(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: publicTime]: тип time HH:MM:SS
        Передаем: значение часа больше максимального значения.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');
        $i->createNews(['publicTime' => '00:00:00']);
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicTime' => '24:00:00',
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterPublicTimeToInt(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: publicTime]: тип time HH:MM:SS
        Передаем: значение типа int.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');

        $i->createNews(['publicTime' => '01:01:01']);
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicTime' => 010101,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterPublicTimeToString(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: publicTime]: тип time HH:MM:SS
        Передаем: строку с рандомным набором ASCII символов.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicTime' => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterPublicTimeToSql(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: publicTime]: тип time HH:MM:SS
        Передаем: корректный sql запрос.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicTime' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterPublicTimeToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: publicTime]: тип time HH:MM:SS
        Передаем: значение типа array.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Public Time».');

        $i->createNews(['publicTime' => '01:01:01']);
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'publicTime' => ['01:01:01'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'publicTime' => 'Неверный формат значения «Public Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws Exception
     *
     * @return void
     */
    public function listNewsCloseDateTimeCorrect(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: closeDateTime]: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: корректная дата и время.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsData = $i->createNews();
        // Создание дополнительных новостей.
        $i->createNews();
        $i->createNews();
        // Формирование запроса
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'closeDateTime' => $firstNewsData['closeDateTime'],
            ],
        ]);
        // Проверки ответа АПИ
        $filterNewsData = $i->grabManyFromNewsTable(['closeDateTime' => $firstNewsData['closeDateTime']]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $filterNewsData,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($filterNewsData) . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsCloseDateTimePatternWrongDateFormat(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: closeDateTime]: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: невалидный формат записи даты YYYY/MM/DD.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $i->createNews(['closeDateTime' => '2222-01-12 22:10:01']);
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'closeDateTime' => '2222/01/12 22:10:01',
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function listNewsCloseDateTimeToInt(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: closeDateTime]: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: значение типа int.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $i->createNews(['closeDateTime' => '2222-01-12 22:10:01']);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'closeDateTime' => random_int(1, 300),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function listNewsCloseDateTimeToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: closeDateTime]: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: значение типа array.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $i->createNews(['closeDateTime' => '2222-01-12 22:10:01']);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'closeDateTime' => ['2222-01-12 22:10:01'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsCloseDateTimeToString(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: closeDateTime]: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: строку с рандомным набором ASCII символов.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $i->createNews(['closeDateTime' => '2222-01-12 22:10:01']);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'closeDateTime' => $i->getRandomString(10, DataTypesValueHelper::ASCII),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsCloseDateTimeToSql(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: closeDateTime]: тип dateTime YYYY-MM-DD HH:MM:SS
        Передаем: корректный SQL запрос.
        Ожидаем: ошибка с пояснением - Неверный формат значения «Close Date Time».');
        $i->createNews(['closeDateTime' => '2222-01-12 22:10:01']);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'closeDateTime' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'closeDateTime' => 'Неверный формат значения «Close Date Time».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterIsMainTrue(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: IsMain]: тип tinyint(1)
        Передаем: значение 1.
        Ожидаем:  отфильтрованный список');
        $firstNewsData = $i->createNews(['isMain' => 1]);
        // Создание дополнительных новостей.
        $i->createNews();
        $i->createNews();
        // Формирование запроса
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isMain' => 1,
            ],
        ]);
        // Проверки ответа АПИ
        $filterNewsData = $i->grabOneFromNewsTable(['isMain' => 1]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $filterNewsData,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($filterNewsData) . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterIsMainOverMaxLogicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: isMain]: тип tinyint(1)
        Передаем: значение rand_int(2, 300).
        Ожидаем:  ошибку с пояснением - Значение «Is Main» должно быть равно «1» или «0».');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isMain' => random_int(2, 300),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isMain' => 'Значение «Is Main» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterIsMainToString(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: isMain]: тип tinyint(1)
        Передаем: значение типа string.
        Ожидаем: ошибку с пояснением - Значение «Is Main» должно быть равно «1» или «0».');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isMain' => sqs('text'),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isMain' => 'Значение «Is Main» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterIsMainToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: isMain]: тип tinyint(1)
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Is Main» должно быть равно «1» или «0».');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isMain' => ['array'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isMain' => 'Значение «Is Main» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterIsMainToFloat(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: isMain]: тип tinyint(1)
        Передаем: значение типа float.
        Ожидаем: ошибку с пояснением - Значение «Is Main» должно быть равно «1» или «0».');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isMain' => 1.11,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isMain' => 'Значение «Is Main» должно быть равно «1» или «0».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws Exception
     *
     * @return void
     */
    public function listNewsFilterTitleCorrect(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: title]: 
        Передаем: корректный ввод.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsData = $i->createNews();
        // Создание дополнительных новостей.
        $i->createNews();
        $i->createNews();
        // Формирование запроса
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'title' => $firstNewsData['title'],
            ],
        ]);
        // Проверки ответа АПИ
        $filterNewsData = $i->grabManyFromNewsTable(['title' => $firstNewsData['title']]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $filterNewsData,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($filterNewsData) . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsFilterTitleOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: title]: тип varchar(255)
        Передаем: кол-во символов превышает физическое ограничение типа данных.
        Ожидаем: ошибка с пояснением - Значение «Title» должно содержать максимум 255 символов.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'title' => $i->getRandomString(256),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'title' => 'Значение «Title» должно содержать максимум 255 символов.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterTitleToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: title]:
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Title» должно быть строкой.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'title' => ['array'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'title' => 'Значение «Title» должно быть строкой.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterTitleToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: title]:
        Передаем: корректный SQL запрос.
        Ожидаем: пустой массив list за неимением совпадений.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'title' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [],
                'more' => false,
            ],
        ]);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws Exception
     *
     * @return void
     */
    public function listNewsFilterSrcUrlCorrect(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: srcUrl]: 
        Передаем: корректный ввод.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsData = $i->createNews(['srcUrl' => sqs('srcUrl1')]);
        // Создание дополнительных новостей.
        $i->createNews();
        $i->createNews();
        // Формирование запроса
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'srcUrl' => $firstNewsData['srcUrl'],
            ],
        ]);
        // Проверки ответа АПИ
        $filterNewsData = $i->grabManyFromNewsTable(['srcUrl' => $firstNewsData['srcUrl']]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $filterNewsData,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($filterNewsData) . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsFilterSrcUrlOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: srcUrl]: тип varchar(255)
        Передаем: кол-во символов превышает физическое ограничение типа данных.
        Ожидаем: ошибка с пояснением - Значение «srcUrl» должно содержать максимум 255 символов.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'srcUrl' => $i->getRandomString(256),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'srcUrl' => 'Значение «Src Url» должно содержать максимум 255 символов.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterSrcUrlToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: srcUrl]:
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «SrcUrl» должно быть строкой.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'srcUrl' => ['array'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'srcUrl' => 'Значение «Src Url» должно быть строкой.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterSrcUrlToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: srcUrl]:
        Передаем: корректный SQL запрос.
        Ожидаем: пустой массив list за неимением совпадений.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'srcUrl' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [],
                'more' => false,
            ],
        ]);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws Exception
     *
     * @return void
     */
    public function listNewsFilterSummaryCorrect(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: summary]: 
        Передаем: корректный ввод.
        Ожидаем: список отфильтрованных сущностей.');

        $firstNewsData = $i->createNews(['summary' => sqs('summary1')]);
        // Создание дополнительных новостей.
        $i->createNews();
        $i->createNews();
        // Формирование запроса
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'summary' => $firstNewsData['summary'],
            ],
        ]);
        // Проверки ответа АПИ
        $filterNewsData = $i->grabManyFromNewsTable(['summary' => $firstNewsData['summary']]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $filterNewsData,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($filterNewsData) . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsFilterSummaryOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: summary]: тип text
        Передаем: кол-во символов превышает физическое ограничение типа.
        Ожидаем: ошибка с пояснением - Значение «Summary» должно содержать максимум 65 535 символов.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'summary' => $i->getRandomString(65536),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'summary' => 'Значение «Summary» должно содержать максимум 65 535 символов.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterSummaryToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: summary]:
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Summary» должно быть строкой.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'summary' => ['array'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'summary' => 'Значение «Summary» должно быть строкой.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterSummaryToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: summary]:
        Передаем: корректный SQL запрос.
        Ожидаем: пустой массив list за неимением совпадений.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'summary' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [],
                'more' => false,
            ],
        ]);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws Exception
     *
     * @return void
     */
    public function listNewsFilterBodyCorrect(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: body]: 
        Передаем: корректный ввод.
        Ожидаем: список отфильтрованных сущностей.');
        $firstNewsData = $i->createNews(['body' => sqs('body1')]);
        // Создание дополнительных новостей.
        $i->createNews();
        $i->createNews();
        // Формирование запроса
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'body' => $firstNewsData['body'],
            ],
        ]);
        // Проверки ответа АПИ
        $filterNewsData = $i->grabManyFromNewsTable(['body' => $firstNewsData['body']]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $filterNewsData,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($filterNewsData) . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsFilterBodyOverMaxPhysicLength(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: body]: тип text
        Передаем: кол-во символов превышает физическое ограничение типа.
        Ожидаем: ошибка с пояснением - Значение «body» должно содержать максимум 65 535 символов.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'body' => $i->getRandomString(65536),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'body' => 'Значение «Body» должно содержать максимум 65 535 символов.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterBodyToArray(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: body]:
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Body» должно быть строкой.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'body' => ['array'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'body' => 'Значение «Body» должно быть строкой.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterBodyToSqlQuery(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: body]:
        Передаем: корректный SQL запрос.
        Ожидаем: пустой массив list за неимением совпадений.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'body' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [],
                'more' => false,
            ],
        ]);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterLimit(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: limit]:
        Передаем: limit = 2.
        Ожидаем: первые два элемента списка');
        $firstNews  = $i->createNews(['body' => sqs('Test. First News')]);
        $secondNews = $i->createNews(['body' => sqs('Test. Second News')]);
        $thirdNews  = $i->createNews(['body' => sqs('Test. Third News')]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit' => 2,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstNews,
                    $secondNews,
                ],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$thirdNews],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[2]');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterLimitOverMaxPhysicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: limit]: тип bigInt_signed
        Передаем: значение превышающие максимальное физическое значение типа данных.
        Ожидаем: ошибку с пояснением - ');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit' => DataTypesValueHelper::OVER_MAX_BIGINT_SIGNED,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «limit» не должно превышать 9223372036854775809.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterLimitLessMinLogicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: limit]: тип bigInt_signed
        Передаем: значение меньше логического минимума типа данных.
        Ожидаем: весь список новостей');
        $i->createNews();
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit' => - 1,
            ],
        ]);

        $filterNewsData = $i->grabManyFromNewsTable();
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $filterNewsData,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($filterNewsData) . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterLimitToString(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: limit]:
        Передаем: строку с рандомными ASCII символами.
        Ожидаем: ошибку с пояснением - ');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit' => $i->getRandomString(14, DataTypesValueHelper::ASCII),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'limit' => 'Значение «limit» должно быть целым числом.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterLimitToSql(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: limit]:
        Передаем: корректный sql запрос.
        Ожидаем: ошибку с пояснением - ');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'limit' => 'Значение «limit» должно быть целым числом.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterOffset(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: offset]:
        Передаем: offset = 1.
        Ожидаем: список без первого элемента');
        $firstNews  = $i->createNews(['body' => sqs('Test. First News')]);
        $secondNews = $i->createNews(['body' => sqs('Test. Second News')]);
        $thirdNews  = $i->createNews(['body' => sqs('Test. Third News')]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => 1,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $secondNews,
                    $thirdNews,
                ],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $firstNews,
                'more' => false,
            ],
        ]);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterOffsetOverCountEntities(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: offset]:
        Передаем: значение int превышающие кол-во записей в таблице.
        Ожидаем: пустой массив list');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => $i->grabNumRecordsInCommentTable() + 1,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [],
                'more' => false,
            ],
        ]);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterOffsetOverMaxPhysicInt(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: offset]: тип bigInt_signed
        Передаем: значение int превышающее максимальное значение типа данных.
        Ожидаем: преобразование во float -> округление до целого десятичного числа, отфильтрованный список');
        $i->createNews();
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => DataTypesValueHelper::OVER_MAX_BIGINT_SIGNED,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [],
                'more' => false,
            ],
        ]);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterOffsetToSql(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: offset]:
        Передаем: корректный sql запрос.
        Ожидаем: ошибку с пояснением - ');
        $i->createNews();
        $i->createNews();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'limit' => 'Значение «offset» должно быть целым числом.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterOffsetToString(NewsTester $i): void
    {
        $i->wantTo('N [News] [Filter: offset]:
        Передаем: строка с рандомными ASCII символами.
        Ожидаем: ошибку с пояснением - ');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => $i->getRandomString(14, DataTypesValueHelper::ASCII),
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'limit' => 'Значение «offset» должно быть целым числом.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterOffsetLimit(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: offset & limit]:
        Передаем: offset = 1 limit = 1.
        Ожидаем: второй элемент списка.');

        $i->createNews();
        $i->createNews();
        $i->createNews();
        $dataAllNews       = $i->grabManyFromNewsTable();
        $viewDataList      = array_slice($dataAllNews, 1, 1);
        $notViewOffsetList = array_slice($dataAllNews, 0, 1);
        $notViewLimitList  = array_slice($dataAllNews, 2, 1);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit'  => 1,
                'offset' => 1,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $viewDataList,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $notViewOffsetList,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $notViewLimitList,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($viewDataList) . ']');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listNewsFilterPartTitleOffsetLimit(NewsTester $i): void
    {
        $i->wantTo('P [News] [Filter: offset & limit & title]:
        Передаем: корректный ввод в поле title части значения, так же offset = 2 и limit = 1.
        Ожидаем: третий элемент отфильтрованного списка');

        $i->createNews(['title' => sqs('Test. First News')]);
        $i->createNews(['title' => sqs('Second News')]);
        $i->createNews(['title' => sqs('Test. Third News')]);
        $i->createNews(['title' => sqs('Test. fourth News')]);
        $i->createNews(['title' => sqs('Test. fifth News')]);
        $dataFilterNews    = $i->grabManyFromNewsTable(['title like' => '%Test%']);
        $viewDataList      = array_slice($dataFilterNews, 2, 1);
        $notViewOffsetList = array_slice($dataFilterNews, 0, 2);
        $notViewLimitList  = array_slice($dataFilterNews, 3, 1);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'title'  => 'Test',
                'offset' => 2,
                'limit'  => 1,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $viewDataList,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $notViewOffsetList,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $notViewLimitList,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . count($viewDataList) . ']');
    }
}
