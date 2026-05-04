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
                'aliases' => ['project_no' => 'proj_no', 'project_number' => 'proj_no'],
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
            ],
        ];
    }
}
