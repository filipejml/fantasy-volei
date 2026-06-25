<?php

namespace Tests\Feature;

use App\Models\Jogador;
use App\Models\Posicao;
use App\Models\Selecao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_admin_resources(): void
    {
        $user = User::factory()->create(['role' => 1]);

        $this->actingAs($user)
            ->get(route('admin.selecoes.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.jogadores.index'))
            ->assertForbidden();
    }

    public function test_admin_can_manage_selecoes(): void
    {
        $admin = User::factory()->create(['role' => 0]);

        $this->actingAs($admin)
            ->get(route('admin.selecoes.index'))
            ->assertOk()
            ->assertSee('Atualizar da Volleyball World');

        $response = $this->actingAs($admin)->post(route('admin.selecoes.store'), [
            'nome' => 'Brasil',
            'genero' => 'masculino',
            'sigla' => 'BRA',
            'bandeira' => 'https://example.com/brasil.png',
            'ativo' => 1,
        ]);

        $selecao = Selecao::firstOrFail();
        $response->assertRedirect(route('admin.selecoes.show', $selecao));
        $this->assertDatabaseHas('selecoes', ['nome' => 'Brasil', 'sigla' => 'BRA']);

        $this->actingAs($admin)->put(route('admin.selecoes.update', $selecao), [
            'nome' => 'Brasil Vôlei',
            'genero' => 'masculino',
            'sigla' => 'BRA',
            'bandeira' => 'https://example.com/brasil.png',
            'ativo' => 1,
        ])->assertRedirect(route('admin.selecoes.show', $selecao));

        $this->assertDatabaseHas('selecoes', ['id' => $selecao->id, 'nome' => 'Brasil Vôlei']);

        $this->actingAs($admin)
            ->delete(route('admin.selecoes.destroy', $selecao))
            ->assertRedirect(route('admin.selecoes.index'));

        $this->assertDatabaseMissing('selecoes', ['id' => $selecao->id]);
    }

    public function test_admin_can_filter_selecoes_by_name_and_gender(): void
    {
        $admin = User::factory()->create(['role' => 0]);

        Selecao::create(['nome' => 'Brasil', 'sigla' => 'BRA', 'genero' => 'masculino', 'ativo' => true]);
        Selecao::create(['nome' => 'Brasil Feminino', 'sigla' => 'BRF', 'genero' => 'feminino', 'ativo' => true]);
        Selecao::create(['nome' => 'Itália', 'sigla' => 'ITA', 'genero' => 'masculino', 'ativo' => true]);

        $this->actingAs($admin)
            ->get(route('admin.selecoes.index', ['selecao' => 'Bra', 'genero' => 'feminino']))
            ->assertOk()
            ->assertSee('Brasil Feminino')
            ->assertSee('value="Bra"', false)
            ->assertDontSee('Itália')
            ->assertDontSee('>Brasil</div>', false);
    }

    public function test_admin_can_deactivate_selection_and_its_players_are_deactivated(): void
    {
        $admin = User::factory()->create(['role' => 0]);
        $selecao = Selecao::create(['nome' => 'Brasil', 'sigla' => 'BRA', 'genero' => 'masculino', 'ativo' => true]);
        $posicao = Posicao::create(['nome' => 'Ponteiro', 'sigla' => 'OH']);
        $jogador = Jogador::create([
            'selecao_id' => $selecao->id,
            'posicao_id' => $posicao->id,
            'nome' => 'Lucarelli',
            'genero' => 'masculino',
            'valor_creditos' => 10,
            'media_pontos' => 0,
            'ativo' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.selecoes.status', $selecao), ['ativo' => 0])
            ->assertRedirect();

        $this->assertDatabaseHas('selecoes', ['id' => $selecao->id, 'ativo' => false]);
        $this->assertDatabaseHas('jogadors', ['id' => $jogador->id, 'ativo' => false]);

        $this->actingAs(User::factory()->create())
            ->get(route('times.create', ['genero' => 'masculino']))
            ->assertOk()
            ->assertDontSee('Lucarelli');
    }

    public function test_admin_can_manage_jogadores(): void
    {
        $admin = User::factory()->create(['role' => 0]);
        $selecao = Selecao::create([
            'nome' => 'Brasil',
            'genero' => 'feminino',
            'sigla' => 'BRA',
            'ativo' => true,
        ]);
        $posicao = Posicao::create([
            'nome' => 'Ponteiro',
            'sigla' => 'OH',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.jogadores.index'))
            ->assertOk()
            ->assertSee('Atualizar da Volleyball World');

        $response = $this->actingAs($admin)->post(route('admin.jogadores.store'), [
            'selecao_id' => $selecao->id,
            'posicao_id' => $posicao->id,
            'nome' => 'Jogadora Teste',
            'genero' => 'feminino',
            'valor_creditos' => 15.50,
            'media_pontos' => 8.25,
            'idade' => 25,
            'altura' => 1.90,
            'foto' => 'https://example.com/jogadora.png',
            'ativo' => 1,
        ]);

        $jogador = Jogador::firstOrFail();
        $response->assertRedirect(route('admin.jogadores.show', $jogador));
        $this->assertDatabaseHas('jogadors', ['nome' => 'Jogadora Teste']);

        $this->actingAs($admin)->put(route('admin.jogadores.update', $jogador), [
            'selecao_id' => $selecao->id,
            'posicao_id' => $posicao->id,
            'nome' => 'Jogadora Atualizada',
            'genero' => 'feminino',
            'valor_creditos' => 17.00,
            'media_pontos' => 9.00,
            'idade' => 26,
            'altura' => 1.90,
            'ativo' => 1,
        ])->assertRedirect(route('admin.jogadores.show', $jogador));

        $this->assertDatabaseHas('jogadors', ['id' => $jogador->id, 'nome' => 'Jogadora Atualizada']);

        $this->actingAs($admin)
            ->delete(route('admin.jogadores.destroy', $jogador))
            ->assertRedirect(route('admin.jogadores.index'));

        $this->assertDatabaseMissing('jogadors', ['id' => $jogador->id]);
    }

    public function test_admin_can_filter_jogadores_by_position(): void
    {
        $admin = User::factory()->create(['role' => 0]);
        $selecao = Selecao::create(['nome' => 'Brasil', 'genero' => 'masculino', 'sigla' => 'BRA', 'ativo' => true]);
        $ponteiro = Posicao::create(['nome' => 'Ponteiro', 'sigla' => 'OH']);
        $libero = Posicao::create(['nome' => 'Líbero', 'sigla' => 'L']);

        Jogador::create([
            'selecao_id' => $selecao->id,
            'posicao_id' => $ponteiro->id,
            'nome' => 'Lucarelli',
            'genero' => 'masculino',
            'valor_creditos' => 10,
            'media_pontos' => 0,
            'ativo' => true,
        ]);

        Jogador::create([
            'selecao_id' => $selecao->id,
            'posicao_id' => $libero->id,
            'nome' => 'Maique',
            'genero' => 'masculino',
            'valor_creditos' => 10,
            'media_pontos' => 0,
            'ativo' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.jogadores.index', ['posicao_id' => $libero->id]))
            ->assertOk()
            ->assertSee('Todas as posições')
            ->assertSee('Maique')
            ->assertSee('L')
            ->assertDontSee('Lucarelli');
    }

    public function test_admin_can_sort_jogadores_by_name_and_value(): void
    {
        $admin = User::factory()->create(['role' => 0]);
        $selecao = Selecao::create(['nome' => 'Brasil', 'genero' => 'masculino', 'sigla' => 'BRA', 'ativo' => true]);
        $posicao = Posicao::create(['nome' => 'Ponteiro', 'sigla' => 'OH']);

        Jogador::create([
            'selecao_id' => $selecao->id,
            'posicao_id' => $posicao->id,
            'nome' => 'Zed',
            'genero' => 'masculino',
            'valor_creditos' => 5,
            'media_pontos' => 0,
            'ativo' => true,
        ]);

        Jogador::create([
            'selecao_id' => $selecao->id,
            'posicao_id' => $posicao->id,
            'nome' => 'Ana',
            'genero' => 'masculino',
            'valor_creditos' => 20,
            'media_pontos' => 0,
            'ativo' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.jogadores.index', ['ordenar' => 'nome', 'direcao' => 'asc']))
            ->assertOk()
            ->assertSeeTextInOrder(['Ana', 'Zed']);

        $this->actingAs($admin)
            ->get(route('admin.jogadores.index', ['ordenar' => 'valor_creditos', 'direcao' => 'desc']))
            ->assertOk()
            ->assertSeeTextInOrder(['Ana', 'C$ 20,00', 'Zed', 'C$ 5,00']);
    }
}
