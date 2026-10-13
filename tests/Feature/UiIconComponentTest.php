<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UiIconComponentTest extends TestCase
{
    public function test_icon_renders_correct_svg_and_path(): void
    {
        $rendered = Blade::render('<x-ui.icon name="check" class="w-5 h-5 text-success" />');

        $this->assertStringContainsString('<svg', $rendered);
        $this->assertStringContainsString('aria-hidden="true"', $rendered);
        $this->assertStringContainsString('viewBox="0 0 24 24"', $rendered);
        $this->assertStringContainsString('w-5 h-5 text-success', $rendered);
        $this->assertStringContainsString('M5 13l4 4L19 7', $rendered);
    }

    public function test_icon_uses_default_size_when_not_provided(): void
    {
        $rendered = Blade::render('<x-ui.icon name="search" class="text-primary" />');

        $this->assertStringContainsString('w-4 h-4', $rendered);
        $this->assertStringContainsString('text-primary', $rendered);
        $this->assertStringContainsString('M21 21l-6-6m2-5', $rendered);
    }

    public function test_icon_resolves_common_aliases(): void
    {
        $add = Blade::render('<x-ui.icon name="add" />');
        $this->assertStringContainsString('M12 4v16m8-8H4', $add); // plus

        $trash = Blade::render('<x-ui.icon name="delete" />');
        $this->assertStringContainsString('M19 7l-.867', $trash); // trash

        $close = Blade::render('<x-ui.icon name="close" />');
        $this->assertStringContainsString('M6 18L18 6', $close); // x-mark

        $search = Blade::render('<x-ui.icon name="cari" />');
        $this->assertStringContainsString('M21 21l-6-6m2-5', $search); // magnifying-glass
    }
}
