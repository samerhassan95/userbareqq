<?php

namespace App\Enum;

class RoleEnum
{
    public const USER = 1;
    public const ADMIN = 2;
    public const DESIGNER = 3;
    public const MARKETER = 4;

    /**
     * Get all available roles.
     *
     * @return array
     */
    public static function getList(): array
    {
        return [
            self::USER => 'User',
            self::ADMIN => 'Admin',
            self::DESIGNER => 'Designer',
            self::MARKETER => 'Marketer',
        ];
    }

    /**
     * Check if a given value is a valid role.
     *
     * @param int $value
     * @return bool
     */
    public static function isValid(int $value): bool
    {
        return array_key_exists($value, self::getList());
    }

    /**
     * Get the label for a given role.
     *
     * @param int $value
     * @return string|null
     */
    public static function getLabel(int $value): ?string
    {
        return self::getList()[$value] ?? null;
    }
}
