<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MapUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_upload_rejects_unsupported_file_extensions_with_clear_message(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
            'agreed_to_terms' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['agreed_to_terms' => true])
            ->post(route('map.upload'), [
                'category' => 'Irrigated Area',
                'target_folder' => 'Agno',
                'files' => [
                    UploadedFile::fake()->create('unsupported.pdf', 10, 'application/pdf'),
                ],
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Unsupported file detected. Please upload only: .geojson, .json, .kml, .kmz, .zip, .shp, .shx, .dbf, .prj, or .cpg.',
        ]);
    }
}
