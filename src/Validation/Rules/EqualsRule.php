<?php

namespace Wixiweb\WixiwebLaravel\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Validation\Validator;

class EqualsRule implements ValidationRule, ValidatorAwareRule
{
    protected Validator $validator;

    public function __construct(
        protected mixed $expectedValue,
        protected bool $strict = true,
        protected ?string $customMessage = null,
    )
    {
    }

    public static function getRuleName(string $attribute): string
    {
        return $attribute.'.'.self::class;
    }

    public function validate(string $attribute, mixed $value, Closure $fail,): void
    {
        $isEqual = $this->strict === true
            ? $value === $this->expectedValue
            : $value == $this->expectedValue;

        if (!$isEqual) {

            $fail($this->message())->translate([
                'expectedValue' => $this->formatExpectedValue(),
                'attribute' => $attribute,
                'value' => $value,
            ]);
        }
    }

    protected function message(): string
    {
        $strictModeKey = $this->strict === true ? 'equals_strict' : 'equals_loose';

        $validationKey = 'validation.'.$strictModeKey;
        $defaultKey = 'wixiweb-laravel::validation.'.$strictModeKey;

        $translator = $this->validator->getTranslator();

        if ($translator->has($validationKey)) {
            return $validationKey;
        }

        if ($translator->has($defaultKey)) {
            return $defaultKey;
        }

        return $defaultKey;
    }

    protected function formatExpectedValue(): string
    {
        return match (true) {
            is_null($this->expectedValue) => 'null',
            is_bool($this->expectedValue) => $this->expectedValue ? 'true' : 'false',
            is_string($this->expectedValue) => $this->expectedValue,
            is_array($this->expectedValue) => json_encode($this->expectedValue, JSON_THROW_ON_ERROR),
            is_object($this->expectedValue) => get_class($this->expectedValue),
            default => (string) $this->expectedValue
        };
    }

    public function setValidator(Validator $validator) : void
    {
        $this->validator = $validator;
    }
}
