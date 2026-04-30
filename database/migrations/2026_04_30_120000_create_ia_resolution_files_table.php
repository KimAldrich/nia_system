<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ia_resolution_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ia_resolution_id')->constrained('ia_resolutions')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->timestamps();
        });

        $existingResolutions = DB::table('ia_resolutions')
            ->whereNotNull('file_path')
            ->whereNotNull('original_name')
            ->get();

        $attachments = $existingResolutions
            ->map(function ($resolution) {
                return [
                    'ia_resolution_id' => $resolution->id,
                    'file_path' => $resolution->file_path,
                    'original_name' => $resolution->original_name,
                    'created_at' => $resolution->created_at,
                    'updated_at' => $resolution->updated_at,
                ];
            })
            ->all();

        if (!empty($attachments)) {
            DB::table('ia_resolution_files')->insert($attachments);
        }

        $duplicateGroups = DB::table('ia_resolutions')
            ->select('team', 'title')
            ->groupBy('team', 'title')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $rows = DB::table('ia_resolutions')
                ->where('team', $group->team)
                ->where('title', $group->title)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get();

            $keeper = $rows->first();
            $duplicates = $rows->skip(1)->pluck('id');

            if ($duplicates->isNotEmpty()) {
                DB::table('ia_resolution_files')
                    ->whereIn('ia_resolution_id', $duplicates)
                    ->update(['ia_resolution_id' => $keeper->id]);

                DB::table('ia_resolutions')
                    ->whereIn('id', $duplicates)
                    ->delete();
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_resolution_files');
    }
};
