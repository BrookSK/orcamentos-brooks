<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Models\Orcamento;
use App\Models\OrcamentoItem;
use App\Models\OrcamentoOpcao;

final class OrcamentoController
{
    public function index(): void
    {
        Logger::info('orcamentos.index');
        $orcamentos = Orcamento::all();
        $this->render('orcamentos/index', [
            'orcamentos' => $orcamentos,
        ]);
    }

    public function create(): void
    {
        Logger::info('orcamentos.create');
        $templateItems = self::templateItems();
        $this->render('orcamentos/create', [
            'orcamento' => [
                'numero_proposta' => 'P 000 00',
                'cliente_nome' => 'Cliente Teste',
                'arquiteto_nome' => 'Arquiteto Teste',
                'obra_nome' => 'Obra Teste',
                'endereco_obra' => 'Endereço Teste, 123',
                'local_obra' => 'Cidade/UF',
                'data' => date('Y-m-d'),
                'referencia' => '00',
                'area_m2' => '0,00',
                'contrato' => 'Administração',
                'tipo' => 'Administração',
                'prazo_dias' => '0',
                'rev' => '00',
                'empresa_nome' => 'Empresa Teste',
                'empresa_endereco' => 'Endereço da empresa (teste)',
                'empresa_telefone' => '(00) 0000-0000',
                'empresa_email' => '',
                'logo_path' => '',
            ],
            'use_template_items' => true,
            'template_items' => $templateItems,
            'grupos' => OrcamentoOpcao::namesByTipo('grupo'),
            'categorias' => OrcamentoOpcao::namesByTipo('categoria'),
            'unidades' => OrcamentoOpcao::namesByTipo('unidade'),
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        Logger::info('orcamentos.store.start');
        $data = Orcamento::normalize($_POST);
        $errors = Orcamento::validate($data);

        $templateItemsInput = $_POST['template_items'] ?? null;
        if ($errors) {
            Logger::warning('orcamentos.store.validation_failed', ['errors' => $errors]);
            $this->render('orcamentos/create', [
                'orcamento' => $data,
                'use_template_items' => (string)($_POST['use_template_items'] ?? '') === '1',
                'template_items' => is_array($templateItemsInput) ? $templateItemsInput : self::templateItems(),
                'grupos' => OrcamentoOpcao::namesByTipo('grupo'),
                'categorias' => OrcamentoOpcao::namesByTipo('categoria'),
                'unidades' => OrcamentoOpcao::namesByTipo('unidade'),
                'errors' => $errors,
            ]);
            return;
        }

        $id = Orcamento::create($data);
        Logger::info('orcamentos.store.created', ['id' => $id, 'numero_proposta' => $data['numero_proposta'] ?? null]);

        $useTemplateItems = (string)($_POST['use_template_items'] ?? '') === '1';
        if ($useTemplateItems) {
            $rows = [];
            if (is_array($templateItemsInput)) {
                $rows = $templateItemsInput;
            } else {
                $rows = self::templateItems();
            }

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $normalized = OrcamentoItem::normalize($row);
                OrcamentoItem::create($id, $normalized);
            }
            Logger::info('orcamentos.store.template_items_seeded', ['id' => $id]);
        }

        $this->redirect('/?route=orcamentos/show&id=' . $id);
    }

    private static function templateItems(): array
    {
        $jsonPath = __DIR__ . '/../../estimativa_custos.json';
        if (is_file($jsonPath)) {
            $raw = file_get_contents($jsonPath);
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $rows = [];
                    $ordem = 1;

                    $pushItem = static function (string $grupo, string $categoria, array $item) use (&$rows, &$ordem): void {
                        $codigo = (string)($item['codigo'] ?? '');
                        $descricao = (string)($item['descricao'] ?? '');
                        $quantidade = $item['quantidade'] ?? '';
                        $unidade = (string)($item['unidade'] ?? '');
                        $valorUnitario = $item['valor_unitario'] ?? '';

                        $q = is_numeric($quantidade) ? (float)$quantidade : 0.0;
                        $vu = is_numeric($valorUnitario) ? (float)$valorUnitario : 0.0;

                        $rows[] = [
                            'grupo' => $grupo,
                            'categoria' => $categoria,
                            'codigo' => $codigo,
                            'descricao' => $descricao,
                            'quantidade' => self::formatPtBrNumber($q, 2),
                            'unidade' => $unidade,
                            'valor_unitario' => self::formatPtBrNumber($vu, 2),
                            'ordem' => $ordem,
                        ];
                        $ordem++;
                    };

                    $walkSections = static function (string $grupo, array $sections) use (&$walkSections, $pushItem): void {
                        foreach ($sections as $section) {
                            if (!is_array($section)) {
                                continue;
                            }

                            $categoria = (string)($section['descricao'] ?? '');

                            if (!empty($section['itens']) && is_array($section['itens'])) {
                                foreach ($section['itens'] as $it) {
                                    if (!is_array($it)) {
                                        continue;
                                    }
                                    $pushItem($grupo, $categoria, $it);
                                }
                            }

                            if (!empty($section['subgrupos']) && is_array($section['subgrupos'])) {
                                foreach ($section['subgrupos'] as $sub) {
                                    if (!is_array($sub)) {
                                        continue;
                                    }
                                    $subCategoria = (string)($sub['descricao'] ?? '');

                                    if (!empty($sub['itens']) && is_array($sub['itens'])) {
                                        foreach ($sub['itens'] as $it) {
                                            if (!is_array($it)) {
                                                continue;
                                            }
                                            $pushItem($categoria !== '' ? $categoria : $grupo, $subCategoria !== '' ? $subCategoria : $categoria, $it);
                                        }
                                    }
                                }
                            }
                        }
                    };

                    if (!empty($decoded['servicos_preliminares']) && is_array($decoded['servicos_preliminares'])) {
                        $walkSections('SERVIÇOS PRELIMINARES', $decoded['servicos_preliminares']);
                    }

                    if (!empty($decoded['obra']) && is_array($decoded['obra'])) {
                        $walkSections('OBRA', $decoded['obra']);
                    }

                    if (!empty($rows)) {
                        return $rows;
                    }
                }
            }
        }

        return [
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'PROJETOS COMPLEMENTARES',
                'codigo' => '1.1',
                'descricao' => "PROJETO DE INSTALAÇÕES ELÉTRICAS\n- Entrada de energia\n- Distribuição elétrica de baixa tensão\n- Infraestrutura seca para sistemas complementares",
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '33.760,00',
                'ordem' => 11,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'PROJETOS COMPLEMENTARES',
                'codigo' => '1.2',
                'descricao' => "PROJETO DE INSTALAÇÕES HIDRÁULICAS E GÁS\n- Dimensionamento do sistema de esgoto e ventilação (comum);\n- Distribuição de água fria / quente;\n- Dimensionamento do sistema de aquecimento da casa/piscina/hidro;\n- Dimensionamento de sistemas para hidromassagem;\n- Dreno para sistema de ar condicionado;\n- Isométricos e detalhes de instalação;\n- Dimensionamento de tubulação de gás GN ou GLP",
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '29.840,00',
                'ordem' => 12,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'PROJETOS COMPLEMENTARES',
                'codigo' => '1.3',
                'descricao' => 'PROJETO ESTRUTURAL',
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '55.000,00',
                'ordem' => 13,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'PROJETOS COMPLEMENTARES',
                'codigo' => '1.4',
                'descricao' => 'PROJETO DE FUNDAÇÃO E CONTENÇÃO + Consultoria',
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '21.000,00',
                'ordem' => 14,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'PROJETOS COMPLEMENTARES',
                'codigo' => '1.5',
                'descricao' => "PROJETO DE AR CONDICIONADO E EXAUSTÃO\n- Contratado pela arquitetura/cliente\nEXCLUSO",
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '0',
                'ordem' => 15,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'PROJETOS COMPLEMENTARES',
                'codigo' => '1.6',
                'descricao' => 'PROJETO DE AUTOMAÇÃO - Contratado pela arquitetura/cliente\nEXCLUSO',
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '0',
                'ordem' => 16,
            ],

            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.1',
                'descricao' => 'Instalações provisórias',
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '3.000,00',
                'ordem' => 21,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.2',
                'descricao' => "Locação de containers para canteiro de obras:\n01 Escritório + 01 Almoxarifado + 01 WC + 01 Vestiário",
                'quantidade' => '7,0',
                'unidade' => 'mês',
                'valor_unitario' => '2.700,00',
                'ordem' => 22,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.3',
                'descricao' => 'Fretes ida e volta de containers',
                'quantidade' => '2,0',
                'unidade' => 'unid',
                'valor_unitario' => '4.000,00',
                'ordem' => 23,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.4',
                'descricao' => 'Isolamento de tapume com telha trapezoidal metálica h: 2,3m - 31 metros lineares - Considerando apenas na frente do terreno',
                'quantidade' => '71,3',
                'unidade' => 'm²',
                'valor_unitario' => '200,00',
                'ordem' => 24,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.5',
                'descricao' => 'Caçamba estacionária para remoção de entulho',
                'quantidade' => '56,0',
                'unidade' => 'und',
                'valor_unitario' => '430,00',
                'ordem' => 25,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.6',
                'descricao' => 'Locação de equipamentos gerais de obra',
                'quantidade' => '7,0',
                'unidade' => 'mês',
                'valor_unitario' => '900,00',
                'ordem' => 26,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.7',
                'descricao' => 'Locação de andaimes',
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '5.500,00',
                'ordem' => 27,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.8',
                'descricao' => 'Fretes e carretos - entrega de materiais',
                'quantidade' => '7,0',
                'unidade' => 'mês',
                'valor_unitario' => '1.300,00',
                'ordem' => 28,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.8b',
                'descricao' => 'Despesas de canteiro - Materiais de escritório, limpeza e TI',
                'quantidade' => '7,0',
                'unidade' => 'mês',
                'valor_unitario' => '600,00',
                'ordem' => 29,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.9',
                'descricao' => 'Consumo de água fria, esgoto e elétrica - à cargo do cliente\nEXCLUSO',
                'quantidade' => '7,0',
                'unidade' => 'mês',
                'valor_unitario' => '0',
                'ordem' => 30,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.10',
                'descricao' => 'Lava rodas / wap',
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '2.500,00',
                'ordem' => 31,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'CANTEIRO DE OBRAS',
                'codigo' => '2.11',
                'descricao' => "Consultoria de Segurança - Técnico de Seg. do Trabalho, visitas semanais",
                'quantidade' => '7,0',
                'unidade' => 'mês',
                'valor_unitario' => '4.500,00',
                'ordem' => 32,
            ],

            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'TAXAS E SEGUROS',
                'codigo' => '3.1',
                'descricao' => 'ART - Anotação de Responsabilidade Técnica',
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '300,00',
                'ordem' => 41,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'TAXAS E SEGUROS',
                'codigo' => '3.2',
                'descricao' => 'Seguro de Obra - Considerado para todo o período da obra',
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '16.350,75',
                'ordem' => 42,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'TAXAS E SEGUROS',
                'codigo' => '3.3',
                'descricao' => 'Cópias e plotagens de projetos',
                'quantidade' => '7,0',
                'unidade' => 'mês',
                'valor_unitario' => '700,00',
                'ordem' => 43,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'TAXAS E SEGUROS',
                'codigo' => '3.4',
                'descricao' => 'Abertura e fechamento de CNO - à cargo do cliente\nEXCLUSO',
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '0',
                'ordem' => 44,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'TAXAS E SEGUROS',
                'codigo' => '3.5',
                'descricao' => 'Acompanhamento fiscal - Análise tributária (ISS/INSS) - à cargo do cliente\nEXCLUSO',
                'quantidade' => '7,0',
                'unidade' => 'mês',
                'valor_unitario' => '0',
                'ordem' => 45,
            ],
            [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'TAXAS E SEGUROS',
                'codigo' => '3.6',
                'descricao' => 'Laudo de vistoria cautelar em vizinhança - 02 Vizinhos',
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '6.000,00',
                'ordem' => 46,
            ],
        ];
    }

    private static function formatPtBrNumber(float $value, int $decimals): string
    {
        return number_format($value, $decimals, ',', '.');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        Logger::info('orcamentos.edit', ['id' => $id]);
        $orcamento = Orcamento::find($id);
        if (!$orcamento) {
            Logger::warning('orcamentos.edit.not_found', ['id' => $id]);
            $this->redirect('/?route=orcamentos/index');
            return;
        }

        $this->render('orcamentos/edit', [
            'orcamento' => $orcamento,
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        Logger::info('orcamentos.update.start', ['id' => $id]);
        $existing = Orcamento::find($id);
        if (!$existing) {
            Logger::warning('orcamentos.update.not_found', ['id' => $id]);
            $this->redirect('/?route=orcamentos/index');
            return;
        }

        $data = Orcamento::normalize($_POST);

        $data['logo_path'] = (string)($existing['logo_path'] ?? '');

        $errors = Orcamento::validate($data);
        if ($errors) {
            Logger::warning('orcamentos.update.validation_failed', ['id' => $id, 'errors' => $errors]);
            $data['id'] = $id;
            $this->render('orcamentos/edit', [
                'orcamento' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        Orcamento::update($id, $data);
        Logger::info('orcamentos.update.updated', ['id' => $id]);
        $this->redirect('/?route=orcamentos/show&id=' . $id);
    }

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Orcamento::delete($id);
            Logger::info('orcamentos.delete.deleted', ['id' => $id]);
        }
        $this->redirect('/?route=orcamentos/index');
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        Logger::info('orcamentos.show', ['id' => $id]);
        $orcamento = Orcamento::find($id);
        if (!$orcamento) {
            Logger::warning('orcamentos.show.not_found', ['id' => $id]);
            $this->redirect('/?route=orcamentos/index');
            return;
        }

        $itens = OrcamentoItem::allByOrcamento($id);

        $this->render('orcamentos/show', [
            'orcamento' => $orcamento,
            'itens' => $itens,
            'grupos' => OrcamentoOpcao::namesByTipo('grupo'),
            'categorias' => OrcamentoOpcao::namesByTipo('categoria'),
            'unidades' => OrcamentoOpcao::namesByTipo('unidade'),
            'item' => [
                'grupo' => 'SERVIÇOS PRELIMINARES',
                'categoria' => 'PROJETOS COMPLEMENTARES',
                'codigo' => '1.1',
                'descricao' => 'PROJETO DE INSTALAÇÕES ELÉTRICAS\n- Entrada de energia\n- Distribuição elétrica de baixa tensão',
                'quantidade' => '1,0',
                'unidade' => 'vb',
                'valor_unitario' => '0',
                'ordem' => '0',
            ],
            'errors' => [],
        ]);
    }

    public function itemStore(): void
    {
        $orcamentoId = (int)($_POST['orcamento_id'] ?? 0);
        Logger::info('orcamentos.itemStore.start', ['orcamento_id' => $orcamentoId]);
        $orcamento = Orcamento::find($orcamentoId);
        if (!$orcamento) {
            Logger::warning('orcamentos.itemStore.orcamento_not_found', ['orcamento_id' => $orcamentoId]);
            $this->redirect('/?route=orcamentos/index');
            return;
        }

        $data = OrcamentoItem::normalize($_POST);
        $errors = OrcamentoItem::validate($data);

        if ($errors) {
            Logger::warning('orcamentos.itemStore.validation_failed', ['orcamento_id' => $orcamentoId, 'errors' => $errors]);
            $itens = OrcamentoItem::allByOrcamento($orcamentoId);
            $this->render('orcamentos/show', [
                'orcamento' => $orcamento,
                'itens' => $itens,
                'grupos' => OrcamentoOpcao::namesByTipo('grupo'),
                'categorias' => OrcamentoOpcao::namesByTipo('categoria'),
                'unidades' => OrcamentoOpcao::namesByTipo('unidade'),
                'item' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $itemId = OrcamentoItem::create($orcamentoId, $data);
        Logger::info('orcamentos.itemStore.created', ['orcamento_id' => $orcamentoId, 'item_id' => $itemId, 'codigo' => $data['codigo'] ?? null]);
        $this->redirect('/?route=orcamentos/show&id=' . $orcamentoId);
    }

    public function itemEdit(): void
    {
        $orcamentoId = (int)($_GET['orcamento_id'] ?? 0);
        $id = (int)($_GET['id'] ?? 0);
        Logger::info('orcamentos.itemEdit', ['orcamento_id' => $orcamentoId, 'item_id' => $id]);

        $orcamento = Orcamento::find($orcamentoId);
        if (!$orcamento) {
            Logger::warning('orcamentos.itemEdit.orcamento_not_found', ['orcamento_id' => $orcamentoId]);
            $this->redirect('/?route=orcamentos/index');
            return;
        }

        $item = OrcamentoItem::find($id);
        if (!$item || (int)($item['orcamento_id'] ?? 0) !== $orcamentoId) {
            Logger::warning('orcamentos.itemEdit.item_not_found', ['orcamento_id' => $orcamentoId, 'item_id' => $id]);
            $this->redirect('/?route=orcamentos/show&id=' . $orcamentoId);
            return;
        }

        $this->render('orcamentos/item_edit', [
            'orcamento' => $orcamento,
            'item' => $item,
            'grupos' => OrcamentoOpcao::namesByTipo('grupo'),
            'categorias' => OrcamentoOpcao::namesByTipo('categoria'),
            'unidades' => OrcamentoOpcao::namesByTipo('unidade'),
            'errors' => [],
        ]);
    }

    public function itemUpdate(): void
    {
        $orcamentoId = (int)($_POST['orcamento_id'] ?? 0);
        $id = (int)($_POST['id'] ?? 0);
        Logger::info('orcamentos.itemUpdate.start', ['orcamento_id' => $orcamentoId, 'item_id' => $id]);

        $orcamento = Orcamento::find($orcamentoId);
        if (!$orcamento) {
            Logger::warning('orcamentos.itemUpdate.orcamento_not_found', ['orcamento_id' => $orcamentoId]);
            $this->redirect('/?route=orcamentos/index');
            return;
        }

        $existing = OrcamentoItem::find($id);
        if (!$existing || (int)($existing['orcamento_id'] ?? 0) !== $orcamentoId) {
            Logger::warning('orcamentos.itemUpdate.item_not_found', ['orcamento_id' => $orcamentoId, 'item_id' => $id]);
            $this->redirect('/?route=orcamentos/show&id=' . $orcamentoId);
            return;
        }

        $data = OrcamentoItem::normalize($_POST);
        $errors = OrcamentoItem::validate($data);
        if ($errors) {
            Logger::warning('orcamentos.itemUpdate.validation_failed', ['orcamento_id' => $orcamentoId, 'item_id' => $id, 'errors' => $errors]);
            $data['id'] = $id;
            $data['orcamento_id'] = $orcamentoId;
            $this->render('orcamentos/item_edit', [
                'orcamento' => $orcamento,
                'item' => $data,
                'grupos' => OrcamentoOpcao::namesByTipo('grupo'),
                'categorias' => OrcamentoOpcao::namesByTipo('categoria'),
                'unidades' => OrcamentoOpcao::namesByTipo('unidade'),
                'errors' => $errors,
            ]);
            return;
        }

        OrcamentoItem::update($id, $data);
        Logger::info('orcamentos.itemUpdate.updated', ['orcamento_id' => $orcamentoId, 'item_id' => $id]);
        $this->redirect('/?route=orcamentos/show&id=' . $orcamentoId);
    }

    public function grupos(): void
    {
        Logger::info('orcamentos.grupos');
        $this->render('orcamentos/opcoes', [
            'tipo' => 'grupo',
            'titulo' => 'Grupos',
            'items' => OrcamentoOpcao::allByTipo('grupo'),
            'errors' => [],
        ]);
    }

    public function gruposStore(): void
    {
        $nome = trim((string)($_POST['nome'] ?? ''));
        $errors = OrcamentoOpcao::validate($nome);
        if ($errors) {
            $this->render('orcamentos/opcoes', [
                'tipo' => 'grupo',
                'titulo' => 'Grupos',
                'items' => OrcamentoOpcao::allByTipo('grupo'),
                'errors' => $errors,
            ]);
            return;
        }
        OrcamentoOpcao::create('grupo', $nome);
        $this->redirect('/?route=orcamentos/grupos');
    }

    public function gruposDelete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            OrcamentoOpcao::delete($id, 'grupo');
        }
        $this->redirect('/?route=orcamentos/grupos');
    }

    public function categorias(): void
    {
        Logger::info('orcamentos.categorias');
        $this->render('orcamentos/opcoes', [
            'tipo' => 'categoria',
            'titulo' => 'Categorias',
            'items' => OrcamentoOpcao::allByTipo('categoria'),
            'errors' => [],
        ]);
    }

    public function categoriasStore(): void
    {
        $nome = trim((string)($_POST['nome'] ?? ''));
        $errors = OrcamentoOpcao::validate($nome);
        if ($errors) {
            $this->render('orcamentos/opcoes', [
                'tipo' => 'categoria',
                'titulo' => 'Categorias',
                'items' => OrcamentoOpcao::allByTipo('categoria'),
                'errors' => $errors,
            ]);
            return;
        }
        OrcamentoOpcao::create('categoria', $nome);
        $this->redirect('/?route=orcamentos/categorias');
    }

    public function categoriasDelete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            OrcamentoOpcao::delete($id, 'categoria');
        }
        $this->redirect('/?route=orcamentos/categorias');
    }

    public function unidades(): void
    {
        Logger::info('orcamentos.unidades');
        $this->render('orcamentos/opcoes', [
            'tipo' => 'unidade',
            'titulo' => 'Unidades',
            'items' => OrcamentoOpcao::allByTipo('unidade'),
            'errors' => [],
        ]);
    }

    public function unidadesStore(): void
    {
        $nome = trim((string)($_POST['nome'] ?? ''));
        $errors = OrcamentoOpcao::validate($nome);
        if ($errors) {
            $this->render('orcamentos/opcoes', [
                'tipo' => 'unidade',
                'titulo' => 'Unidades',
                'items' => OrcamentoOpcao::allByTipo('unidade'),
                'errors' => $errors,
            ]);
            return;
        }
        OrcamentoOpcao::create('unidade', $nome);
        $this->redirect('/?route=orcamentos/unidades');
    }

    public function unidadesDelete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            OrcamentoOpcao::delete($id, 'unidade');
        }
        $this->redirect('/?route=orcamentos/unidades');
    }

    public function itemDelete(): void
    {
        $orcamentoId = (int)($_POST['orcamento_id'] ?? 0);
        $itemId = (int)($_POST['id'] ?? 0);
        if ($itemId > 0) {
            OrcamentoItem::delete($itemId);
            Logger::info('orcamentos.itemDelete.deleted', ['orcamento_id' => $orcamentoId, 'item_id' => $itemId]);
        }
        $this->redirect('/?route=orcamentos/show&id=' . $orcamentoId);
    }

    public function print(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        Logger::info('orcamentos.print', ['id' => $id]);
        $orcamento = Orcamento::find($id);
        if (!$orcamento) {
            Logger::warning('orcamentos.print.not_found', ['id' => $id]);
            $this->redirect('/?route=orcamentos/index');
            return;
        }

        $itens = OrcamentoItem::allByOrcamento($id);

        $this->render('orcamentos/print', [
            'orcamento' => $orcamento,
            'itens' => $itens,
            'isPrint' => true,
        ]);
    }

    public function pdf(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        Logger::info('orcamentos.pdf', ['id' => $id]);
        $orcamento = Orcamento::find($id);
        if (!$orcamento) {
            Logger::warning('orcamentos.pdf.not_found', ['id' => $id]);
            $this->redirect('/?route=orcamentos/index');
            return;
        }

        $itens = OrcamentoItem::allByOrcamento($id);

        if (class_exists('Dompdf\\Dompdf')) {
            $html = $this->renderToString('orcamentos/print', [
                'orcamento' => $orcamento,
                'itens' => $itens,
                'isPrint' => true,
            ]);

            $klass = 'Dompdf\\Dompdf';
            $dompdf = new $klass([
                'isRemoteEnabled' => true,
            ]);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="orcamento-' . $id . '.pdf"');
            echo $dompdf->output();
            return;
        }

        Logger::warning('orcamentos.pdf.dompdf_missing', ['id' => $id]);
        $this->redirect('/?route=orcamentos/print&id=' . $id);
    }

    private function handleLogoUpload(string $field): ?string
    {
        if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
            return null;
        }

        $file = $_FILES[$field];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        $tmp = (string)($file['tmp_name'] ?? '');
        $name = (string)($file['name'] ?? '');

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            Logger::warning('orcamentos.logo_upload.invalid_extension', ['name' => $name, 'ext' => $ext]);
            return null;
        }

        $targetDir = __DIR__ . '/../../public/uploads/logos';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $base = bin2hex(random_bytes(8));
        $target = $targetDir . '/' . $base . '.' . $ext;

        if (!move_uploaded_file($tmp, $target)) {
            Logger::error('orcamentos.logo_upload.move_failed', ['tmp' => $tmp, 'target' => $target]);
            return null;
        }

        $webPath = '/uploads/logos/' . $base . '.' . $ext;
        Logger::info('orcamentos.logo_upload.success', ['path' => $webPath]);
        return $webPath;
    }

    private function render(string $view, array $params = []): void
    {
        extract($params, EXTR_SKIP);
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layout.php';
    }

    private function renderToString(string $view, array $params = []): string
    {
        extract($params, EXTR_SKIP);
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        ob_start();
        require __DIR__ . '/../Views/layout_print.php';
        return (string)ob_get_clean();
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
