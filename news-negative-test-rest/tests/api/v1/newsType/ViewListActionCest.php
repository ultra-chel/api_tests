<?php

declare(strict_types = 1);

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\newsType;

use Codeception\Util\HttpCode;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\NewsTypeTester;

/**
 * Класс тестирования REST API: удаление сущности "Типы новостей".
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
    public static $URI = 'v1/news-type';

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
                        'id'         => 'integer',
                        'name'       => 'string',
                        'isDefault'  => 'boolean',
                        'createDate' => 'string',
                        'creatorId'  => 'integer|null',
                        'updateDate' => 'string',
                        'updaterId'  => 'integer|null',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _after(NewsTypeTester $i): void
    {
    }

    /**
     * Метод для предварительных инициализаций перед тестами.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _before(NewsTypeTester $i): void
    {
        $i->flushCache();
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function listNewsTypeWithoutAuth(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType]: 
        Передаем: Запрос на получение списка сущностей без авторизации.
        Ожидаем: Ошибку с пояcнением - Доступ запрещен.');

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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listNewsTypePositive(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType]: 
        Передаем: Запрос на получение списка сущностей без фильтров, лимитов и оффсетов.
        Ожидаем: список всех сущностей.');
        $firstNewsType  = $i->createNewsType(['name' => sqs('Test. First newsType')]);
        $secondNewsType = $i->createNewsType(['name' => sqs('Test. Second newsType')]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstNewsType,
                    $secondNewsType,
                ],
                'more' => false,
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listNewsTypeFilterNameCorrect(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] [Filter: Name]: 
        Передаем: корректный ввод существующего значения.
        Ожидаем: список отфильтрованных сущностей.');
        $i->createNewsType(['name' => sqs('Test. First newsType')]);
        $secondNewsType = $i->createNewsType(['name' => sqs('Test. Second newsType')]);
        $i->createNewsType(['name' => sqs('Test. Third newsType')]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'name' => $secondNewsType['name'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$secondNewsType],
                'more' => false,
            ],
        ]);
        $countResult = $i->grabNumRecordsFromNewsTypeTable(['name' => $secondNewsType['name']]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countResult . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listNewsTypeFilterNameOverMaxPhysicLength(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: Name]: тип varchar(255)
        Передаем: кол-во символов превышает максимальную длину типа данных.
        Ожидаем: ошибку с пояснением - Значение «Name» должно содержать максимум 255 символов.');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'name' => $i->getRandomString(256),
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
                        'name' => 'Значение «Name» должно содержать максимум 255 символов.',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterNameToArray(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: Name]:
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Name» должно быть строкой.');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'name' => ['name'],
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
                        'name' => 'Значение «Name» должно быть строкой.',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterNameToSqlQuery(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: Name]:
        Передаем: корректный SQL запрос.
        Ожидаем: пустой массив list за неимением совпадений.');
        $partName     = substr(DataTypesValueHelper::SQL_QUERY, 0, 3);
        $newsTypeData = $i->createNewsType(['name' => $partName]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'name' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $newsTypeData,
                'more' => false,
            ],
        ]);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterIsDefaultCorrect(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] [Filter: IsDefault]:
        Передаем: значение 1.
        Ожидаем:  отфильтрованный список');
        $i->createNewsType(['isDefault' => 0]);
        $secondNewsType = $i->createNewsType(['isDefault' => true]);
        $i->createNewsType(['isDefault' => 0]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isDefault' => 1,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$secondNewsType],
                'more' => false,
            ],
        ]);
        $countResult = $i->grabNumRecordsFromNewsTypeTable(['isDefault' => true]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countResult . ']');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterIsDefaultInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: IsDefault]: тип tinyint(1)
        Передаем: random_int(2,300).
        Ожидаем: ошибку с пояснением - Значение «Is Default» должно быть равно «1» или «0».');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isDefault' => random_int(2, 300),
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
                        'isDefault' => 'Значение «Is Default» должно быть равно «1» или «0».',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterIsDefaultSignedInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: IsDefault]: тип tinyint(1)
        Передаем: значение -1.
        Ожидаем: ошибку с пояснением - Значение «Is Default» должно быть равно «1» или «0».');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isDefault' => - 1,
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
                        'isDefault' => 'Значение «Is Default» должно быть равно «1» или «0».',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterIsDefaultToString(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] [Filter: IsDefault]: тип tinyint(1)
        Передаем: значение типа string.
        Ожидаем: ошибку с пояснением - Значение «Is Default» должно быть равно «1» или «0».');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isDefault' => sqs('Text'),
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
                        'isDefault' => 'Значение «Is Default» должно быть равно «1» или «0».',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterIsDefaultToArray(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: IsDefault]: тип tinyint(1)
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Is Default» должно быть равно «1» или «0».');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isDefault' => ['Text'],
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
                        'isDefault' => 'Значение «Is Default» должно быть равно «1» или «0».',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterIsDefaultToEmptyString(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: IsDefault]: тип tinyint(1)
        Передаем: пустую строку.
        Ожидаем: преобразование в bool и получение отфильтрованного списка');
        $firstNewsType  = $i->createNewsType(['isDefault' => false]);
        $secondNewsType = $i->createNewsType(['isDefault' => true]);
        $thirdNewsType  = $i->createNewsType(['isDefault' => false]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'isDefault' => '',
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstNewsType,
                    $thirdNewsType,
                ],
                'more' => false,
            ],
        ]);
        $countResult = $i->grabNumRecordsFromNewsTypeTable(['isDefault' => false]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countResult . ']');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterLimit(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] [Filter: limit]:
        Передаем: limit = 2.
        Ожидаем: первые два элемента списка');
        $firstNewsType  = $i->createNewsType(['isDefault' => false]);
        $secondNewsType = $i->createNewsType(['isDefault' => true]);
        $thirdNewsType  = $i->createNewsType(['isDefault' => false]);
        $limitNumber    = 2;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit' => $limitNumber,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstNewsType,
                    $secondNewsType,
                ],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $thirdNewsType,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $limitNumber . ']');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterLimitOverMaxPhysicInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: limit]: тип bigInt_signed
        Передаем: значение int превышающие физическое ограничение типа данных.
        Ожидаем: ошибку с пояснением - .');
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
                        'limit' => 'Значение «limit» должно быть не больше 9223372036854775809',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterLimitLessMinPhysicInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: limit]:
        Передаем: значение int меньше минимального значения типа данных.
        Ожидаем: ошибку с пояснением - .');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit' => DataTypesValueHelper::LESS_MIN_BIGINT_SIGNED,
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
                        'limit' => 'Значение «limit» должно быть не меньше - 9223372036854775809',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterLimitToString(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: limit]:
        Передаем: строка с рандомным набором ASCII символов.
        Ожидаем: ошибку с пояснением - ');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit' => sqs('text'),
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
                        'limit' => 'Значение «limit» должно быть типом int',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterOffset(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] [Filter: offset]:
        Передаем: offset = 1.
        Ожидаем: список без первого элемента');
        $firstNewsType  = $i->createNewsType(['isDefault' => false]);
        $secondNewsType = $i->createNewsType(['isDefault' => true]);
        $thirdNewsType  = $i->createNewsType(['isDefault' => false]);
        $offsetNumber   = 1;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => $offsetNumber,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $secondNewsType,
                    $thirdNewsType,
                ],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $firstNewsType,
                'more' => false,
            ],
        ]);
        $countResult = $i->grabNumRecordsFromNewsTypeTable() - $offsetNumber;
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countResult . ']');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterOffsetOverCountEntities(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: offset]:
        Передаем: значение int превышающие кол-во записей в БД.
        Ожидаем: пустой массив list');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => $i->grabNumRecordsFromNewsTypeTable() + 1,
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterOffsetOverMaxPhysicInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: offset]: тип bigInt_signed
        Передаем: значение int превышающие максимальное значение типа данных.
        Ожидаем: ошибка с пояснением - ');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => DataTypesValueHelper::OVER_MAX_BIGINT_SIGNED,
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
                        'offset' => 'Значение «offset» должно быть не больше 9223372036854775809',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterOffsetLessMinPhysicInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: offset]: тип bigInt_signed
        Передаем: значение int меньше минимального значения типа данных.
        Ожидаем: ошибка с пояснением - ');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => DataTypesValueHelper::LESS_MIN_BIGINT_SIGNED,
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
                        'offset' => 'Значение «offset» должно быть не менее - 9223372036854775809',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterOffsetToNull(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: offset]:
        Передаем: NULL.
        Ожидаем: список всех типов новостей.');
        $firstNewsType  = $i->createNewsType(['isDefault' => false]);
        $secondNewsType = $i->createNewsType(['isDefault' => true]);
        $thirdNewsType  = $i->createNewsType(['isDefault' => false]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => null,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstNewsType,
                    $secondNewsType,
                    $thirdNewsType,
                ],
                'more' => false,
            ],
        ]);
        $countResult = $i->grabNumRecordsFromNewsTypeTable();
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countResult . ']');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterOffsetToString(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] [Filter: offset]:
        Передаем: строка с рандомным набором ASCII символов.
        Ожидаем: ошибку с пояснением - .');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => sqs('text'),
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
                        'offset' => 'Значение «offset» должно быть типом int',
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
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterOffsetLimit(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] [Filter: offset & limit]:
        Передаем: offset = 1 limit = 2.
        Ожидаем: второй и третий элемент списка.');
        $firstNewsType  = $i->createNewsType(['isDefault' => false]);
        $secondNewsType = $i->createNewsType(['isDefault' => true]);
        $thirdNewsType  = $i->createNewsType(['isDefault' => false]);
        $fourthNewsType = $i->createNewsType(['isDefault' => false]);
        $countLimit     = 2;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit'  => $countLimit,
                'offset' => 1,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $secondNewsType,
                    $thirdNewsType,
                ],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $firstNewsType,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $fourthNewsType,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countLimit . ']');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listNewsTypeFilterPartNameOffsetLimit(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] [Filter: offset & limit & name]:
        Передаем: корректный ввод в поле name части значения, так же offset = 2 и limit = 1.
        Ожидаем: второй и третий элемент отфильтрованного списка');
        $firstNewsType  = $i->createNewsType(['name' => 'Test first News type']);
        $secondNewsType = $i->createNewsType(['name' => 'second News type']);
        $thirdNewsType  = $i->createNewsType(['name' => 'Test third News type']);
        $fourthNewsType = $i->createNewsType(['name' => 'Test fourth News type']);
        $countLimit     = 2;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'name'   => 'Test',
                'offset' => 1,
                'limit'  => $countLimit,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $thirdNewsType,
                    $fourthNewsType,
                ],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $firstNewsType,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $secondNewsType,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countLimit . ']');
    }
}
