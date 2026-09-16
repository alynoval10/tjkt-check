<?php

namespace Tests\Feature;

use App\Filament\Pages\PenilaianMassal;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PenilaianMassalTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_renders_with_material_selection_disabled_until_class_is_selected(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs(User::factory()->create());

        Livewire::test(PenilaianMassal::class)
            ->assertSuccessful()
            ->assertSee('Pilih kelas')
            ->assertSeeHtml('disabled');
    }
}
