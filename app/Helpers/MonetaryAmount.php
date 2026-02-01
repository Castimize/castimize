<?php

declare(strict_types=1);

namespace app\Helpers;

use InvalidArgumentException;
use Stringable;

final class MonetaryAmount implements Stringable
{
    public const VALID_REGEX_PATTERN = '/^-?[0-9]+([\.\,]([0-9]{1}|[0-9]{5}))?$/';

    public function __construct(
        private int $value = 0,
    ) {}

    public function __toString(): string
    {
        return $this->toString();
    }

    public static function zero(): self
    {
        return new self;
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromFloat(float $decimal): self
    {
        return self::fromString((string) $decimal);
    }

    /**
     * Returns the amount as a currency formatted string.
     */
    public function toString(): string
    {
        return number_format(num: (int) $this->getValue() / 100, decimals: 2, thousands_separator: '');
    }

    public function getValue(bool $isAbsolute = false): int
    {
        return $isAbsolute ? abs($this->value) : $this->value;
    }

    /**
     * Returns the amount as a float of it's value.
     */
    public function toFloat(): float
    {
        return (float) bcdiv((string) $this->value, '100', 2);
    }

    /**
     * Returns the amount in cents.
     */
    public function toCents(): int
    {
        return $this->getValue();
    }

    public function add(self $monetaryValue): self
    {
        $result = self::fromString(bcadd($this->toString(), $monetaryValue->toString(), 2));
        $test = 0;

        return $result;
    }

    public static function fromString(string $string): self
    {
        self::validate($string);

        return new self((int) bcmul($string, '100', 2));
    }

    public function subtract(self $monetaryValue): self
    {
        return self::fromString(bcsub($this->toString(), $monetaryValue->toString(), 2));
    }

    public function percentageOf(float $percentage): self
    {
        return self::fromFloat(round((($this->value / 100) / 100) * $percentage, 2));
    }

    public function multiply(float $by): self
    {
        return self::fromString(bcmul($this->toString(), (string) $by, 2));
    }

    public function divide(float $by): self
    {
        return self::fromString(bcdiv($this->toString(), (string) $by, 2));
    }

    public function modulo(float $by): self
    {
        return self::fromFloat($this->toFloat() % $by);
    }

    public function absolute(): self
    {
        return new self(value: abs($this->getValue()));
    }

    public function negativeAbsolute(): self
    {
        return $this->absolute()->multiply(-1);
    }

    public function equals(self $other): bool
    {
        return $this->getValue() === $other->getValue();
    }

    public function isZero(): bool
    {
        return $this->getValue() === 0;
    }

    public function isNotZero(): bool
    {
        return $this->getValue() !== 0;
    }

    public function isNegative(): bool
    {
        return $this->getValue() < 0;
    }

    public function isZeroOrNegative(): bool
    {
        return $this->isNegative() || $this->isZero();
    }

    public function isPositive(): bool
    {
        return $this->getValue() > 0;
    }

    public function isSmallerThan(self $other, bool $comparesAbsoluteValue = false): bool
    {
        return $this->getValue() < $other->getValue($comparesAbsoluteValue);
    }

    public function isSmallerThanOrEquals(self $otherValue, bool $comparesAbsoluteValue = false): bool
    {
        return $this->getValue() <= $otherValue->getValue($comparesAbsoluteValue);
    }

    public function isGreaterThanOrEquals(self $otherValue, bool $comparesAbsoluteValue = false): bool
    {
        return $this->getValue() >= $otherValue->getValue($comparesAbsoluteValue);
    }

    public function isGreaterThan(self $otherValue, bool $comparesAbsoluteValue = false): bool
    {
        return $this->getValue() > $otherValue->getValue($comparesAbsoluteValue);
    }

    private static function validate(string $input): void
    {
        if (! (bool) preg_match(self::VALID_REGEX_PATTERN, $input)) {
            // throw new InvalidArgumentException("'{$input}' is not a valid monetary value");
        }
    }
}
