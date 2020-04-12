<?php

declare(strict_types = 1);

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\tag;

use Codeception\Util\HttpCode;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\TagTester;

/**
 * Класс тестирования REST API: создание новой сущности "Тэги".
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
    public static $URI = 'v1/tag';

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
    public function createTagWithOutAuth(TagTester $i): void
    {
        $i->wantTo('N [Tag]: 
        Передаем: запрос на создание сущности без авторизации.
        Ожидаем: ошибка с пояснением - Доступ запрещен.');
        $tagData = [
            'name' => sqs('name'),
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $tagData);

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
        $i->dontSeeInTagTable($tagData);
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
    public function createTagWithoutRequiredFields(TagTester $i): void
    {
        $i->wantTo('N [Tag]: 
        Передаем: Запрос без указания параметров.
        Ожидаем: ошибка с пояснением - Необходимо заполнить «name».');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI);

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
    public function createTagNameCorrect(TagTester $i): void
    {
        $i->wantTo('P [Tag] {поле name}: 
        Передаем: Запрос на создание сущности со сгенерированными корректными случайными данными.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $tagData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($tagData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
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
    public function createTagNameUnique(TagTester $i): void
    {
        $i->wantTo('N [Tag] {поле name}: 
        Передаем: значение которое уже существует в таблице.
        Ожидаем: ошибка с пояснением - Запись с таким значением уже существует в таблице.');
        $tagData = [
            'name' => $i->createTag()['name'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $tagData);

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
     * Метод проверяет позитивный кейс.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function createTagNameMaxPhysicLength(TagTester $i): void
    {
        $i->wantTo('P [Tag] {поле name}: тип varchar(255)
        Передаем: максимальная длина значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = [
            'name' => $i->getRandomString(255, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $tagData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($tagData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
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
    public function createTagNameOverMaxPhysicLength(TagTester $i): void
    {
        $i->wantTo('N [Tag] {поле name}: тип varchar(255)
        Передаем: превышение максимальной длины значения типа данных.
        Ожидаем: ошибка с пояснением - Значение «name» должно содержать максимум 255 символов.');
        $tagData = [
            'name' => $i->getRandomString(256, DataTypesValueHelper::ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $tagData);

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
    public function createTagNameHieroglyphs(TagTester $i): void
    {
        $i->wantTo('P [Tag] {поле name}: тип varchar(255)
        Передаем: строка с набором рандомных японских иероглифоф.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::JP_HIEROGLYPHS),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $tagData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($tagData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
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
    public function createTagNameNonAscii(TagTester $i): void
    {
        $i->wantTo('P [Tag] {поле name}: тип varchar(255)
        Передаем: строка с набором рандомных non-ascii символов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::NON_ASCII),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $tagData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($tagData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
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
    public function createTagNameSpecialchars(TagTester $i): void
    {
        $i->wantTo('P [Tag] {поле name}: тип varchar(255)
        Передаем: строка с набором рандомных спецсимволов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = [
            'name' => $i->getRandomString(10, DataTypesValueHelper::CHARACTERS),
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $tagData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($tagData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
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
     * @throws
     *
     * @return void
     */
    public function createTagNameToArray(TagTester $i): void
    {
        $i->wantTo('N [Tag] {поле name}: тип varchar(255)
        Передаем: значение типа array.
        Ожидаем:  ошибку с пояснением - Значение «name» должно быть строкой.');
        $tagData = [
            'name' => ['name'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $tagData);

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
     * @throws
     *
     * @return void
     */
    public function createTagNameToSqlQuery(TagTester $i): void
    {
        $i->wantTo('N [Tag] {поле name}: тип varchar(255)
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $tagData = [
            'name' => DataTypesValueHelper::SQL_QUERY,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, $tagData);

        $i->seeResponseCodeIs(HttpCode::OK);
        $tagData = $i->grabOneFromTagTable($tagData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'id'   => $tagData['id'],
                'name' => $tagData['name'],
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }
}
