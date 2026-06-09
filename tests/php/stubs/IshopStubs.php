<?php

declare(strict_types=1);

/**
 * Минимальные stubs com_ishop для автономной проверки helper-контракта.
 */

namespace Ilange\Component\Ishop\Site\Service;

final class FilterRules
{
    public static array $normalizeCalls = [];
    public static mixed $normalizedReturn = null;

    /**
     * Сбрасывает историю нормализации между тестами.
     */
    public static function reset(): void
    {
        self::$normalizeCalls = [];
        self::$normalizedReturn = null;
    }

    /**
     * Запоминает входные данные и возвращает управляемый результат.
     */
    public static function normalizeFilterInput(array $input): array
    {
        self::$normalizeCalls[] = $input;

        return is_array(self::$normalizedReturn) ? self::$normalizedReturn : $input;
    }
}

final class FilterAvailabilityService
{
    public static array $filteredProductIds = [];
    public static array $availableOptions = [];
    public static array $filteredProductCalls = [];
    public static array $availableOptionsCalls = [];

    /**
     * Сбрасывает состояние service double между тестами.
     */
    public static function reset(): void
    {
        self::$filteredProductIds = [];
        self::$availableOptions = [];
        self::$filteredProductCalls = [];
        self::$availableOptionsCalls = [];
    }

    /**
     * Возвращает настроенный список product ids и фиксирует аргументы.
     */
    public function getFilteredProductIds(int $categoryId, int $itemId, array $filters): array
    {
        self::$filteredProductCalls[] = [$categoryId, $itemId, $filters];

        return self::$filteredProductIds;
    }

    /**
     * Возвращает настроенные available options и фиксирует аргументы.
     */
    public function getAvailableOptions(int $categoryId, int $itemId, array $filters): array
    {
        self::$availableOptionsCalls[] = [$categoryId, $itemId, $filters];

        return self::$availableOptions;
    }
}
