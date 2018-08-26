<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\LeaveHelperTrait;
use Tests\TestCase;

class LeaveApplicationTest extends TestCase
{
    use RefreshDatabase, LeaveHelperTrait;
}
