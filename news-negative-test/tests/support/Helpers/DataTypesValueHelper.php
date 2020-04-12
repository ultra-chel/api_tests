<?php

namespace UserstoryTemp\NewsNegativeTest\tests\support\Helpers;

use Codeception\Module;
use Exception;

/**
 * Класс описывает типы данных и формирование рандомной строки из указанной группы символов.
 */
class DataTypesValueHelper extends Module
{
    const LATIN_UPPER            = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const LATIN_LOWER            = 'abcdefghijklmnopqrstuvwxyz';
    const CYRILLIC_UPPER         = 'АВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';
    const CYRILLIC_LOWER         = 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя';
    const JP_HIEROGLYPHS         = '茨媛岡潟岐熊香佐埼崎滋鹿縄井沖栃奈梨阪阜';
    const ARABIC                 = 'كلمنسعفصقرشتثخذضظغ';
    const CHARACTERS             = '!№;%:?*()_-+/!@#$%^&*"}"{[]:;<>?/|';
    const INTEGER                = '1234567890';
    const MAX_INT_SIGNED         = 2147483647;
    const MIN_INT_SIGNED         = - 2147483648;
    const OVER_MAX_INT_SIGNED    = 2147483649;
    const LESS_MIN_INT_SIGNED    = - 2147483649;
    const OVER_MAX_BIGINT_SIGNED = 9223372036854775809;
    const LESS_MIN_BIGINT_SIGNED = - 9223372036854775809;
    const NON_ASCII              = '¡¢£¤¥¦§¨©ª«¬®¯°±µ¶';
    const ASCII                  = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzАВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя';
    const SQL_QUERY              = '1"; DELETE * FROM news_types WHERE id = 1;';

    /**
     * Возвращает случайную строку указанной длины.
     *
     * @param int    $length     Длина генерируемой строки.
     * @param string $characters Список символов, из которых нужно сгенерировать случайную строку.
     *
     * @throws Exception Если было невозможно собрать достаточную энтропию при генерации целого числа.
     *
     * @return string
     */
    public static function getRandomString(int $length = 1, string $characters = ''): string
    {
        if ('' === $characters) {
            $characters = self::ASCII;
        }
        $charsCount = mb_strlen($characters, 'utf8') - 1;
        $result     = '';
        for ($i = 0; $i < $length; $i ++) {
            $result .= mb_substr($characters, random_int(0, $charsCount), 1, 'utf8');
        }
        return $result;
    }

    /**
     * Возвращает случайное положительное число не превышающее физический лимит integer.
     *
     * @param int    $length     Длина генерируемой строки.
     * @param string $characters Список символов, из которых нужно сгенерировать случайную строку.
     *
     * @throws Exception Если было невозможно собрать достаточную энтропию при генерации целого числа.
     *
     * @return string
     */
    public static function getRandomInt()
    {
        return random_int(1, self::MAX_INT_SIGNED);
    }

    /**
     * Возвращает случайное положительное число, которого нет в массиве.
     *
     * @param int    $length     Длина генерируемой строки.
     * @param string $characters Список символов, из которых нужно сгенерировать случайную строку.
     *
     * @throws Exception Если было невозможно собрать достаточную энтропию при генерации целого числа.
     *
     * @return int
     */
    public static function getRandomIntMissingInArray(array $array): int
    {
        $randomInt = count($array);
        while (in_array($randomInt, $array) || $randomInt < 1) {
            $randomInt = random_int($randomInt, self::MAX_INT_SIGNED);
        }
        return $randomInt;
    }
}
