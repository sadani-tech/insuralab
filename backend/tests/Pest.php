<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(Tests\TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
| RefreshDatabase is opted into per-file with `uses(RefreshDatabase::class)`
| so that migration-lifecycle tests can manage the schema themselves.
*/
