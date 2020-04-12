<?php

declare( strict_types = 1 );

namespace UserstoryTemp\NewsNegativeTestRest\tests\support;

use Codeception\Actor;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\traits\CommentHelperTrait;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\traits\NewsHelperTrait;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\traits\NewsTagParamHelperTrait;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\traits\NewsTypeHelperTrait;
use UserstoryTemp\NewsNegativeTest\tests\support\Helpers\traits\TagsHelperTrait;
use yii;

/**
 * Класс текущего актора-тестера.
 *
 * @method void wantToTest( $text )
 * @method void wantTo( $text )
 * @method void execute( $callable )
 * @method void expectTo( $prediction )
 * @method void expect( $prediction )
 * @method void amGoingTo( $argumentation )
 * @method void am( $role )
 * @method void lookForwardTo( $achieveValue )
 * @method void comment( $description )
 * @method \Codeception\Lib\Friend haveFriend( $name, $actorClass = null )
 */
class ApiTester extends Actor
{
    use _generated\ApiTesterActions;
    use CommentHelperTrait;
    use NewsTypeHelperTrait;
    use NewsHelperTrait;
    use TagsHelperTrait;

    /**
     * Метод логинит пользователя под админом.
     *
     * @return void
     */
    public function loginAsAdmin(): void
    {
        $login    = sqs('admin');
        $password = '123456';
        $this->createNewUser($login, [
            'password' => $password,
            'roleId'   => 1,
        ]);
        $this->login($login, $password);
    }

    /**
     * Метод логинит пользователя.
     *
     * @param string $username Имя пользователя.
     * @param string $password Пароль пользователя.
     *
     * @return void
     */
    public function login(string $username = 'admin', string $password = '123456'): void
    {
        $this->haveHttpHeader('X-HTTP-Method-Override', 'POST');
        $this->sendPOST('v1/auth', [
            'login'    => $username,
            'password' => $password,
        ]);
        $this->seeResponseCodeIs(200);
    }

    /**
     * Метод чистит кеш приложения.
     *
     * @return void
     */
    public function flushCache(): void
    {
        Yii::$app->cache->flush();
    }
}
