<?php

declare(strict_types = 1);

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\tag;

use Codeception\Util\HttpCode;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\TagTester;

/**
 * Класс тестирования REST API: удаление сущности "Тэги".
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
    public static $URI = 'v1/tag/';

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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _after(TagTester $i): void
    {
    }

    /**
     * Метод для предварительных инициализаций перед тестами.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _before(TagTester $i): void
    {
        $i->flushCache();
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function listTagWithoutAuth(TagTester $i): void
    {
        $i->wantTo('N [Tag]: 
        Передаем: Запрос на получение списка сущностей без авторизации.
        Ожидаем: Ошибку с поянением - Доступ запрещен.');

        $i->haveHttpHeader('X-HTTP-Method-Override', 'GET');
        $i->sendPOST('v1/tag');

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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listTagPositive(TagTester $i): void
    {
        $i->wantTo('P [Tag]: 
        Передаем: Запрос на получение списка сущностей без фильтров, лимитов и оффсетов.
        Ожидаем: список всех сущностей.');
        $firstTag  = $i->createTag(['name' => sqs('Test. First Tag')]);
        $secondTag = $i->createTag(['name' => sqs('Test. Second Tag')]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstTag,
                    $secondTag,
                ],
                'more' => false,
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listTagFilterNameCorrect(TagTester $i): void
    {
        $i->wantTo('P [Tag] {Filter: name}:
        Передаем: корректное значение.
        Ожидаем: список отфильтрованных сущностей.');
        $i->createTag(['name' => sqs('Test. First Tag')]);
        $secondTag = $i->createTag(['name' => sqs('Test. Second Tag')]);
        $i->createTag(['name' => sqs('Test. Third Tag')]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'name' => $secondTag['name'],
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$secondTag],
                'more' => false,
            ],
        ]);
        $countResult = $i->grabNumRecordsFromTagTable(['name' => $secondTag['name']]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countResult . ']');
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listTagFilterNameOverMaxPhysicLength(TagTester $i): void
    {
        $i->wantTo('N [Tag] {Filter: name}: тип varchar(255)
        Передаем: кол-во символов превышает физическое ограничение типа данных.
        Ожидаем: ошибка с пояснением - Значение «name» должно содержать максимум 255 символов.');
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listTagFilterNameToNull(TagTester $i): void
    {
        $i->wantTo('P [Tag] {Filter: name}: тип varchar(255)
        Передаем: NULL .
        Ожидаем: список всех тэгов.');
        $firstTag  = $i->createTag(['name' => sqs('Test. First Tag')]);
        $secondTag = $i->createTag(['name' => sqs('Test. Second Tag')]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'name' => null,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstTag,
                    $secondTag,
                ],
                'more' => false,
            ],
        ]);
        $countResult = $i->grabNumRecordsFromTagTable();
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countResult . ']');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listTagFilterNameToArray(TagTester $i): void
    {
        $i->wantTo('N [Tag] {Filter: name}:
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «name» должно быть строкой.');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'name' => ['array'],
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listTagFilterNameToSqlQuery(TagTester $i): void
    {
        $i->wantTo('N [Tag] {Filter: name}:
        Передаем: корректный SQL запрос.
        Ожидаем: пустой массив list за неимением совпадений.');
        $partName = substr(DataTypesValueHelper::SQL_QUERY, 0, 3);
        $tagData  = $i->createNewsType(['name' => $partName]);

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
                'list' => $tagData,
                'more' => false,
            ],
        ]);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listTagFilterLimit(TagTester $i): void
    {
        $i->wantTo('P [Tag] [Filter: limit]:
        Передаем: limit = 2.
        Ожидаем: первые два элемента списка');
        $firstTag    = $i->createTag(['name' => sqs('Test. First Tag')]);
        $secondTag   = $i->createTag(['name' => sqs('Test. Second Tag')]);
        $thirdTag    = $i->createTag(['name' => sqs('Test. third Tag')]);
        $limitNumber = 2;

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
                    $firstTag,
                    $secondTag,
                ],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $thirdTag,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $limitNumber . ']');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listTagFilterLimitOverMaxPhysicInt(TagTester $i): void
    {
        $i->wantTo('N [Tag] [Filter: limit]:  тип bigInt_signed
        Передаем: значение int превышающее физический максимум типа данных.
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listTagFilterLimitLessMinPhysicInt(TagTester $i): void
    {
        $i->wantTo('N [Tag] [Filter: limit]:  тип bigInt_signed
        Передаем: значение int меньше физического минимума типа данных.
        Ожидаем: ошибку с пояснением - ');
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listTagFilterLimitToString(TagTester $i): void
    {
        $i->wantTo('N [Tag] [Filter: limit]:
        Передаем: строку с рандомным набором ASCII символов.
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listTagFilterOffset(TagTester $i): void
    {
        $i->wantTo('P [Tag] [Filter: offset]:
        Передаем: offset = 1.
        Ожидаем: список без первого элемента');
        $firstTag     = $i->createTag(['name' => sqs('Test. First Tag')]);
        $secondTag    = $i->createTag(['name' => sqs('Test. Second Tag')]);
        $thirdTag     = $i->createTag(['name' => sqs('Test. third Tag')]);
        $offsetNumber = 1;

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
                    $secondTag,
                    $thirdTag,
                ],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$firstTag],
                'more' => false,
            ],
        ]);
        $countResult = $i->grabNumRecordsFromTagTable() - $offsetNumber;
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countResult . ']');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listTagFilterOffsetOverCountEntities(TagTester $i): void
    {
        $i->wantTo('N [Tag] [Filter: offset]:
        Передаем: значение int превышающее кол-во записей в БД.
        Ожидаем: пустой массив list');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => $i->grabNumRecordsFromTagTable() + 1,
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listTagFilterOffsetOverMaxPhysicInt(TagTester $i): void
    {
        $i->wantTo('N [Tag] [Filter: offset]:
        Передаем: значение int превышающее физический максимум типа данных.
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listTagFilterOffsetLessMinPhysicInt(TagTester $i): void
    {
        $i->wantTo('N [Tag] [Filter: offset]:
        Передаем: значение int меньше физического минимума типа данных.
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listTagFilterOffsetToString(TagTester $i): void
    {
        $i->wantTo('N [Tag] [Filter: offset]:
        Передаем: строку с рандомным набором ASCII символов.
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
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listTagFilterOffsetLimit(TagTester $i): void
    {
        $i->wantTo('P [Tag] [Filter: offset & limit]:
        Передаем: offset = 1 limit = 2.
        Ожидаем: второй и третий элемент списка.');
        $firstTag   = $i->createTag(['name' => sqs('Test. First Tag')]);
        $secondTag  = $i->createTag(['name' => sqs('Test. Second Tag')]);
        $thirdTag   = $i->createTag(['name' => sqs('Test. third Tag')]);
        $fourtTag   = $i->createTag(['name' => sqs('Test. fourt Tag')]);
        $countLimit = 2;

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
                    $secondTag,
                    $thirdTag,
                ],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $firstTag,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $fourtTag,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countLimit . ']');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listTagFilterPartNameOffsetLimit(TagTester $i): void
    {
        $i->wantTo('P [Tag] [Filter: offset & limit & name]:
        Передаем: корректный ввод в поле name части значения, так же offset = 2 и limit = 1.
        Ожидаем: третий элемент отфильтрованного списка');
        $firstTag   = $i->createTag(['name' => sqs('Test. First Tag')]);
        $secondTag  = $i->createTag(['name' => sqs('Second Tag')]);
        $thirdTag   = $i->createTag(['name' => sqs('Test. third Tag')]);
        $fourtTag   = $i->createTag(['name' => sqs('Test. fourt Tag')]);
        $countLimit = 1;

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'name'   => 'Test',
                'offset' => 2,
                'limit'  => $countLimit,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$fourtTag],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstTag,
                    $secondTag,
                    $thirdTag,
                ],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[' . $countLimit . ']');
    }
}
