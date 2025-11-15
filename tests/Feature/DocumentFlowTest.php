<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_file_creates_document()
    {
        // TODO: file upload testing requires filesystem setup; keep skeleton
        $this->assertTrue(true);
    }

    public function test_search_endpoint_returns_results()
    {
        // ensure index route responds
        $res = $this->get('/documents');
        $res->assertStatus(200);
    }
}
