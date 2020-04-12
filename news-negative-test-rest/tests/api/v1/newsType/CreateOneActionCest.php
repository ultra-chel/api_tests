<?php

declare(strict_types = 1);

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\newsType;

use Codeception\Util\HttpCode;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\NewsTypeTester;

/**
 * Класс тестирования REST API: создание новой сущности "Типы новостей".
 */
class CreateOneActionCest
{
    /**
     * Заголовки, указывающие на метод.
     *
     * @var array
     */
    public static $methodHeader = [
        'name'  => 'X-HTTP-Method-Override',
        'value' => 'CREATE',
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
                'id'         => 'integer',
                'name'       => 'string',
                'isDefault'  => 'boolean',
                'createDate' => 'string',
                'creatorId'  => 'integer|null',
                'updateDate' => 'string',
                'updaterId'  => 'integer|null',
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
     * Метод проверяет запрос создания типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function createNewsTypeWithOutAuth(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] 
        Запрос: создание сущности без авторизации. 
        Ожидаем: Ошибку с пояснением - Доступ запрещен.');
        $newsTypeData = [
            'name' => sqs('NewsTypeName'),
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

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
        $i->dontSeeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос создания типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function createNewsTypeCorrect(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType]
        Передаем: Запрос на создание сущности со сгенерированными корректными случайными данными.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = [
            'name' => sqs('Test newsType name'),
            'isDefault' => false,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($newsTypeData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $newsTypeData['id'],
                'name' => $newsTypeData['name'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный запрос создания типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createNewsTypeNameUnique(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле name:}
        Передаем: cуществующее в таблице значение поля.
        Ожидаем: ошибку с пояснением - Запись с таким значением уже существует в таблице.');
        $newsTypeData = [
            'name' => $i->createNewsType()['name'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'name' => 'Запись с таким значением уже существует в таблице.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос создания типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createNewsTypeNameNull(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле name:}
        Передаем: NULL. 
        Ожидаем: ошибку с пояснением - Необходимо заполнить «Name».');
        $newsTypeData = [
            'name' => null,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'name' => 'Необходимо заполнить «Name».',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->dontSeeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос создания типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createNewsTypeNameMaxPhysicLength(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле name:} тип varchar(255)
        Передаем: значение максимальной длины типа данных. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = [
            'name' => $i->getRandomString(255, DataTypesValueHelper::ASCII),
        ];
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($newsTypeData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'        => $newsTypeData['id'],
                'name'      => $newsTypeData['name'],
                'isDefault' => $newsTypeData['isDefault'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос создания типа новости. Кейсы: превышение максимального физического значения, asscii, non-ascii.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createNewsTypeNameOverMaxPhysicLength(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле name:} тип varchar(255)
        Передаем: значение превышающее максимальную длину типа данных. 
        Ожидаем: ошибка с пояснением - Значение «Name» должно содержать максимум 255 символов.');
        $newsTypeData = [
            'name' => $i->getRandomString(256, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

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
        $i->dontSeeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос создания типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createNewsTypeNameHieroglyphs(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле name:}
        Передаем: японские иероглифы. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::JP_HIEROGLYPHS),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($newsTypeData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'        => $newsTypeData['id'],
                'name'      => $newsTypeData['name'],
                'isDefault' => $newsTypeData['isDefault'],
            ],
        ]);
        $i->seeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос создания типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createNewsTypeNameToSpecialchars(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле name:}
        Передаем: строка с рандомным набором спецсимволов. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::CHARACTERS),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($newsTypeData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'        => $newsTypeData['id'],
                'name'      => $newsTypeData['name'],
                'isDefault' => $newsTypeData['isDefault'],
            ],
        ]);
        $i->seeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос создания типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     * @throws \Exception
     */
    public function createNewsTypeNameNonAscii(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле name:}
        Передаем: строка с рандомным набором символов non-ascii. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::NON_ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($newsTypeData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'        => $newsTypeData['id'],
                'name'      => $newsTypeData['name'],
                'isDefault' => $newsTypeData['isDefault'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос создания типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     * @throws \Exception
     */
    public function createNewsTypeNameToSqlQuery(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле name:}
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = [
            'name' => DataTypesValueHelper::SQL_QUERY,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($newsTypeData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'        => $newsTypeData['id'],
                'name'      => $newsTypeData['name'],
                'isDefault' => $newsTypeData['isDefault'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос создания типа новости c невалидным типом данных.
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
    public function createNewsTypeNameArray(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле name:} тип varchar(255)
        Передаем: значение типа array. 
        Ожидаем: ошибка с пояснением - Значение «Name» должно быть строкой.');
        $newsTypeData = [
            'name' => ['array'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

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
     * Метод проверяет запрос создания типа новости c валидным типом данных.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function createNewsTypeIsDefaultCorrect(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле isDefault:} тип tinyInt(1)
        Передаем: значение 1.
        Ожидаем: преобразование в boolean и положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = [
            'name'      => sqs('name'),
            'isDefault' => true,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($newsTypeData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'        => $newsTypeData['id'],
                'name'      => $newsTypeData['name'],
                'isDefault' => $newsTypeData['isDefault'],
            ],
        ]);
        $i->seeNumRecordsInNewsTypeTable(1, ['isDefault' => 1]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос создания типа новости c валидным типом данных.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createNewsTypeIsDefaultNull(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле isDefault:} тип tinyInt(1)
        Передаем: NULL.
        Ожидаем: преобразование в boolean и положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = [
            'name'      => sqs('name'),
            'isDefault' => null,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable([
            'name'      => sqs('name'),
            'isDefault' => 0,
        ]);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'        => $newsTypeData['id'],
                'name'      => $newsTypeData['name'],
                'isDefault' => $newsTypeData['isDefault'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос создания типа новости c валидным типом данных.
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
    public function createNewsTypeIsDefaultInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле isDefault:} тип tinyInt(1)
        Передаем: значение integer больше 1.
        Ожидаем: ошибку с пояснением - Значение «Is Default» должно быть равно «1» или «0».');
        $newsTypeData = [
            'name'      => sqs('name'),
            'isDefault' => random_int(2, 100),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

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
        $i->dontSeeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос создания типа новости c валидным типом данных.
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
    public function createNewsTypeIsDefaultSignedInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле isDefault:} тип tinyInt(1)
        Передаем: значение integer меньше 0. 
        Ожидаем: ошибку с пояснением - Значение «Is Default» должно быть равно «1» или «0».');
        $newsTypeData = [
            'name'      => sqs('name'),
            'isDefault' => random_int(- 100, - 1),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

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
        $i->dontSeeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос создания типа новости c валидным типом данных.
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
    public function createNewsTypeIsDefaultString(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле isDefault:} тип tinyInt(1)
        Передаем: значение типа string.
        Ожидаем: ошибку с пояснением - Значение «Is Default» должно быть равно «1» или «0».');
        $newsTypeData = [
            'name'      => sqs('name'),
            'isDefault' => sqs('test'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

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
        $i->dontSeeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос создания типа новости c валидным типом данных.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createNewsTypeIsDefaultArray(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле isDefault:} тип tinyInt(1)
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Is Default» должно быть равно «1» или «0».');
        $newsTypeData = [
            'name'      => sqs('name'),
            'isDefault' => ['test'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

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
     * Метод проверяет запрос создания типа новости c валидным типом данных.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function createNewsTypeIsDefaultUnique(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле isDefault:} тип tinyInt(1)
        Передаем: значение 1 с предусловием что в таблице уже есть запись со значением 1.
        Ожидаем: создание новой записи со значением 1, у всех других записей в таблице значение сбрасывается на 0.');
        $i->createNewsTypeList(4);
        $i->createNewsType(['isDefault' => 1]);
        $newsTypeData = [
            'name'      => sqs('name'),
            'isDefault' => 1,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $newsTypeData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($newsTypeData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'        => $newsTypeData['id'],
                'name'      => $newsTypeData['name'],
                'isDefault' => $newsTypeData['isDefault'],
            ],
        ]);
        $i->seeNumRecordsInNewsTypeTable(1, $newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }
}
