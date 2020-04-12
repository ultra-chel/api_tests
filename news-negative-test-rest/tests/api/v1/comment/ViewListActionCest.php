<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\comment;

use Codeception\Util\HttpCode;
use Exception;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\CommentTester;

/**
 * Класс тестирования REST API: удаление сущности "Комментарии к новостям".
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
    public static $URI = 'v1/comment';

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
                        'newsId'     => 'integer',
                        'body'       => 'string',
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
    public function listCommentWithoutAuth(CommentTester $i): void
    {
        $i->wantTo('N [Comment]: 
        Передаем: Запрос на получение списка сущностей без авторизации.
        Ожидаем: Ошибку с поянением - Доступ запрещен.');

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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listCommentPositive(CommentTester $i): void
    {
        $i->wantTo('P [Comment]: 
        Передаем: Запрос на получение списка сущностей без фильтров, лимитов и оффсетов.
        Ожидаем: список всех сущностей.');
        $firstComment  = $i->createComment(['body' => sqs('Test. First comment')]);
        $secondComment = $i->createComment(['body' => sqs('Test. Second comment')]);
        $thirdComment  = $i->createComment([
            'body'   => sqs('Test. Third comment'),
            'newsId' => $firstComment['newsId'],
        ]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstComment,
                    $secondComment,
                    $thirdComment,
                ],
                'more' => false,
            ],
        ]);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
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
    public function listCommentFilterNewsIdCorrect(CommentTester $i): void
    {
        $i->wantTo('P [Comment] {Filter: newsId}: 
        Передаем: корректное значение.
        Ожидаем: список отфильтрованных сущностей.');
        $firstComment  = $i->createComment(['body' => sqs('Test. First comment')]);
        $secondComment = $i->createComment(['body' => sqs('Test. Second comment')]);
        $thirdComment  = $i->createComment([
            'body'   => sqs('Test. Third comment'),
            'newsId' => $firstComment['newsId'],
        ]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => $firstComment['newsId'],
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstComment,
                    $thirdComment,
                ],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[2]');
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
    public function listCommentFilterNewsIdNotExist(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {Filter: newsId}: 
        Передаем: несуществующий в таблице новостей идентификатор.
        Ожидаем: ошибку с пояснением - Значение «News Id» неверно.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => $i->getNotExistNewsId(),
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsId' => 'Значение «News Id» неверно.',
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
    public function listCommentFilterNewsIdOverMaxPhysicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {Filter: newsId}: 
        Передаем: превышение максимального значения типа int.
        Ожидаем: ошибку с пояснением - Значение «News Id» не должно превышать 2147483647.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => DataTypesValueHelper::OVER_MAX_INT_SIGNED,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsId' => 'Значение «News Id» не должно превышать 2147483647.',
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
    public function listCommentFilterNewsIdLessMinLogicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {Filter: newsId}: 
        Передаем: меньше минимального значения типа int.
        Ожидаем: ошибку с пояснением - Значение «News Id» неверно.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => - 1,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsId' => 'Значение «News Id» неверно.',
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
    public function listCommentFilterNewsIdToArray(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {Filter: newsId}: 
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «News Id» должно быть целым числом.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => ['array'],
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsId' => 'Значение «News Id» должно быть целым числом.',
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
    public function listCommentFilterNewsIdToString(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {Filter: newsId}: 
        Передаем: значение типа string.
        Ожидаем: ошибку с пояснением - Значение «News Id» должно быть целым числом.');
        $i->loginAsAdmin();

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => 'text',
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsId' => 'Значение «News Id» должно быть целым числом.',
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
    public function listCommentFilterNewsIdToSqlQuery(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {Filter: newsId}: 
        Передаем: корректный sql запрос в строке.
        Ожидаем: ошибку с пояснением - Значение «News Id» должно быть целым числом.');

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'newsId' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'newsId' => 'Значение «News Id» должно быть целым числом.',
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
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function listCommentFilterBodyCorrect(CommentTester $i): void
    {
        $i->wantTo('P [Comment] {Filter: body}: 
        Передаем: корректное значение.
        Ожидаем: список отфильтрованных сущностей.');
        $firstComment = $i->createComment(['body' => sqs('Test. First comment')]);
        $i->createComment(['body' => sqs('Test. Second comment')]);
        $i->createComment([
            'body'   => sqs('Test. Third comment'),
            'newsId' => $firstComment['newsId'],
        ]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'body' => $firstComment['body'],
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $firstComment,
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[1]');
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
    public function listCommentFilterBodyOverMaxPhysicLength(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {Filter: body}: тип text
        Передаем: кол-во символов превышает максимальную длину типа данных.
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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listCommentFilterBodyToArray(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {Filter: body}: тип text
        Передаем: значение типа array.
        Ожидаем: ошибку с пояснением - Значение «body» должно быть строкой.');

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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function listCommentFilterBodyToSqlQuery(CommentTester $i): void
    {
        $i->wantTo('N [Comment] {Filter: body}: тип text
        Передаем: корректный SQL запрос.
        Ожидаем: пустой массив list за неимением совпадений.');
        $partBody    = substr(DataTypesValueHelper::SQL_QUERY, 0, 3);
        $commentData = $i->createComment(['body' => $partBody]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'body' => DataTypesValueHelper::SQL_QUERY,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $commentData,
                'more' => false,
            ],
        ]);
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listCommentFilterLimit(CommentTester $i): void
    {
        $i->wantTo('P [Comment] [Filter: limit]:
        Передаем: limit = 2.
        Ожидаем: первые два элемента списка');
        $firstComment  = $i->createComment(['body' => sqs('Test. First comment')]);
        $secondComment = $i->createComment(['body' => sqs('Test. Second comment')]);
        $thirdComment  = $i->createComment(['body' => sqs('Test. Third comment')]);

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
                    $firstComment,
                    $secondComment,
                ],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$thirdComment],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[2]');
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
    public function listCommentFilterLimitOverMaxPhysicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] [Filter: limit]: тип bigInt_signed
        Передаем: значение int превышающие физическое ограничение типа данных.
        Ожидаем: ошибку с пояснением. (cомнительное описание ошибки)');

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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listCommentFilterLimitLessMinLogicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] [Filter: limit]: тип bigInt_signed
        Передаем: значение int меньше минимального значения типа данных.
        Ожидаем: ошибку с пояснением. (cомнительное описание ошибки)');
        $i->createComment();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'limit' => - 1,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [],
                'more' => true,
            ],
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
    public function listCommentFilterLimitToString(CommentTester $i): void
    {
        $i->wantTo('N [Comment] [Filter: limit]:
        Передаем: строку с рандомным набором ASCII символов.
        Ожидаем: ошибку с пояснением. (cомнительное описание ошибки)');

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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listCommentFilterOffset(CommentTester $i): void
    {
        $i->wantTo('P [Comment] [Filter: offset]:
        Передаем: offset = 1.
        Ожидаем: список без первого элемента');
        $firstComment  = $i->createComment(['body' => sqs('Test. First comment')]);
        $secondComment = $i->createComment(['body' => sqs('Test. Second comment')]);
        $thirdComment  = $i->createComment([
            'body'   => sqs('Test. Third comment'),
            'newsId' => $firstComment['newsId'],
        ]);

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
                    $secondComment,
                    $thirdComment,
                ],
                'more' => false,
            ],
        ]);
        $i->dontSeeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => $firstComment,
                'more' => false,
            ],
        ]);
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
    public function listCommentFilterOffsetOverCountEntities(CommentTester $i): void
    {
        $i->wantTo('N [Comment] [Filter: offset]:
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
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[0]');
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
    public function listCommentFilterOffsetOverMaxPhysicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] [Filter: offset]:
        Передаем: значение int превышающие максимальное ограничение типа.
        Ожидаем: пустой список');
        $i->createComment();

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
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @throws
     *
     * @return void
     */
    public function listCommentFilterOffsetLessMinLogicInt(CommentTester $i): void
    {
        $i->wantTo('N [Comment] [Filter: offset]:
        Передаем: значение int меньше минимального логического значения.
        Ожидаем: полный список сущностей');
        $firstComment  = $i->createComment(['body' => sqs('Test. First comment')]);
        $secondComment = $i->createComment(['body' => sqs('Test. Second comment')]);
        $thirdComment  = $i->createComment(['body' => sqs('Test. Third comment')]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'offset' => - 1,
            ],
        ]);
        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [
                    $firstComment,
                    $secondComment,
                    $thirdComment,
                ],
                'more' => false,
            ],
        ]);
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
    public function listCommentFilterOffsetToString(CommentTester $i): void
    {
        $i->wantTo('N [Comment] [Filter: offset]:
        Передаем: строку с рандомным набором ASCII символов.
        Ожидаем: ошибку с пояснением - .');

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
                'code'   => 422,
                'title'  => '',
                'detail' => '',
                'data'   => [
                    'offset' => 'Значение «offset» должно быть целым числом.',
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
    public function listCommentFilterOffsetLimit(CommentTester $i): void
    {
        $i->wantTo('P [Comment] [Filter: offset & limit]:
        Передаем: offset = 1 limit = 1.
        Ожидаем: второй элемент списка.');
        $i->createComment(['body' => sqs('Test. First comment')]);
        $secondComment = $i->createComment(['body' => sqs('Test. Second comment')]);
        $i->createComment(['body' => sqs('Test. Third comment')]);

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
                'list' => $secondComment,
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[1]');
    }

    /**
     * Метод проверяет позитивный кейс.
     *
     * @param CommentTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @throws
     *
     * @return void
     */
    public function listCommentFilterPartBodyOffsetLimit(CommentTester $i): void
    {
        $i->wantTo('P [Comment] [Filter: offset & limit & body]:
        Передаем: корректный ввод в поле body части значения, так же offset = 1 и limit = 1.
        Ожидаем: третий элемент отфильтрованного списка');
        $i->createComment(['body' => sqs('First comment')]);
        $secondComment = $i->createComment(['body' => sqs('Test. Second comment')]);
        $thirdComment  = $i->createComment(['body' => sqs('Test. Third comment')]);
        $fourthComment = $i->createComment(['body' => sqs('Test. Fourth comment')]);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI, [
            'filter' => [
                'body'   => 'Test',
                'offset' => 1,
                'limit'  => 1,
            ],
        ]);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'list' => [$thirdComment],
                'more' => true,
            ],
        ]);
        $i->dontSeeResponseJsonMatchesJsonPath('$.data.list[1]');
    }
}
