<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_and_availability_filters_work_together_and_exports_match(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Available academic book',
            'category' => 'Academic',
            'availability_status' => 'Available',
        ]);
        Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Unavailable academic book',
            'category' => 'Academic',
            'availability_status' => 'Unavailable',
        ]);
        Book::factory()->create([
            'user_id' => $user->id,
            'title' => 'Available fiction book',
            'category' => 'Fiction',
            'availability_status' => 'Available',
        ]);

        $filters = ['category' => 'Academic', 'availability_status' => 'Available'];
        $this->get(route('reports.index', $filters))
            ->assertOk()
            ->assertSee('Available academic book')
            ->assertDontSee('Unavailable academic book')
            ->assertDontSee('Available fiction book');

        $pdf = $this->get(route('reports.export.pdf', $filters))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->getContent();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('Available academic book', $pdf);
        $this->assertStringNotContainsString('Unavailable academic book', $pdf);

        $excel = $this->get(route('reports.export.excel', $filters))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->getContent();
        $this->assertStringStartsWith("PK\x03\x04", $excel);
        $sheet = $this->readZipEntry($excel, 'xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('Available academic book', $sheet);
        $this->assertStringNotContainsString('Unavailable academic book', $sheet);
    }

    public function test_reports_show_no_matches_message_and_reset_filters(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('reports.index', ['category' => 'Academic']))
            ->assertOk()
            ->assertSee('No books match the selected filters.')
            ->assertSee('Reset filters');
    }

    private function readZipEntry(string $zip, string $entryName): string
    {
        $offset = 0;
        while (substr($zip, $offset, 4) === "PK\x03\x04") {
            $header = unpack('Vsignature/vversion/vflags/vmethod/vtime/vdate/Vcrc/Vcompressed/Vsize/vnameLength/vextraLength', substr($zip, $offset, 30));
            $nameOffset = $offset + 30;
            $name = substr($zip, $nameOffset, $header['nameLength']);
            $contentOffset = $nameOffset + $header['nameLength'] + $header['extraLength'];
            if ($name === $entryName) {
                return gzinflate(substr($zip, $contentOffset, $header['compressed']));
            }
            $offset = $contentOffset + $header['compressed'];
        }

        $this->fail("The XLSX file does not contain {$entryName}.");
    }
}
