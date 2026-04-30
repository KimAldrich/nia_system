<?php

namespace Tests\Unit;

use App\Models\IaResolution;
use PHPUnit\Framework\TestCase;

class IaResolutionStatusTest extends TestCase
{
    public function test_fs_team_uses_validated_as_completed_label(): void
    {
        $this->assertSame('validated', IaResolution::completedStatusValueForTeam('fs_team'));
        $this->assertSame('Validated', IaResolution::completedStatusLabelForTeam('fs_team'));
        $this->assertSame('Not Validated', IaResolution::pendingStatusLabelForTeam('fs_team'));
        $this->assertSame('Validated', IaResolution::displayStatusLabel('validated', 'fs_team'));
        $this->assertSame('Not Validated', IaResolution::displayStatusLabel('not-validated', 'fs_team'));
    }

    public function test_other_teams_use_accomplished_as_completed_label(): void
    {
        $this->assertSame('accomplished', IaResolution::completedStatusValueForTeam('rpwsis_team'));
        $this->assertSame('Accomplished', IaResolution::completedStatusLabelForTeam('rpwsis_team'));
        $this->assertSame('Not Accomplished', IaResolution::pendingStatusLabelForTeam('rpwsis_team'));
        $this->assertSame('accomplished', IaResolution::normalizeStatusForTeam('validated', 'rpwsis_team'));
        $this->assertSame('Accomplished', IaResolution::displayStatusLabel('validated', 'rpwsis_team'));
        $this->assertSame('Accomplished', IaResolution::displayStatusLabel('accomplished', 'pao_team'));
        $this->assertSame('Not Accomplished', IaResolution::displayStatusLabel('not-validated', 'pao_team'));
    }
}
