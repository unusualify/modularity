<?php

namespace Unusualify\Modularous\Tests\Entities\Enums;

use Unusualify\Modularous\Entities\Enums\RevisionStatus;
use Unusualify\Modularous\Tests\TestCase;

class RevisionStatusTest extends TestCase
{
    public function test_cases_have_expected_values()
    {
        $this->assertEquals('pending', RevisionStatus::Pending->value);
        $this->assertEquals('approved', RevisionStatus::Approved->value);
        $this->assertEquals('rejected', RevisionStatus::Rejected->value);
    }

    public function test_it_lists_all_cases()
    {
        $this->assertSame([
            RevisionStatus::Pending,
            RevisionStatus::Approved,
            RevisionStatus::Rejected,
        ], RevisionStatus::cases());
    }

    public function test_default_approved_returns_approved_case()
    {
        $this->assertSame(RevisionStatus::Approved, RevisionStatus::defaultApproved());
        $this->assertEquals('approved', RevisionStatus::defaultApproved()->value);
    }
}
