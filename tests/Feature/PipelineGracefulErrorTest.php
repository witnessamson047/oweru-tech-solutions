<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * A data-layer failure (DB down, missing table, bad migration) must show
 * staff a friendly fallback page — not a raw Laravel crash. The dashboard
 * already had this protection; the pipeline now shares the same view.
 */
class PipelineGracefulErrorTest extends TestCase
{
    public function test_pipeline_shows_friendly_page_when_data_layer_fails(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin);

        // Simulate the data layer being broken: the table the pipeline reads
        // no longer exists. Without the try/catch this surfaces as a raw
        // QueryException / 500 error page.
        Schema::drop('enquiries');

        $response = $this->get(route('admin.pipeline.index'));

        $response->assertOk();
        $response->assertSee('problem connecting to the database', false);
    }
}
