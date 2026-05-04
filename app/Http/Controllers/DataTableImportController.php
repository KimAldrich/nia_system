<?php

namespace App\Http\Controllers;

use App\Models\FsdeProject;
use App\Models\HydroGeoProject;
use App\Models\PaoPowData;
use App\Models\PcrStatusReport;
use App\Models\ProcurementProject;
use App\Models\RpwsisAccomplishment;
use App\Models\RpwsisAccomplishmentSummary;
use App\Models\RpwsisInfrastructure;
use App\Models\RpwsisNurseryEstablishment;
use App\Models\RpwsisSignage;
use App\Services\ExcelTableImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DataTableImportController extends Controller
{
    public function __construct(private readonly ExcelTableImportService $importer)
    {
    }

    public function import(Request $request, string $table)
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $definition = $this->definitions()[$table] ?? null;
        abort_unless($definition, 404);

        $result = $this->importer->import(
            $request->file('import_file'),
            $definition['model'],
            $definition['aliases'] ?? [],
            $definition['rules'],
            $definition['transform'] ?? null
        );

        $message = "{$result['created']} row(s) imported successfully.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} blank row(s) skipped.";
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    private function definitions(): array
    {
        $months = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

        return [
            'fs-hydro' => [
                'model' => HydroGeoProject::class,
                'rules' => [
                    'year' => ['required', 'digits:4', 'integer', 'min:2000', 'max:2100'],
                    'district' => ['required', 'string', 'max:100'],
                    'project_code' => ['required', 'string', 'max:100'],
                    'system_name' => ['required', 'string', 'max:255'],
                    'description' => ['required', 'string', 'max:2000'],
                    'municipality' => ['required', 'string', 'max:100'],
                    'status' => ['required', Rule::in(['For Schedule', 'For Interpretation', 'For Submission of Raw data', 'Relocation', 'Interpreted', 'Not Applicable', 'C/O Contractor', 'Open Source', 'With Geo-res'])],
                    'result' => ['nullable', 'string', 'max:100'],
                ],
                'aliases' => [
                    'system' => 'system_name',
                    'description_remarks' => 'description',
                    'result_feasible_or_not_feasible' => 'result',
                ],
            ],
            'fs-fsde' => [
                'model' => FsdeProject::class,
                'rules' => array_merge([
                    'year' => ['required', 'digits:4', 'integer', 'min:2000', 'max:2100'],
                    'type_of_study' => ['required', 'string', 'max:255'],
                    'project_name' => ['required', 'string', 'max:1000'],
                    'municipality' => ['required', 'string', 'max:100'],
                    'consultant' => ['required', 'string', 'max:255'],
                    'period_start' => ['nullable', 'date'],
                    'period_end' => ['nullable', 'date'],
                    'contract_amount' => ['nullable', 'numeric', 'min:0'],
                    'actual_obligation' => ['nullable', 'numeric', 'min:0'],
                    'value_of_acc' => ['nullable', 'numeric', 'min:0'],
                    'actual_expenditures' => ['nullable', 'numeric', 'min:0'],
                    'acc_year' => ['nullable', 'digits:4', 'integer', 'min:2000', 'max:2100'],
                    'remarks' => ['nullable', 'string', 'max:2000'],
                ], collect($months)->flatMap(fn ($month) => [
                    "{$month}_phy" => ['nullable', 'numeric', 'between:0,100'],
                    "{$month}_fin" => ['nullable', 'numeric', 'between:0,100'],
                ])->toArray()),
                'aliases' => [
                    'total_funding_requirement_p_000' => 'contract_amount',
                    'approved_budget_p_000' => 'contract_amount',
                    'contract_amount_p_000' => 'contract_amount',
                    'actual_obligation_p_000' => 'actual_obligation',
                    'mode_of_implementation_name_of_consultant' => 'consultant',
                    'value_of_accomplishment_p_000' => 'value_of_acc',
                    'actual_expenditures_p_000' => 'actual_expenditures',
                    'remarks' => 'remarks',
                ],
                'transform' => fn (array $payload, array $row) => $this->transformFsdeImportRow($payload, $row),
            ],
            'rpwsis-accomplishments' => [
                'model' => RpwsisAccomplishment::class,
                'rules' => [
                    'region' => ['required', 'string', 'max:100'],
                    'batch' => ['nullable', 'string', 'max:100'],
                    'allocation' => ['nullable', 'string', 'max:255'],
                    'nis' => ['nullable', 'string', 'max:255'],
                    'activity' => ['required', 'string', 'max:255'],
                    'remarks' => ['nullable', 'string', 'max:1000'],
                    'amount' => ['nullable', 'numeric', 'min:0'],
                    'phy' => ['nullable', 'numeric', 'between:0,100'],
                    'fin' => ['nullable', 'numeric', 'between:0,100'],
                    'exp' => ['nullable', 'numeric', 'min:0'],
                ] + collect(range(1, 12))->mapWithKeys(fn ($index) => ["c{$index}" => ['nullable', 'string', 'max:255']])->toArray(),
            ],
            'rpwsis-summary' => [
                'model' => RpwsisAccomplishmentSummary::class,
                'rules' => [
                    'region' => ['required', 'string', 'max:100'],
                    'province' => ['required', 'string', 'max:100'],
                    'municipality' => ['required', 'string', 'max:100'],
                    'barangay' => ['required', 'string', 'max:255'],
                    'plantation_type' => ['required', 'string', 'max:255'],
                    'year_established' => ['required', 'digits:4', 'integer', 'min:1900', 'max:2100'],
                    'target_area_1' => ['nullable', 'string', 'max:255'],
                    'area_planted' => ['nullable', 'string', 'max:255'],
                    'species_planted' => ['nullable', 'string', 'max:5000'],
                    'spacing' => ['nullable', 'string', 'max:255'],
                    'maintenance' => ['nullable', 'string', 'max:255'],
                    'target_area_2' => ['nullable', 'string', 'max:255'],
                    'actual_area' => ['nullable', 'string', 'max:255'],
                    'mortality_rate' => ['nullable', 'string', 'max:255'],
                    'species_replanted' => ['nullable', 'string', 'max:5000'],
                    'nis_name' => ['nullable', 'string', 'max:255'],
                    'remarks' => ['nullable', 'string', 'max:5000'],
                ],
                'aliases' => ['type' => 'plantation_type', 'year' => 'year_established', 'nis' => 'nis_name'],
            ],
            'rpwsis-nursery' => [
                'model' => RpwsisNurseryEstablishment::class,
                'rules' => [
                    'region' => ['required', 'string', 'max:100'],
                    'province' => ['required', 'string', 'max:100'],
                    'municipality' => ['required', 'string', 'max:100'],
                    'barangay' => ['required', 'string', 'max:255'],
                    'x_coordinates' => ['nullable', 'string', 'max:255'],
                    'y_coordinates' => ['nullable', 'string', 'max:255'],
                    'seedlings_produced' => ['nullable', 'string', 'max:255'],
                    'nursery_type' => ['required', 'string', 'max:255'],
                    'nis_name' => ['nullable', 'string', 'max:255'],
                    'remarks' => ['nullable', 'string', 'max:5000'],
                ],
                'aliases' => ['x_coord' => 'x_coordinates', 'y_coord' => 'y_coordinates', 'nis' => 'nis_name', 'type' => 'nursery_type'],
            ],
            'rpwsis-signages' => [
                'model' => RpwsisSignage::class,
                'rules' => [
                    'region' => ['required', 'string', 'max:100'],
                    'province' => ['required', 'string', 'max:100'],
                    'municipality' => ['required', 'string', 'max:100'],
                    'barangay' => ['required', 'string', 'max:255'],
                    'x_coordinates' => ['nullable', 'string', 'max:255'],
                    'y_coordinates' => ['nullable', 'string', 'max:255'],
                    'signage_type' => ['required', 'string', 'max:255'],
                    'nis_name' => ['nullable', 'string', 'max:255'],
                    'remarks' => ['nullable', 'string', 'max:5000'],
                ],
                'aliases' => ['x_coord' => 'x_coordinates', 'y_coord' => 'y_coordinates', 'nis' => 'nis_name', 'type' => 'signage_type'],
            ],
            'rpwsis-infrastructure' => [
                'model' => RpwsisInfrastructure::class,
                'rules' => [
                    'region' => ['required', 'string', 'max:100'],
                    'province' => ['required', 'string', 'max:100'],
                    'municipality' => ['required', 'string', 'max:100'],
                    'barangay' => ['required', 'string', 'max:5000'],
                    'x_coordinates' => ['nullable', 'string', 'max:5000'],
                    'y_coordinates' => ['nullable', 'string', 'max:5000'],
                    'infrastructure_type' => ['required', 'string', 'max:5000'],
                    'nis_name' => ['nullable', 'string', 'max:255'],
                    'remarks' => ['nullable', 'string', 'max:5000'],
                ],
                'aliases' => ['x_coord' => 'x_coordinates', 'y_coord' => 'y_coordinates', 'nis' => 'nis_name', 'type' => 'infrastructure_type'],
            ],
            'cm-procurement' => [
                'model' => ProcurementProject::class,
                'rules' => [
                    'category' => ['required', 'string', 'max:255'],
                    'proj_no' => ['required', 'string', 'max:50'],
                    'name_of_project' => ['required', 'string', 'max:1000'],
                    'municipality' => ['required', 'string', 'max:100'],
                    'allocation' => ['nullable', 'numeric', 'min:0'],
                    'abc' => ['nullable', 'numeric', 'min:0'],
                    'bid_out' => ['nullable', 'integer', 'min:0'],
                    'for_bidding' => ['nullable', 'integer', 'min:0'],
                    'date_of_bidding' => ['nullable', 'date'],
                    'awarded' => ['nullable', 'integer', 'min:0'],
                    'date_of_award' => ['nullable', 'date'],
                    'ca_date' => ['nullable', 'date'],
                    'ntp_date' => ['nullable', 'date'],
                    'contract_no' => ['nullable', 'string', 'max:100'],
                    'contract_amount' => ['nullable', 'numeric', 'min:0'],
                    'name_of_contractor' => ['nullable', 'string', 'max:255'],
                    'remarks' => ['nullable', 'string', 'max:1000'],
                    'project_description' => ['nullable', 'string', 'max:2000'],
                ],
                'aliases' => [
                    'project_no' => 'proj_no',
                    'project_number' => 'proj_no',
                    'no_of_proj' => 'proj_no',
                    'fy_2026_allocation' => 'allocation',
                    'approved_budget_of_the_contract' => 'abc',
                    'contract_agreement_date' => 'ca_date',
                    'notice_to_proceed_date' => 'ntp_date',
                ],
                'transform' => fn (array $payload, array $row, array $headers, array &$state) => $this->transformProcurementImportRow($payload, $row, $state),
            ],
            'pcr-status' => [
                'model' => PcrStatusReport::class,
                'rules' => [
                    'fund_source' => ['required', 'string', 'max:50'],
                    'no_of_contracts' => ['required', 'integer', 'min:0'],
                    'allocation' => ['required', 'numeric', 'min:0'],
                    'no_of_pcr_prepared' => ['required', 'integer', 'min:0'],
                    'no_of_pcr_submitted_to_regional_office' => ['required', 'integer', 'min:0'],
                    'accomplishment_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
                    'for_signing_of_ia_chief_dm_rm' => ['required', 'integer', 'min:0'],
                    'for_submission_to_ro1' => ['required', 'integer', 'min:0'],
                    'not_yet_prepared_pending_details' => ['required', 'integer', 'min:0'],
                ],
            ],
            'pao-pow' => [
                'model' => PaoPowData::class,
                'rules' => [
                    'district' => ['required', Rule::in(['District 1', 'District 2', 'District 3', 'District 4', 'District 5', 'District 6'])],
                    'no_of_projects' => ['required', 'integer', 'min:0'],
                    'total_allocation' => ['required', 'numeric', 'min:0'],
                    'no_of_plans_received' => ['required', 'integer', 'min:0'],
                    'no_of_project_estimate_received' => ['required', 'integer', 'min:0'],
                    'pow_received' => ['required', 'integer', 'min:0'],
                    'pow_approved' => ['required', 'integer', 'min:0'],
                    'pow_submitted' => ['required', 'integer', 'min:0'],
                    'ongoing_pow_preparation' => ['required', 'integer', 'min:0'],
                    'pow_for_submission' => ['required', 'integer', 'min:0'],
                    'remarks' => ['nullable', 'string', 'max:2000'],
                ],
                'transform' => fn (array $payload, array $row) => $this->transformPowImportRow($payload, $row),
            ],
        ];
    }

    private function transformPowImportRow(array $payload, array $row): ?array
    {
        $district = $this->cleanImportText($payload['district'] ?? $row['A'] ?? null);

        if ($this->isImportHeading($district, ['PROGRAM OF WORKS STATUS MONITORING', 'DISTRICT', 'TOTAL'])) {
            return null;
        }

        $payload['district'] = $this->normalizePowDistrict($district);
        $payload['pow_received'] = $payload['pow_received'] ?? $row['F'] ?? null;
        $payload['pow_approved'] = $payload['pow_approved'] ?? $row['G'] ?? null;
        $payload['pow_submitted'] = $payload['pow_submitted'] ?? $row['H'] ?? null;

        foreach ([
            'no_of_projects',
            'no_of_plans_received',
            'no_of_project_estimate_received',
            'pow_received',
            'pow_approved',
            'pow_submitted',
            'ongoing_pow_preparation',
            'pow_for_submission',
        ] as $field) {
            $payload[$field] = $this->importInteger($payload[$field] ?? 0);
        }

        $payload['total_allocation'] = $this->importNumber($payload['total_allocation'] ?? 0);

        return $payload;
    }

    private function transformProcurementImportRow(array $payload, array $row, array &$state): ?array
    {
        $firstCell = $this->cleanImportText($row['A'] ?? null);

        if ($this->isImportHeading($firstCell, ['PANGASINAN IMO', 'NO. OF PROJ.', 'FY 2026 (ALLOCATION)'])) {
            return null;
        }

        $hasOnlyFirstCell = $firstCell !== null && collect($row)
            ->except(['A'])
            ->every(fn ($value) => $this->cleanImportText($value) === null);

        if ($hasOnlyFirstCell) {
            $state['category'] = $firstCell;
            return null;
        }

        $payload['category'] = $payload['category'] ?? $state['category'] ?? null;
        $payload['allocation'] = $this->importNumber($payload['allocation'] ?? $row['D'] ?? null);
        $payload['abc'] = $this->importNumber($payload['abc'] ?? $row['E'] ?? null);
        $payload['contract_amount'] = $this->importNumber($payload['contract_amount'] ?? null);

        foreach (['bid_out', 'for_bidding', 'awarded'] as $field) {
            $payload[$field] = $this->importInteger($payload[$field] ?? null);
        }

        foreach (['date_of_bidding', 'date_of_award', 'ca_date', 'ntp_date'] as $field) {
            $payload[$field] = $this->importDate($payload[$field] ?? null);
        }

        return $payload;
    }

    private function transformFsdeImportRow(array $payload, array $row): ?array
    {
        $year = $this->cleanImportText($payload['year'] ?? $row['A'] ?? null);

        if ($this->isImportHeading($year, ['PANGASINAN', 'TOTAL FOR PANGASINAN', 'YEAR'])) {
            return null;
        }

        foreach (['period_start', 'period_end'] as $field) {
            $payload[$field] = $this->importDate($payload[$field] ?? null);
        }

        $payload['period_start'] = $this->importDate($payload['period_start'] ?? $row['H'] ?? null);
        $payload['period_end'] = $this->importDate($payload['period_end'] ?? $row['I'] ?? null);

        foreach (['contract_amount', 'actual_obligation', 'value_of_acc', 'actual_expenditures'] as $field) {
            $payload[$field] = $this->importNumber($payload[$field] ?? null);
        }

        foreach (['jan_phy', 'jan_fin', 'feb_phy', 'feb_fin', 'mar_phy', 'mar_fin', 'apr_phy', 'apr_fin', 'may_phy', 'may_fin', 'jun_phy', 'jun_fin', 'jul_phy', 'jul_fin', 'aug_phy', 'aug_fin', 'sep_phy', 'sep_fin', 'oct_phy', 'oct_fin', 'nov_phy', 'nov_fin', 'dec_phy', 'dec_fin'] as $field) {
            if (array_key_exists($field, $payload)) {
                $payload[$field] = $this->importNumber($payload[$field]);
            }
        }

        $payload['acc_year'] = $payload['acc_year'] ?? (preg_match('/^\d{4}$/', (string) $year) ? $year : null);

        return $payload;
    }

    private function importDate(mixed $value): mixed
    {
        $value = $this->cleanImportText($value);
        if ($value === null) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function importNumber(mixed $value): mixed
    {
        $value = $this->cleanImportText($value);
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/[^\d.\-]/', '', $value) ?? $value;
        $normalized = trim($normalized);

        return is_numeric($normalized) ? $normalized : $value;
    }

    private function importInteger(mixed $value): mixed
    {
        $value = $this->importNumber($value);

        return is_numeric($value) ? (int) $value : $value;
    }

    private function normalizePowDistrict(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (preg_match('/(\d+)/', $value, $matches)) {
            return 'District ' . $matches[1];
        }

        return $value;
    }

    private function cleanImportText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function isImportHeading(?string $value, array $headings): bool
    {
        if ($value === null) {
            return false;
        }

        return in_array(strtoupper($value), $headings, true);
    }
}
