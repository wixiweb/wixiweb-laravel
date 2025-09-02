<?php

namespace Wixiweb\WixiwebLaravel\Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Wixiweb\WixiwebLaravel\Validation\Rules\EqualsRule;


it('test strict validation passes with identical values', function ()
{
    $validator = Validator::make(
        [
        'field' => 'test'
        ],
        [
            'field' => [new EqualsRule('test')],
        ]
    );

    $fails = $validator->fails();

    expect($fails)->toBeFalse();
});

it ('test strict validation fails with different types', function()
{
    $validator = Validator::make(
        [
            'field' => '123'
        ],
        [
            'field' => [new EqualsRule(123)],
        ]
    );

    $fails = $validator->fails();

    expect($fails)->toBeTrue();
});

it ('test loose validation passes with different types', function()
{
    $validator = Validator::make(
        [
            'field' => '123'
        ],
        [
            'field' => [new EqualsRule(123, false)],
        ]
    );

    $fails = $validator->fails();

    expect($fails)->toBeFalse();
});

it ('test loose validation fails with different values', function(){
    $validator = Validator::make(
        [
            'field' => '1234'
        ],
        [
            'field' => [new EqualsRule(123, false)],
        ]
    );

    $fails = $validator->fails();

    expect($fails)->toBeTrue();
});

it ('test custom validation message is used', function(){
    $customMessage = 'This is a custom message for :attribute with expected value 123';

    $validator = Validator::make(
        [
            'field' => '123',
        ],
        [
            'field' => [new EqualsRule(123, true)],
        ],
        [
            EqualsRule::getRuleName('field') => $customMessage,
        ]
    );

    $fails = $validator->fails();
    $fieldError = $validator->messages()->get('field')[0];

    expect($fails)
        ->toBeTrue()
        ->and($fieldError)
        ->toEqual('This is a custom message for field with expected value 123');
});

it ('test default strict validation message is used when no custom message provided', function(){
    $validator = Validator::make(
        [
            'field' => '123',
        ],
        [
            'field' => [new EqualsRule(123)],
        ]
    );

    $fails = $validator->fails();
    $fieldError = $validator->messages()->get('field')[0];

    expect($fails)
        ->toBeTrue()
        ->and($fieldError)
        ->toEqual('The field field must be strictly equal to 123.');
});

it ('test default loose validation message is used when no custom message provided', function(){
    $validator = Validator::make(
        [
            'field' => '123',
        ],
        [
            'field' => [new EqualsRule(1234, false)],
        ]
    );

    $fails = $validator->fails();
    $fieldError = $validator->messages()->get('field')[0];

    expect($fails)
        ->toBeTrue()
        ->and($fieldError)
        ->toEqual('The field field must be equal to 123.');
});
