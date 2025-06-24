<?php

test('trans_plural works as expected', function () {
    expect(function_exists('trans_plural'))->toBeTrue();

    $singular = trans_plural('test', 'tests', 1);
    $plural = trans_plural('test', 'tests', 2);

    expect($singular)->toBe('test')
        ->and($plural)->toBe('tests');

    $singularWithCount = trans_plural('test :count', 'tests :count', 1);
    $pluralWithCount = trans_plural('test :count', 'tests :count', 2);

    expect($singularWithCount)->toBe('test 1')
        ->and($pluralWithCount)->toBe('tests 2');

    $singularWithReplace = trans_plural('test :my_variable', 'tests :my_variable', 1, ['my_variable' => 'toto']);
    $pluralWithReplace = trans_plural('test :my_variable', 'tests :my_variable', 2, ['my_variable' => 'toto']);

    expect($singularWithReplace)->toBe('test toto')
        ->and($pluralWithReplace)->toBe('tests toto');
});

test('trans_plural_map works as expected', function () {
    expect(function_exists('trans_plural_map'))->toBeTrue();

    $singular = trans_plural_map([
        '0,1' => 'test',
        '2,*' => 'tests',
    ], 1);

    $plural = trans_plural_map([
        '0,1' => 'test',
        '2,*' => 'tests',
    ], 2);

    expect($singular)->toBe('test')
        ->and($plural)->toBe('tests');

    $singularWithCount = trans_plural_map([
        '0,1' => 'test :count',
        '2,*' => 'tests :count',
    ], 1);

    $pluralWithCount = trans_plural_map([
        '0,1' => 'test :count',
        '2,*' => 'tests :count',
    ], 2);

    expect($singularWithCount)->toBe('test 1')
        ->and($pluralWithCount)->toBe('tests 2');

    $singularWithReplace = trans_plural_map([
        '0,1' => 'test :my_variable',
        '2,*' => 'tests :my_variable',
    ], 1, ['my_variable' => 'toto']);

    $pluralWithReplace = trans_plural_map([
        '0,1' => 'test :my_variable',
        '2,*' => 'tests :my_variable',
    ], 2, ['my_variable' => 'toto']);

    expect($singularWithReplace)->toBe('test toto')
        ->and($pluralWithReplace)->toBe('tests toto');
});

