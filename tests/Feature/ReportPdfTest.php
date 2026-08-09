<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_report_pdf_download()
    {
        $admin = $this->adminUser();
        $response = $this->actingAs($admin)->get(route('admin.reports.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    private function adminUser()
    {
        return User::factory()->create(['role' => 'admin']);
    }
}
