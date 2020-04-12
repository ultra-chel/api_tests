<?php

declare(strict_types = 1);

namespace UserstoryTemp\NewsNegativeTestRest\tests\api\v1\tag;

use Codeception\Util\HttpCode;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\DataTypesValueHelper;
use UserstoryTemp\NewsNegativeTestRest\tests\support\Step\Api\TagTester;
use function count;

/**
 * Класс тестирования REST API: удаление сущности "Тэги".
 */
class DeleteOneActionCest
{
    /**
     * Заголовки, указывающие на метод.
     *
     * @var array
     */
    public static $methodHeader = [
        'name'  => 'X-HTTP-Method-Override',
        'value' => 'DELETE',
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
                'success' => 'boolean',
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
     * Метод проверяет запрос удаления типа новости.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L2
     * @group authorization
     *
     * @return void
     */
    public function deleteTagWithOutAuth(TagTester $i): void
    {
        $i->wantTo('N [Tag] 
        Передаем: удаление сущности без авторизации. 
        Ожидаем: Ошибку с пояснением - Доступ запрещен');
        $tagData = $i->createTag();

        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $tagData['id']);

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
        $i->seeInTagTable($tagData);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L1
     * @group functional
     *
     * @return void
     */
    public function deleteTagCorrect(TagTester $i): void
    {
        $i->wantTo('P [Tag] 
        Передаем: корректный id. 
        Ожидаем: Успешное удаление.');
        $tagData = $i->createTag();

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $tagData['id']);

        $i->seeResponseCodeIs(HttpCode::OK);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'success' => true,
            ],
        ]);
        $i->dontSeeInTagTable($tagData);
        $i->seeResponseMatchesJsonType($this->responseTypes['correct']);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     * @throws \Exception
     */
    public function deleteTagNotExist(TagTester $i): void
    {
        $i->wantTo('N [Tag] 
        Передаем: несуществующей в таблице идентификатор тэга.
        Ожидаем: Успешный результат выполнения запроса. Количество записей в базе не изменено');
        $tagIds         = $i->grabColumnFromNewsTypeTable('id');
        $notExistId     = $i->getNotExistTagId();
        $tagCountBefore = count($tagIds);

        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . $notExistId);

        $i->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $i->seeResponseContainsJson([
            'errors'  => [],
            'notices' => [],
            'data'    => [
                'success' => true,
            ],
        ]);
        $i->seeNumRecordsInNewsTypeTable($tagCountBefore);
        $i->seeResponseMatchesJsonType($this->responseTypes['errors']);
    }

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function deleteTagIdLessMinLogicInt(TagTester $i): void
    {
        $i->wantTo('N [Tag] тип int(11)
        Передаем: значение id меньше логического минимума типа данных. 
        Ожидаем: Ошибку с пояснением - Значение «Id» должно быть не меньше 1.');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . 0);

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

    /**
     * Метод проверяет запрос удаления типа новости.
     *
     * @param TagTester $i Объект текущего тестировщика.
     *
     * @group L3
     * @group validation
     *
     * @return void
     */
    public function deleteTagIdOverMaxPhysicInt(TagTester $i): void
    {
        $i->wantTo('N [Tag] тип int(11)
        Передаем: значение id больше физического максимума типа данных. 
        Ожидаем: ошибку с пояснением - Значение «Id» не должно превышать 2147483647.');
        $i->loginAsAdmin();
        $i->haveHttpHeader(self::$methodHeader['name'], self::$methodHeader['value']);
        $i->sendPOST(self::$URI . DataTypesValueHelper::OVER_MAX_INT_SIGNED);

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
}
