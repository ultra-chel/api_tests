<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\comment;

use Codeception\Util\HttpCode;
use Exception;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\CommentTester;

/**
 * Класс тестирования REST API: обновление сущности "Комментарии к новостям".
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
    public static $URI = 'v1/comment/';

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
                'newsId'     => 'integer',
                'body'       => 'string',
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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _after(CommentTester $i): void
    {
    }

    /**
     * Метод для предварительных инициализаций перед тестами.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @return void
     */
    public function _before(CommentTester $i): void
    {
        $i->flushCache();
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function updateCommentWithOutAuth(CommentTester $i): void
    {
        $i->wantTo('N [Comment]: 
        Передаем: запрос на обновление сущности без авторизации.
        Ожидаем: ошибка с пояснением - Доступ запрещен.');
        $preconditionData = $i->preconditionForComment();
        $commentData      = $i->createComment();
        $request          = [
            'body'   => sqs('Test comment body'),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
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
        $i->seeInCommentTable($commentData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function updateCommentCorrect(CommentTester $i): void
    {
        $i->wantTo('P [Comment]: 
        Передаем: запрос на обновление сущности со сгенерированными корректными случайными данными.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForComment();
        $commentData      = $i->createComment();
        $request          = [
            'body'   => sqs('Test comment body'),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newCommentData = array_merge(['id' => $commentData['id']], $request);
        $i->seeInCommentTable($newCommentData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $newCommentData,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateCommentWithoutRequiredFields(CommentTester $i): void
    {
        $i->wantTo('N [Comment]: 
        Передаем: запрос без указания параметров.
        Ожидаем: ошибку с пояснением о необходимости заполнить обязательные поля.');
        $commentData = $i->createComment();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], []);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Необходимо заполнить «News Id».',
                    ],
                ],
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'body' => 'Необходимо заполнить «Body».',
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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateCommentBodyMaxPhysicLength(CommentTester $i): void
    {
        $i->wantTo('P [Comment] {поле body}: тип text
        Передаем: максимальная длина значения типа данных.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $preconditionData = $i->preconditionForComment();
        $commentData      = $i->createComment();
        $request          = [
            'body'   => $i->getRandomString(65535, DataTypesValueHelper::ASCII),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeInCommentTable([
            'id'        => $commentData['id'],
            'body like' => mb_substr($request['body'], 0, 50, 'UTF-8') . '%',
            'newsId'    => $request['newsId'],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateCommentBodyOverMaxPhysicLength(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле body}: тип text
        Передаем: превышение максимальной длины значения типа данных.
        Ожидаем: ошибка с пояснением - Значение «Body» должно содержать максимум 65 535 символов.');
        $preconditionData = $i->preconditionForComment();
        $commentData      = $i->createComment();
        $request          = [
            'body'   => $i->getRandomString(65536, DataTypesValueHelper::ASCII),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateCommentBodyToHieroglyphs(CommentTester $i): void
    {
        $i->wantTo('P [Comment] {поле body}:
        Передаем: строка с рандомным набором японских иероглифов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $commentData      = $i->createComment();
        $preconditionData = $i->preconditionForComment();
        $request          = [
            'body'   => $i->getRandomString(10, DataTypesValueHelper::JP_HIEROGLYPHS),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newCommentData = array_merge(['id' => $commentData['id']], $request);
        $i->seeInCommentTable($newCommentData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $newCommentData,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateCommentBodyToNonAscii(CommentTester $i): void
    {
        $i->wantTo('P [Comment] {поле body}:
        Передаем: строка с рандомным набором non-ascii символов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $commentData      = $i->createComment();
        $preconditionData = $i->preconditionForComment();
        $request          = [
            'body'   => $i->getRandomString(10, DataTypesValueHelper::NON_ASCII),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newCommentData = array_merge(['id' => $commentData['id']], $request);
        $i->seeInCommentTable($newCommentData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $newCommentData,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateCommentBodyToSpecialChars(CommentTester $i): void
    {
        $i->wantTo('P [Comment] {поле body}:
        Передаем: строка с рандомным набором спецсимволов.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $commentData      = $i->createComment();
        $preconditionData = $i->preconditionForComment();
        $request          = [
            'body'   => $i->getRandomString(10, DataTypesValueHelper::CHARACTERS),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newCommentData = array_merge(['id' => $commentData['id']], $request);
        $i->seeInCommentTable($request);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $newCommentData,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateCommentBodyToArray(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле body}:
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «Body» должно быть строкой.');
        $commentData      = $i->createComment();
        $preconditionData = $i->preconditionForComment();
        $request          = [
            'body'   => ['array'],
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws
     */
    public function updateCommentBodyToSqlQuery(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле body}:
        Передаем: корректный SQL запрос.
        Ожидаем: положительный ответ сервера с содержанием этих данных и присвоенным идентификатором записи.');
        $commentData      = $i->createComment();
        $preconditionData = $i->preconditionForComment();
        $request          = [
            'body'   => DataTypesValueHelper::SQL_QUERY,
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::OK);
        $newCommentData = array_merge(['id' => $commentData['id']], $request);
        $i->seeInCommentTable($newCommentData);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => $newCommentData,
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     *
     * @throws Exception
     */
    public function updateCommentNewsIdNotExist(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}: 
        Передаем: несуществующий в таблице новостей Id.
        Ожидаем: ошибку с пояснением - Значение «News Id» неверно.');
        $commentData  = $i->createComment();
        $newsIds      = $i->grabColumnFromNewsTable('id');
        $notExistedId = DataTypesValueHelper::getRandomIntMissingInArray($newsIds);
        $request      = [
            'body'   => sqs('Test body comment'),
            'newsId' => $notExistedId,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «News Id» неверно.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeInCommentTable($commentData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateCommentNewsIdOverMaxPhysicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}:  тип int(11)
        Передаем: превышение максимального значения типа данных.
        Ожидаем: ошибку с пояснением - Значение «News Id» не должно превышать 2147483647.');
        $commentData = $i->createComment();
        $request     = [
            'body'   => sqs('test'),
            'newsId' => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «News Id» не должно превышать 2147483647.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeInCommentTable($commentData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateCommentNewsIdLessMinLogicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}: тип int(11)
        Передаем: значение -1.
        Ожидаем: ошибку с пояснением - Значение «News Id» неверно.');
        $commentData = $i->createComment();
        $request     = [
            'body'   => sqs('Test comment body'),
            'newsId' => - 1,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «News Id» неверно.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeInCommentTable($commentData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateCommentNewsIdToFloat(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}: 
        Передаем: значение типа float.
        Ожидаем: ошибку с пояснением - Значение «News Id» должно быть целым числом.');
        $commentData = $i->createComment();
        $request     = [
            'body'   => sqs('Test comment body'),
            'newsId' => 5.65656,
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «News Id» должно быть целым числом.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeInCommentTable($commentData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function updateCommentNewsIdToArray(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {поле newsId}: 
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «News Id» должно быть целым числом.');
        $commentData = $i->createComment();
        $request     = [
            'body'   => sqs('test'),
            'newsId' => ['array'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $commentData['id'], $request);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                [
                    'code'   => 422,
                    'title'  => '',
                    'detail' => '',
                    'data'   => [
                        'newsId' => 'Значение «News Id» должно быть целым числом.',
                    ],
                ],
            ],
            'notices' => [],
            'data'    => [],
        ]);
        $i->seeInCommentTable($commentData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function updateCommentNotExist(CommentTester $i): void
    {
        $i->wantTo('N [Comment]
        Передаем: несуществующий в таблице идентификатор.
        Ожидаем:  ошибку с пояснением - Сущность не найдена.');
        $preconditionData = $i->preconditionForComment();
        $request          = [
            'body'   => sqs('test'),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $i->getNotExistCommentId(), $request);
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
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function updateCommentOverMaxPhysicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] тип int
        Передаем: значение превышающее максимальное значение типа данных.
        Ожидаем:  ошибку с пояснением - Значение «Id» не должно превышать 2147483647.');
        $preconditionData = $i->preconditionForComment();
        $request          = [
            'body'   => sqs('test'),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . DataTypesValueHelper::OVER_MAX_INT_SIGNED, $request);
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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function updateCommentTypesLessMinLogicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] :
        Передаем: значение 0.
        Ожидаем: ошибка с пояснением - Значение «Id» должно быть не меньше 1.');
        $preconditionData      = $i->preconditionForComment();
        $request = [
            'body'   => sqs('test'),
            'newsId' => $preconditionData['newsData']['id'],
        ];

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . 0, $request);
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
