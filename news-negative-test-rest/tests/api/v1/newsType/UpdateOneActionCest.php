<?php

declare(strict_types = 1);

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\newsType;

use Codeception\Util\HttpCode;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\NewsTypeTester;
use yii;

/**
 * Класс тестирования REST API: обновление сущности "Типы новостей".
 */
class UpdateOneActionCest
{
    /**
     * Заголовки, указывающие на метод.
     *
     * @var array
     */
    public static $methodHeader = [
        'name'  => 'X-HTTP-Method-Override',
        'value' => 'PUT',
    ];

    /**
     * Путь до тестируемого экшена.
     *
     * @var string
     */
    public static $URI = 'v1/news-type/';

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
        Yii::$app->cache->flush();
        $i->createNewUser(sqs('NewsTypeTester'), [
            'password' => 123123,
            'roleId'   => 1,
        ]);
    }

    /**
     * Метод проверяет запрос обновления типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function updateNewsTypeWithOutAuth(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] 
        Запрос: обновление сущности без авторизации. 
        Ожидаем: Ошибку с пояснением - Доступ запрещен.');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'name'      => sqs('NewsTypeName'),
            'isDefault' => true,
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

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
        $i->seeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос обновления типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function updateNewsTypeCorrect(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType]
        Передаем: запрос на обновление сущности со сгенерированными корректными случайными данными.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'name'      => $i->getRandomString(1, DataTypesValueHelper::ASCII),
            'isDefault' => true,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

        $newsTypeData = $i->grabOneFromNewsTypeTable($request);
        $i->seeResponseCodeIs(HttpCode::OK);
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
     * Метод проверяет позитивный запрос обновления типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTypeNameUnique(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле name:}
        Передаем: cуществующее в таблице значение поля.
        Ожидаем: ошибку с пояснением - Запись с таким значением уже существует в таблице.');
        $firstNewsTypeData  = $i->createNewsType();
        $secondNewsTypeData = $i->createNewsType();
        $request            = [
            'name' => $secondNewsTypeData['name'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $firstNewsTypeData['id'], $request);

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
        $i->seeNewsTypeInTable($firstNewsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос обновление типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTypeNameMaxPhysicLength(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле name:} тип varchar(255)
        Передаем: значение максимальной длины типа данных. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'name' => $i->getRandomString(255, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($request);
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
     * Метод проверяет запрос обновление типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTypeNameNull(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле name:}
        Передаем: NULL. 
        Ожидаем: ошибку с пояснением - Необходимо заполнить «Name».');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'name' => null,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

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
        $i->seeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос обновление типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTypeNameOverMaxPhysicLength(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле name:} тип varchar(255)
        Передаем: значение превышающее максимальную длину типа данных. 
        Ожидаем: ошибка с пояснением - Значение «Name» должно содержать максимум 255 символов.');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'name' => $i->getRandomString(256, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

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
        $i->seeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос обновление типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTypeNameHieroglyphs(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле name:}
        Передаем: арабские иероглифы. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::ARABIC),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($request);
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
     * Метод проверяет запрос обновление типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTypeNameNonAscii(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле name:}
        Передаем: строка с рандомным набором символов non assci. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::NON_ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($request);
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
     * Метод проверяет запрос обновление типа новости.
     *
     * @param NewsTypeTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateNewsTypeNameToSpecialchars(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле name:}
        Передаем: строка с рандомным набором спецсимволов. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::CHARACTERS),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($request);
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
     * Метод проверяет запрос обновление типа новости c невалидным типом данных.
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
    public function updateNewsTypeNameToArray(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле name:} тип varchar(255)
        Передаем: значение типа array. 
        Ожидаем: ошибка с пояснением - Значение «Name» должно быть строкой.');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'name' => ['name'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

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
        $i->seeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос обновление типа новости c валидным типом данных.
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
    public function updateNewsTypeIsDefaultPatternTrueToFalse(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле isDefault} тип tinyInt(1)
        Передаем: значение 0 для сущности у которой isDefault = 1. 
        Ожидаем: ошибка с пояснением - ');
        $newsTypeData = $i->createNewsType(['isDefault' => 1]);
        $request      = [
            'isDefault' => 0,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'isDefault' => '',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeNewsTypeInTable($newsTypeData);
        $i->seeNumRecordsInNewsTypeTable(1, ['isDefault' => 1]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос обновление типа новости c валидным типом данных.
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
    public function updateNewsTypeIsDefaultPatternFalseToTrue(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле isDefault} тип tinyInt(1)
        Передаем: значение 1 для сущности у которой isDefault = 0. 
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = $i->createNewsType(['isDefault' => 0]);
        $request      = [
            'isDefault' => 1,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
        $newsTypeData = $i->grabOneFromNewsTypeTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'        => $newsTypeData['id'],
                'name'      => $newsTypeData['name'],
                'isDefault' => true,
            ],
        ]);
        $i->seeNumRecordsInNewsTypeTable(1, ['isDefault' => true]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос обновление типа новости c валидным типом данных.
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
    public function updateNewsTypeIsDefaultToNull(NewsTypeTester $i): void
    {
        $i->wantTo('P [NewsType] {поле isDefault:} тип tinyInt(1)
        Передаем: NULL где по предусловию isDefault = false.
        Ожидаем: преобразование в boolean и положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'isDefault' => null,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

        $i->seeResponseCodeIs(HttpCode::OK);
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
     * Метод проверяет запрос обновление типа новости c валидным типом данных.
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
    public function updateNewsTypeIsDefaultInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле isDefault:} тип tinyInt(1)
        Передаем: значение типа integer больше 1.
        Ожидаем: ошибку с пояснением - Значение «Is Default» должно быть равно «1» или «0».');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'isDefault' => random_int(2, 100),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

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
        $i->seeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос обновление типа новости c валидным типом данных.
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
    public function updateNewsTypeIsDefaultToString(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] {поле isDefault:} тип tinyInt(1)
        Передаем: значение типа string.
        Ожидаем: ошибку с пояснением - Значение «Is Default» должно быть равно «1» или «0».');
        $newsTypeData = $i->createNewsType();
        $request      = [
            'isDefault' => sqs('string'),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $newsTypeData['id'], $request);

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
        $i->seeNewsTypeInTable($newsTypeData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос обновление типа новости c валидным типом данных.
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
    public function updateNewsTypeNotExist(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] :
        Передаем: несуществующий id в таблице типа новости.
        Ожидаем: ошибка с пояснением - Сущность не найдена.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->getNotExistNewsTypeId(), ['name' => sqs('name')]);

        $i->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 404,
                    'title'  => 'Сущность не найдена',
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
     * Метод проверяет запрос обновление типа новости c валидным типом данных.
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
    public function updateNewsTypeOverMaxPhysicInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] :
        Передаем: значение id превышающее максимальное значение типа данных.
        Ожидаем: ошибка с пояснением - Значение «Id» не должно превышать 2147483647.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . DataTypesValueHelper::OVER_MAX_INT_SIGNED, ['name' => sqs('name')]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'id' => 'Значение «Id» не должно превышать 2147483647.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос обновление типа новости c валидным типом данных.
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
    public function updateNewsTypeLessMinLogicInt(NewsTypeTester $i): void
    {
        $i->wantTo('N [NewsType] :
        Передаем: значение id = 0.
        Ожидаем: ошибка с пояснением - Значение «Id» должно быть не меньше 1.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . 0, ['name' => sqs('name')]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'id' => 'Значение «Id» должно быть не меньше 1.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }
}
