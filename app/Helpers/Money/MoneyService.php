<?php

namespace App\Helpers\Money;

use Akaunting\Money\Currency;
use Akaunting\Money\Money as LaravelMoney;
use Illuminate\Support\Str;

class MoneyService
{
    protected LaravelMoney $laravelMoney;
    protected LaravelMoney $rawMoney;

    public function __construct($value = 0, $currency = null, $convert = false)
    {
        $baseValue = $this->extractBaseValue($value);
        $currency = self::resolveCurrency($currency);
        $convert = $this->checkIfConvert($baseValue);

        // Instantiate Laravel Money
        $this->laravelMoney = new LaravelMoney($baseValue, $currency, $convert);
        // Keep A Copy Of Original Value
        $this->rawMoney = clone $this->laravelMoney;
    }

    private function extractBaseValue($value)
    {

        if ($value instanceof self || $value instanceof LaravelMoney) {
            return $value->getValue();
        }
        return $value;
    }

    private function checkIfConvert($value): bool
    {
        if (is_float($value)) {
            return true;
        } elseif (is_string($value) && str_contains($value, '.')) {
            return (bool) Str::after($value, '.');
        }
        return false;
    }

    protected function createNewMoneyObject($value, $currency = null, $convert = false): static
    {
        return new static($value, $currency ?? $this->getCurrencyCode(), $convert);
    }

    public function sameAs($value, ?string $currency = null): bool
    {
        $givenValue = $this->createNewMoneyObject($value, $currency);
        return $this->laravelMoney->equals($givenValue->get());
    }

    public function compare($value, ?string $currency = null): int
    {
        $givenValue = $this->createNewMoneyObject($value, $currency);
        return $this->laravelMoney->compare($givenValue->get());
    }

    public function currency(): Currency
    {
        return $this->laravelMoney->getCurrency();
    }

    public function getCurrency(): Currency
    {
        return $this->currency();
    }

    public function getCurrencyCode(): string
    {
        return $this->currency()->getCurrency();
    }

    public function amount(): float|int
    {
        return $this->laravelMoney->getAmount();
    }

    public function getAmount(): float|int
    {
        return $this->amount();
    }

    public function formatted(): string
    {
        return $this->laravelMoney->format();
    }

    public function forHuman(): string
    {
        return $this->laravelMoney->formatForHumans();
    }

    public function getValue(): float
    {
        return $this->laravelMoney->getValue();
    }

    public function get(): LaravelMoney
    {
        return clone $this->rawMoney;
    }

    public function getMoney(): LaravelMoney
    {
        return clone $this->laravelMoney;
    }

    public static function resolveCurrency(?string $currency = null): Currency
    {
        return new Currency($currency ?? config('services.defaults.currency'));
    }

    public static function format(int|float|string $value, ?string $currency = null): string
    {
        $instance = new static($value, $currency);
        return $instance->formatted();
    }

    public static function isMoney($object): bool
    {
        return $object instanceof self;
    }
}
