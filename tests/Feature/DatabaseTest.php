<?php
use Illuminate\Support\Facades\DB;

it('Create a database', function () {
    $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?";
    $db = DB::select($query, ['wixiweb']);
    expect($db)->toBeArray()->toHaveCount(0);

    $this->artisan('wixiweb:db:create', ['name' => 'wixiweb']);

    $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?";
    $db = DB::select($query, ['wixiweb']);

    expect($db)->toBeArray()->toHaveCount(1)->toMatchArray([
        (object) [
            'SCHEMA_NAME' => 'wixiweb'
        ]
    ]);
});
