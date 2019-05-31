<?php


namespace App\Repositories\Dictionaries;


class ChargeType
{
    const WITHDRAW = [
        'id' => 1,
        'text' => "Withdraw"
    ];

    const FEE = [
        'id' => 2,
        'text' => "Fee"
    ];

    const CHARGE = [
        'id' => 3,
        'text' => "Charge"
    ];

    public static function toText(string $type): string
    {
        $reflectionClass = new ReflectionClass(__CLASS__);
        $cons = $reflectionClass->getConstants();

        if (key_exists($type, $cons)) {
            return $cons[$type]['text'];
        }

        throw new Exception("Undefined const on ChargeType dictionary.", 500);
    }

    public static function toId(string $type): string
    {
        $reflectionClass = new ReflectionClass(__CLASS__);
        $cons = $reflectionClass->getConstants();

        if (key_exists($type, $cons)) {
            return $cons[$type]['id'];
        }

        throw new Exception("Undefined const on ChargeType dictionary.", 500);
    }
}
