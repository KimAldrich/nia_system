<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelTableImportService
{
    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     * @param array<string, string> $aliases
     * @param array<string, mixed> $rules
     * @param callable(array<string, mixed>, array<string, mixed>, array<string, string|null>, array<string, mixed>): array<string, mixed>|null $transform
     * @return array{created:int, skipped:int}
     */
    public function import(
        UploadedFile $file,
        string $modelClass,
        array $aliases,
        array $rules,
        ?callable $transform = null
    ): array {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'import_file' => 'The spreadsheet must include a header row and at least one data row.',
            ]);
        }

        [$headers, $dataRows] = $this->splitHeaderAndRows($rows, $aliases, array_keys($rules));
        $created = 0;
        $skipped = 0;
        $errors = [];

        $state = [];

        foreach ($dataRows as $rowNumber => $row) {
            if ($this->isBlankRow($row)) {
                $skipped++;
                continue;
            }

            $payload = [];
            foreach ($headers as $column => $field) {
                if ($field === null) {
                    continue;
                }

                $payload[$field] = $this->normalizeValue($row[$column] ?? null);
            }

            $payload = $transform ? $transform($payload, $row, $headers, $state) : $payload;

            if ($payload === null) {
                $skipped++;
                continue;
            }

            $validator = Validator::make($payload, $rules);
            if ($validator->fails()) {
                $errors[] = 'Row ' . $rowNumber . ': ' . $validator->errors()->first();
                continue;
            }

            $modelClass::create($validator->validated());
            $created++;
        }

        if ($created === 0 && $errors) {
            throw ValidationException::withMessages([
                'import_file' => implode(' ', array_slice($errors, 0, 5)),
            ]);
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, string> $aliases
     * @param array<int, string> $fields
     * @return array{0: array<string, string|null>, 1: array<int, array<string, mixed>>}
     */
    private function splitHeaderAndRows(array $rows, array $aliases, array $fields): array
    {
        $allowedFields = array_fill_keys($fields, true);

        foreach ($rows as $index => $row) {
            $headers = $this->headers($row, $aliases);
            $matches = collect($headers)
                ->filter(fn ($field) => $field !== null && isset($allowedFields[$field]))
                ->count();

            if ($matches >= 2) {
                return [$headers, array_slice($rows, $index + 1, null, true)];
            }
        }

        throw ValidationException::withMessages([
            'import_file' => 'No recognizable header row was found. Use column headers that match the table fields.',
        ]);
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, string> $aliases
     * @return array<string, string|null>
     */
    private function headers(array $row, array $aliases): array
    {
        $headers = [];
        $aliases = array_merge([
            'no_of_proj' => 'proj_no',
            'project_no' => 'proj_no',
            'proposed_project_name' => 'project_name',
            'type_of_study_activity' => 'type_of_study',
            'mode_of_implementation_name_of_consultant' => 'consultant',
            'start_of_activity' => 'period_start',
            'end_of_activity' => 'period_end',
            'approved_budget_of_the_contract' => 'abc',
            'contract_agreement_date' => 'ca_date',
            'notice_to_proceed_date' => 'ntp_date',
            'accomplishment_prepared_no_of_contracts' => 'accomplishment_percentage',
            'no_of_pow_prepared' => 'pow_received',
            'no_of_pow_approved' => 'pow_approved',
            'no_of_pow_submitted' => 'pow_submitted',
            'on_going_pow_preparation' => 'ongoing_pow_preparation',
        ], $aliases);

        foreach ($row as $column => $value) {
            $key = $this->key((string) $value);
            $headers[$column] = $key === '' ? null : ($aliases[$key] ?? $key);
        }

        return $headers;
    }

    private function key(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function isBlankRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->normalizeValue($value) !== null) {
                return false;
            }
        }

        return true;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_string($value)) {
            $value = trim($value);
            return $value === '' ? null : $value;
        }

        return $value;
    }
}
