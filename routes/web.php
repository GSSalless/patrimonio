<?php
/**
 * Rotas web da aplicação (estilo Laravel: routes/web.php).
 * @var Router $router
 */

// Autenticação
$router->get('',          [AuthController::class, 'showLogin']);
$router->get('login',     [AuthController::class, 'showLogin']);
$router->post('login',    [AuthController::class, 'login']);
$router->get('logout',    [AuthController::class, 'logout']);

// Dashboard
$router->get('dashboard', [DashboardController::class, 'index']);
$router->get('gestao-geral', [GestaoGeralController::class, 'index']);

// Agenda e Alertas (Módulo 14)
$router->get('agenda', [AgendaController::class, 'index']);

// Clientes (admin)
$router->get('clientes',         [ClientesController::class, 'index']);
$router->get('clientes/novo',    [ClientesController::class, 'novo']);
$router->post('clientes/novo',   [ClientesController::class, 'novo']);
$router->get('clientes/editar',  [ClientesController::class, 'editar']);
$router->post('clientes/editar', [ClientesController::class, 'editar']);
$router->get('clientes/item-remover', [ClientesController::class, 'itemRemover']);

// Imóveis
$router->get('imoveis',                [ImoveisController::class, 'index']);
$router->get('imoveis/novo',           [ImoveisController::class, 'novo']);
$router->post('imoveis/novo',          [ImoveisController::class, 'novo']);
$router->get('imoveis/editar',         [ImoveisController::class, 'editar']);
$router->post('imoveis/editar',        [ImoveisController::class, 'editar']);
$router->get('imoveis/ficha',          [ImoveisController::class, 'ficha']);
$router->get('imoveis/pendencias-pdf', [ImoveisController::class, 'pendenciasPdf']);

// Veículos
$router->get('veiculos',                [VeiculosController::class, 'index']);
$router->get('veiculos/novo',           [VeiculosController::class, 'novo']);
$router->post('veiculos/novo',          [VeiculosController::class, 'novo']);
$router->get('veiculos/editar',         [VeiculosController::class, 'editar']);
$router->post('veiculos/editar',        [VeiculosController::class, 'editar']);
$router->get('veiculos/pendencias-pdf', [VeiculosController::class, 'pendenciasPdf']);

// Outros bens
$router->get('outros',         [OutrosController::class, 'index']);
$router->get('outros/novo',    [OutrosController::class, 'novo']);
$router->post('outros/novo',   [OutrosController::class, 'novo']);
$router->get('outros/editar',  [OutrosController::class, 'editar']);
$router->post('outros/editar', [OutrosController::class, 'editar']);

// Reformas
$router->get('reformas/nova',    [ReformasController::class, 'nova']);
$router->post('reformas/nova',   [ReformasController::class, 'nova']);
$router->get('reformas/editar',  [ReformasController::class, 'editar']);
$router->post('reformas/editar', [ReformasController::class, 'editar']);

// Manutenções (imóvel)
$router->get('manutencoes/nova',    [ManutencoesController::class, 'nova']);
$router->post('manutencoes/nova',   [ManutencoesController::class, 'nova']);
$router->get('manutencoes/editar',  [ManutencoesController::class, 'editar']);
$router->post('manutencoes/editar', [ManutencoesController::class, 'editar']);

// Veículos — históricos (abastecimentos, manutenções, sinistros)
$router->get('veiculos/abastecimento',   [VeiculosController::class, 'abastecimento']);
$router->post('veiculos/abastecimento',  [VeiculosController::class, 'abastecimento']);
$router->get('veiculos/manutencao',      [VeiculosController::class, 'manutencao']);
$router->post('veiculos/manutencao',     [VeiculosController::class, 'manutencao']);
$router->get('veiculos/sinistro',        [VeiculosController::class, 'sinistro']);
$router->post('veiculos/sinistro',       [VeiculosController::class, 'sinistro']);

// Outros bens — históricos (manutenções, avaliações)
$router->get('outros/manutencao',   [OutrosController::class, 'manutencao']);
$router->post('outros/manutencao',  [OutrosController::class, 'manutencao']);
$router->get('outros/avaliacao',    [OutrosController::class, 'avaliacao']);
$router->post('outros/avaliacao',   [OutrosController::class, 'avaliacao']);

// Contas Financeiras (Módulo 09)
$router->get('contas',         [ContasController::class, 'index']);
$router->get('contas/novo',    [ContasController::class, 'novo']);
$router->post('contas/novo',   [ContasController::class, 'novo']);
$router->get('contas/editar',  [ContasController::class, 'editar']);
$router->post('contas/editar', [ContasController::class, 'editar']);
$router->get('contas/saldo',   [ContasController::class, 'saldo']);
$router->post('contas/saldo',  [ContasController::class, 'saldo']);

// Locação
$router->get('locacao/novo',  [LocacaoController::class, 'novo']);
$router->post('locacao/novo', [LocacaoController::class, 'novo']);

// Seguros (Módulo 11)
$router->get('seguros',        [SegurosController::class, 'index']);
$router->get('seguros/novo',   [SegurosController::class, 'novo']);
$router->post('seguros/novo',  [SegurosController::class, 'novo']);
$router->get('seguros/editar', [SegurosController::class, 'editar']);
$router->post('seguros/editar',[SegurosController::class, 'editar']);

// Empresas (Módulo 03)
$router->get('empresas',              [EmpresasController::class, 'index']);
$router->get('empresas/novo',         [EmpresasController::class, 'novo']);
$router->post('empresas/novo',        [EmpresasController::class, 'novo']);
$router->get('empresas/editar',       [EmpresasController::class, 'editar']);
$router->post('empresas/editar',      [EmpresasController::class, 'editar']);
$router->post('empresas/socio',       [EmpresasController::class, 'socio']);
$router->get('empresas/socio-remover',[EmpresasController::class, 'socioRemover']);

// Financeiro
$router->get('financeiro/iptu',               [FinanceiroController::class, 'iptu']);
$router->post('financeiro/iptu',              [FinanceiroController::class, 'iptu']);
$router->get('financeiro/lancamento',         [FinanceiroController::class, 'lancamento']);
$router->post('financeiro/lancamento',        [FinanceiroController::class, 'lancamento']);
$router->get('financeiro/fatura-condominio',  [FinanceiroController::class, 'faturaCondominio']);
$router->post('financeiro/fatura-condominio', [FinanceiroController::class, 'faturaCondominio']);

// Condomínios
$router->get('condominios/novo',     [CondominiosController::class, 'novo']);
$router->post('condominios/novo',    [CondominiosController::class, 'novo']);
$router->get('condominios/editar',   [CondominiosController::class, 'editar']);
$router->post('condominios/editar',  [CondominiosController::class, 'editar']);
$router->get('condominios/vincular', [CondominiosController::class, 'vincular']);

// Documentos
$router->get('documentos/upload',  [DocumentosController::class, 'upload']);
$router->post('documentos/upload', [DocumentosController::class, 'upload']);

// Patrimônio (hub de categorias)
$router->get('patrimonio', [PatrimonioController::class, 'index']);
