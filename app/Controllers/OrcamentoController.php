<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Models\Orcamento;
use App\Models\OrcamentoAdequacao;
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
        
        // Inicializar logo_path e capa_path vazios
        $data['logo_path'] = '';
        $data['capa_path_1'] = '';
        $data['capa_path_2'] = '';
        $data['capa_path_3'] = '';
        $data['capa_path_4'] = '';
        
        // Processar upload de logo
        if (!empty($_FILES['logo']['tmp_name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = bin2hex(random_bytes(8)) . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                $data['logo_path'] = '/public/uploads/logos/' . $filename;
                Logger::info('orcamentos.store.logo_uploaded', ['path' => $data['logo_path']]);
            }
        }
        
        // Processar upload de capas (até 4)
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($_FILES['capa_' . $i]['tmp_name'])) {
                $uploadDir = __DIR__ . '/../../public/uploads/capas/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($_FILES['capa_' . $i]['name'], PATHINFO_EXTENSION);
                $filename = bin2hex(random_bytes(8)) . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['capa_' . $i]['tmp_name'], $targetPath)) {
                    $data['capa_path_' . $i] = '/public/uploads/capas/' . $filename;
                    Logger::info('orcamentos.store.capa_uploaded', ['index' => $i, 'path' => $data['capa_path_' . $i]]);
                }
            }
        }
        
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

        try {
            $data = Orcamento::normalize($_POST);
        } catch (\Exception $e) {
            Logger::error('orcamentos.update.normalize_failed', ['id' => $id, 'error' => $e->getMessage()]);
            $this->render('orcamentos/edit', [
                'orcamento' => $existing,
                'errors' => ['geral' => 'Erro ao processar dados: ' . $e->getMessage()],
            ]);
            return;
        }

        $data['logo_path'] = (string)($existing['logo_path'] ?? '');
        $data['capa_path_1'] = (string)($existing['capa_path_1'] ?? '');
        $data['capa_path_2'] = (string)($existing['capa_path_2'] ?? '');
        $data['capa_path_3'] = (string)($existing['capa_path_3'] ?? '');
        $data['capa_path_4'] = (string)($existing['capa_path_4'] ?? '');
        
        // Processar upload de logo
        if (!empty($_FILES['logo']['tmp_name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = bin2hex(random_bytes(8)) . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                // Remover logo antiga se existir
                if (!empty($existing['logo_path'])) {
                    $oldPath = __DIR__ . '/../../' . ltrim($existing['logo_path'], '/');
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $data['logo_path'] = '/public/uploads/logos/' . $filename;
                Logger::info('orcamentos.update.logo_uploaded', ['id' => $id, 'path' => $data['logo_path'], 'file_exists' => file_exists($targetPath)]);
            } else {
                Logger::error('orcamentos.update.logo_upload_failed', ['id' => $id, 'tmp_name' => $_FILES['logo']['tmp_name'], 'target' => $targetPath]);
            }
        }
        
        // Processar upload de capas (até 4)
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($_FILES['capa_' . $i]['tmp_name'])) {
                $uploadDir = __DIR__ . '/../../public/uploads/capas/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($_FILES['capa_' . $i]['name'], PATHINFO_EXTENSION);
                $filename = bin2hex(random_bytes(8)) . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['capa_' . $i]['tmp_name'], $targetPath)) {
                    // Remover capa antiga se existir
                    if (!empty($existing['capa_path_' . $i])) {
                        $oldPath = __DIR__ . '/../../' . ltrim($existing['capa_path_' . $i], '/');
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                    $data['capa_path_' . $i] = '/public/uploads/capas/' . $filename;
                    Logger::info('orcamentos.update.capa_uploaded', ['id' => $id, 'index' => $i, 'path' => $data['capa_path_' . $i]]);
                }
            }
        }

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

        try {
            Orcamento::update($id, $data);
            Logger::info('orcamentos.update.updated', ['id' => $id]);
            $this->redirect('/?route=orcamentos/show&id=' . $id);
        } catch (\Exception $e) {
            Logger::error('orcamentos.update.failed', ['id' => $id, 'error' => $e->getMessage()]);
            $data['id'] = $id;
            $this->render('orcamentos/edit', [
                'orcamento' => $data,
                'errors' => ['geral' => 'Erro ao salvar: ' . $e->getMessage()],
            ]);
        }
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
        // Iniciar sessão se não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $id = (int)($_GET['id'] ?? 0);
        Logger::info('orcamentos.show', ['id' => $id]);

        try {
            $orcamento = Orcamento::find($id);
            if (!$orcamento) {
                Logger::warning('orcamentos.show.not_found', ['id' => $id]);
                $this->redirect('/?route=orcamentos/index');
                return;
            }

            // Redirecionar para view correta baseado no tipo
            $tipoOrcamento = (string)($orcamento['tipo_orcamento'] ?? 'manual');
            if ($tipoOrcamento === 'sinapi') {
                $this->redirect('/?route=orcamentos/showSinapi&id=' . $id);
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
        } catch (\Throwable $e) {
            Logger::error('orcamentos.show.error', [
                'id' => $id,
                'message' => $e->getMessage(),
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            error_log('[orcamentos.show.error] id=' . $id . ' type=' . get_class($e) . ' message=' . $e->getMessage() . ' file=' . $e->getFile() . ':' . $e->getLine());
            http_response_code(500);
            
            // Exibir erro detalhado na tela
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Erro - Orçamento</title>';
            echo '<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#fff;font-size:13px;}';
            echo '.error-box{background:#2d1f1f;border:2px solid #ff4444;border-radius:8px;padding:20px;margin:20px 0;}';
            echo '.error-title{color:#ff4444;font-size:20px;font-weight:bold;margin-bottom:10px;}';
            echo '.error-message{color:#ffaa44;font-size:14px;margin:10px 0;padding:10px;background:#1a1a1a;border-radius:4px;word-break:break-all;}';
            echo '.error-trace{color:#888;font-size:11px;margin-top:15px;padding:10px;background:#0a0a0a;border-radius:4px;overflow-x:auto;white-space:pre-wrap;}';
            echo '.btn{display:inline-block;padding:10px 20px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;margin-top:15px;}';
            echo '.debug-section{margin-top:20px;padding:15px;background:#1a3a1a;border-radius:8px;}';
            echo '.debug-title{color:#4CAF50;font-weight:bold;margin-bottom:10px;}';
            echo '</style></head><body>';
            echo '<div class="error-box">';
            echo '<div class="error-title">❌ Erro ao carregar orçamento ID: ' . $id . '</div>';
            echo '<div class="error-message"><strong>Tipo:</strong> ' . htmlspecialchars(get_class($e)) . '</div>';
            echo '<div class="error-message"><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<div class="error-message"><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . '</div>';
            echo '<div class="error-message"><strong>Linha:</strong> ' . $e->getLine() . '</div>';
            
            // Debug adicional
            echo '<div class="debug-section">';
            echo '<div class="debug-title">🔍 Informações de Debug:</div>';
            echo '<div class="error-message"><strong>PHP Version:</strong> ' . PHP_VERSION . '</div>';
            echo '<div class="error-message"><strong>OPcache Enabled:</strong> ' . (function_exists('opcache_get_status') && opcache_get_status() ? 'Sim' : 'Não') . '</div>';
            echo '<div class="error-message"><strong>Memory Limit:</strong> ' . ini_get('memory_limit') . '</div>';
            echo '<div class="error-message"><strong>Max Execution Time:</strong> ' . ini_get('max_execution_time') . 's</div>';
            
            // Verificar se o orçamento existe
            try {
                $testOrcamento = \App\Models\Orcamento::find($id);
                if ($testOrcamento) {
                    echo '<div class="error-message"><strong>Orçamento encontrado:</strong> Sim (ID: ' . $id . ')</div>';
                    echo '<div class="error-message"><strong>Tipo:</strong> ' . htmlspecialchars($testOrcamento['tipo_orcamento'] ?? 'N/A') . '</div>';
                } else {
                    echo '<div class="error-message"><strong>Orçamento encontrado:</strong> Não</div>';
                }
            } catch (\Throwable $e2) {
                echo '<div class="error-message"><strong>Erro ao buscar orçamento:</strong> ' . htmlspecialchars($e2->getMessage()) . '</div>';
            }
            echo '</div>';
            
            echo '<div class="error-trace"><strong>Stack Trace:</strong><br>' . htmlspecialchars($e->getTraceAsString()) . '</div>';
            echo '<a href="/?route=orcamentos/index" class="btn">← Voltar para lista de orçamentos</a>';
            echo '<a href="/limpar_cache.php" class="btn" style="background:#ff9800;">🧹 Limpar Cache</a>';
            echo '</div></body></html>';
        }
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
        
        Logger::info('orcamentos.itemStore.data_normalized', [
            'orcamento_id' => $orcamentoId,
            'valor_unitario' => $data['valor_unitario'] ?? 'NAO_EXISTE',
            'margem_personalizada' => $data['margem_personalizada'] ?? 'NAO_EXISTE',
            'usa_margem_personalizada' => $data['usa_margem_personalizada'] ?? 'NAO_EXISTE',
            'categoria' => $data['categoria'] ?? 'NAO_EXISTE'
        ]);
        
        // Determinar qual margem usar baseado na classificacao_custo
        $usaMargemPersonalizada = (int)($data['usa_margem_personalizada'] ?? 0);
        $classificacaoCusto = (string)($data['classificacao_custo'] ?? '');
        
        if ($usaMargemPersonalizada) {
            // Usar margem personalizada do item
            $margem = (float)($data['margem_personalizada'] ?? 0);
            Logger::info('orcamentos.itemStore.margem', [
                'tipo' => 'personalizada',
                'margem' => $margem,
                'classificacao_custo' => $classificacaoCusto
            ]);
        } else {
            // Usar margem global do orçamento baseada na classificacao_custo
            Logger::info('orcamentos.itemStore.orcamento_margens', [
                'margem_mao_obra' => $orcamento['margem_mao_obra'] ?? 'NAO_EXISTE',
                'margem_materiais' => $orcamento['margem_materiais'] ?? 'NAO_EXISTE',
                'margem_equipamentos' => $orcamento['margem_equipamentos'] ?? 'NAO_EXISTE'
            ]);
            
            if ($classificacaoCusto === 'mao_obra') {
                $margem = (float)($orcamento['margem_mao_obra'] ?? 50);
                Logger::info('orcamentos.itemStore.margem', [
                    'tipo' => 'global_mao_obra',
                    'margem' => $margem,
                    'classificacao_custo' => $classificacaoCusto
                ]);
            } elseif ($classificacaoCusto === 'equipamento') {
                $margem = (float)($orcamento['margem_equipamentos'] ?? 20);
                Logger::info('orcamentos.itemStore.margem', [
                    'tipo' => 'global_equipamentos',
                    'margem' => $margem,
                    'classificacao_custo' => $classificacaoCusto
                ]);
            } elseif ($classificacaoCusto === 'material') {
                $margem = (float)($orcamento['margem_materiais'] ?? 20);
                Logger::info('orcamentos.itemStore.margem', [
                    'tipo' => 'global_materiais',
                    'margem' => $margem,
                    'classificacao_custo' => $classificacaoCusto
                ]);
            } else {
                // Se classificacao_custo não foi definido, usar 0 (sem margem)
                $margem = 0;
                Logger::warning('orcamentos.itemStore.classificacao_custo_nao_definido', [
                    'classificacao_custo' => $classificacaoCusto
                ]);
            }
        }
        
        // Calcular valor_cobranca = valor_unitario × (1 + margem/100)
        $valorUnitario = (float)($data['valor_unitario'] ?? 0);
        if ($margem > 0) {
            $valorCobrancaCalculado = round($valorUnitario * (1 + $margem / 100), 2);
            $data['valor_cobranca'] = (string)$valorCobrancaCalculado;
            Logger::info('orcamentos.itemStore.calculo', [
                'valor_unitario' => $valorUnitario,
                'margem' => $margem,
                'valor_cobranca' => $valorCobrancaCalculado,
                'formula' => "$valorUnitario × (1 + $margem/100) = $valorCobrancaCalculado"
            ]);
        } else {
            $data['valor_cobranca'] = (string)$valorUnitario;
            Logger::warning('orcamentos.itemStore.sem_margem', [
                'valor_unitario' => $valorUnitario,
                'margem' => $margem,
                'categoria' => $categoria,
                'usa_margem_personalizada' => $usaMargemPersonalizada
            ]);
        }
        
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
        // Iniciar sessão se não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
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

        if (!array_key_exists('etapa', $_POST)) {
            $data['etapa'] = (string)($existing['etapa'] ?? '');
        }
        if (!array_key_exists('custo_material', $_POST)) {
            $data['custo_material'] = (float)($existing['custo_material'] ?? 0);
        }
        if (!array_key_exists('custo_mao_obra', $_POST)) {
            $data['custo_mao_obra'] = (float)($existing['custo_mao_obra'] ?? 0);
        }
        if (!array_key_exists('margem_lucro', $_POST)) {
            $data['margem_lucro'] = (float)($existing['margem_lucro'] ?? 0);
        }
        if (!array_key_exists('desconto_item', $_POST)) {
            $data['desconto_item'] = (float)($existing['desconto_item'] ?? 0);
        }

        if (!array_key_exists('percentual_realizado', $_POST)) {
            $data['percentual_realizado'] = (float)($existing['percentual_realizado'] ?? 0);
        }
        
        // Determinar qual margem usar baseado na classificacao_custo
        $usaMargemPersonalizada = (int)($data['usa_margem_personalizada'] ?? 0);
        $classificacaoCusto = (string)($data['classificacao_custo'] ?? '');
        
        if ($usaMargemPersonalizada) {
            // Usar margem personalizada do item
            $margem = (float)($data['margem_personalizada'] ?? 0);
        } else {
            // Usar margem global do orçamento baseada na classificacao_custo
            if ($classificacaoCusto === 'mao_obra') {
                $margem = (float)($orcamento['margem_mao_obra'] ?? 50);
            } elseif ($classificacaoCusto === 'equipamento') {
                $margem = (float)($orcamento['margem_equipamentos'] ?? 20);
            } elseif ($classificacaoCusto === 'material') {
                $margem = (float)($orcamento['margem_materiais'] ?? 20);
            } else {
                // Se classificacao_custo não foi definido, usar 0 (sem margem)
                $margem = 0;
            }
        }
        
        // Calcular valor_cobranca = valor_unitario × (1 + margem/100)
        $valorUnitario = (float)($data['valor_unitario'] ?? 0);
        if ($margem > 0) {
            $valorCobrancaCalculado = round($valorUnitario * (1 + $margem / 100), 2);
            $data['valor_cobranca'] = (string)$valorCobrancaCalculado;
        } else {
            $data['valor_cobranca'] = (string)$valorUnitario;
        }

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

        $after = OrcamentoItem::find($id) ?: [];
        Logger::info('orcamentos.itemUpdate.updated', [
            'orcamento_id' => $orcamentoId,
            'item_id' => $id,
            'post_quantidade' => (string)($_POST['quantidade'] ?? ''),
            'post_valor_unitario' => (string)($_POST['valor_unitario'] ?? ''),
            'data_quantidade' => (float)($data['quantidade'] ?? 0),
            'data_valor_unitario' => (float)($data['valor_unitario'] ?? 0),
            'after_quantidade' => (float)($after['quantidade'] ?? 0),
            'after_valor_unitario' => (float)($after['valor_unitario'] ?? 0),
        ]);

        $debug = ((string)($_GET['debug'] ?? '') === '1')
            || ((string)($_POST['debug'] ?? '') === '1')
            || ((string)getenv('APP_DEBUG') === '1');
        if ($debug) {
            header('Content-Type: text/plain; charset=utf-8');
            echo "itemUpdate debug\n";
            echo "POST quantidade=" . (string)($_POST['quantidade'] ?? '') . "\n";
            echo "POST valor_unitario=" . (string)($_POST['valor_unitario'] ?? '') . "\n";
            echo "NORMALIZED quantidade=" . (string)((float)($data['quantidade'] ?? 0)) . "\n";
            echo "NORMALIZED valor_unitario=" . (string)((float)($data['valor_unitario'] ?? 0)) . "\n";
            echo "DB after quantidade=" . (string)((float)($after['quantidade'] ?? 0)) . "\n";
            echo "DB after valor_unitario=" . (string)((float)($after['valor_unitario'] ?? 0)) . "\n";
            return;
        }

        // Salvar item ID para scroll após redirect
        $_SESSION['scroll_to_item'] = $id;
        
        $this->redirect('/?route=orcamentos/show&id=' . $orcamentoId);
    }

    public function recalcularMargens(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $orcamentoId = (int)($data['orcamento_id'] ?? 0);
        
        if ($orcamentoId <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID do orçamento inválido']);
            return;
        }
        
        // Buscar orçamento
        $orcamento = Orcamento::find($orcamentoId);
        if (!$orcamento) {
            echo json_encode(['success' => false, 'error' => 'Orçamento não encontrado']);
            return;
        }
        
        // Buscar margens globais
        $margemMaoObra = (float)($orcamento['margem_mao_obra'] ?? 50);
        $margemMateriais = (float)($orcamento['margem_materiais'] ?? 20);
        $margemEquipamentos = (float)($orcamento['margem_equipamentos'] ?? 20);
        
        // Buscar todos os itens
        $itens = OrcamentoItem::allByOrcamento($orcamentoId);
        
        $count = 0;
        foreach ($itens as $item) {
            $usaMargemPersonalizada = (int)($item['usa_margem_personalizada'] ?? 0);
            
            // Só recalcular itens que NÃO usam margem personalizada
            if ($usaMargemPersonalizada) {
                continue;
            }
            
            $valorUnitario = (float)($item['valor_unitario'] ?? 0);
            if ($valorUnitario <= 0) {
                continue;
            }
            
            // Detectar margem baseada na categoria
            $categoria = (string)($item['categoria'] ?? '');
            $categoriaUpper = strtoupper($categoria);
            
            if (stripos($categoriaUpper, 'MÃO DE OBRA') !== false || stripos($categoriaUpper, 'MAO DE OBRA') !== false) {
                $margem = $margemMaoObra;
            } elseif (stripos($categoriaUpper, 'EQUIPAMENTO') !== false) {
                $margem = $margemEquipamentos;
            } else {
                $margem = $margemMateriais;
            }
            
            // Calcular novo valor_cobranca
            $valorCobranca = round($valorUnitario * (1 + $margem / 100), 2);
            
            // Atualizar no banco
            $pdo = \App\Core\Database::pdo();
            $stmt = $pdo->prepare('UPDATE orcamento_itens SET valor_cobranca = :valor_cobranca WHERE id = :id');
            $stmt->execute([
                ':valor_cobranca' => $valorCobranca,
                ':id' => (int)$item['id']
            ]);
            
            $count++;
        }
        
        Logger::info('orcamentos.recalcularMargens', [
            'orcamento_id' => $orcamentoId,
            'count' => $count,
            'margem_mao_obra' => $margemMaoObra,
            'margem_materiais' => $margemMateriais,
            'margem_equipamentos' => $margemEquipamentos
        ]);
        
        echo json_encode(['success' => true, 'count' => $count]);
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

        if (class_exists('Dompdf\\Dompdf')) {
            try {
                $html = \App\Helpers\OrcamentoPDF::gerarHTML($id, $orcamento);

                $klass = 'Dompdf\\Dompdf';
                $dompdf = new $klass([
                    'isRemoteEnabled' => true,
                ]);
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                header('Content-Type: application/pdf');
                $nomeArquivo = $orcamento['numero_proposta'] ?? 'orcamento-' . $id;
                $nomeArquivo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nomeArquivo);
                $dataExportacao = date('Y-m-d');
                header('Content-Disposition: inline; filename="' . $nomeArquivo . '_' . $dataExportacao . '.pdf"');
                echo $dompdf->output();
                return;
            } catch (\Throwable $e) {
                Logger::error('orcamentos.pdf.error', [
                    'id' => $id,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                header('Content-Type: text/plain; charset=utf-8');
                echo "ERRO AO GERAR PDF:\n\n";
                echo "Mensagem: " . $e->getMessage() . "\n";
                echo "Arquivo: " . $e->getFile() . "\n";
                echo "Linha: " . $e->getLine() . "\n\n";
                echo $e->getTraceAsString();
                return;
            }
        }

        Logger::warning('orcamentos.pdf.dompdf_missing', ['id' => $id]);
        $this->redirect('/?route=orcamentos/print&id=' . $id);
    }

    public function pdfAdmin(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        Logger::info('orcamentos.pdfAdmin', ['id' => $id]);
        $orcamento = Orcamento::find($id);
        if (!$orcamento) {
            Logger::warning('orcamentos.pdfAdmin.not_found', ['id' => $id]);
            $this->redirect('/?route=orcamentos/index');
            return;
        }

        if (class_exists('Dompdf\\Dompdf')) {
            try {
                $html = \App\Helpers\OrcamentoPDF::gerarHTMLAdmin($id, $orcamento);

                $klass = 'Dompdf\\Dompdf';
                $dompdf = new $klass([
                    'isRemoteEnabled' => true,
                ]);
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                header('Content-Type: application/pdf');
                $nomeArquivo = $orcamento['numero_proposta'] ?? 'orcamento-' . $id;
                $nomeArquivo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nomeArquivo);
                $dataExportacao = date('Y-m-d');
                header('Content-Disposition: inline; filename="' . $nomeArquivo . '_ADMIN_' . $dataExportacao . '.pdf"');
                echo $dompdf->output();
                return;
            } catch (\Throwable $e) {
                Logger::error('orcamentos.pdfAdmin.error', [
                    'id' => $id,
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                header('Content-Type: text/plain; charset=utf-8');
                echo "ERRO AO GERAR PDF ADMINISTRATIVO:\n\n";
                echo "Mensagem: " . $e->getMessage() . "\n";
                echo "Arquivo: " . $e->getFile() . "\n";
                echo "Linha: " . $e->getLine() . "\n\n";
                echo $e->getTraceAsString();
                return;
            }
        }

        Logger::warning('orcamentos.pdfAdmin.dompdf_missing', ['id' => $id]);
        $this->redirect('/?route=orcamentos/print&id=' . $id);
    }

    public function adequacao(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        Logger::info('orcamentos.adequacao', ['id' => $id]);
        $orcamento = Orcamento::find($id);
        if (!$orcamento) {
            Logger::warning('orcamentos.adequacao.not_found', ['id' => $id]);
            $this->redirect('/?route=orcamentos/index');
            return;
        }

        $totais = OrcamentoItem::getTotaisGerais($id);

        $this->render('orcamentos/adequacao', [
            'orcamento' => $orcamento,
            'totais' => $totais,
            'historico' => [],
            'errors' => [],
        ]);
    }

    public function adequacaoPreview(): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido']);
            exit;
        }

        $valorDesejado = (float)($input['valor_desejado'] ?? 0);
        $orcamentoId = (int)($input['orcamento_id'] ?? 0);
        
        if ($valorDesejado <= 0 || $orcamentoId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros inválidos']);
            exit;
        }
        
        try {
            $preview = OrcamentoAdequacao::calcularPreview($orcamentoId, $valorDesejado);
            if (is_array($preview) && isset($preview['erro'])) {
                http_response_code(400);
                echo json_encode(['error' => (string)$preview['erro']]);
                exit;
            }

            echo json_encode($preview);
        } catch (\Throwable $e) {
            Logger::error('orcamentos.adequacao.preview.error', [
                'id' => $orcamentoId,
                'message' => $e->getMessage(),
                'type' => get_class($e),
            ]);
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao calcular preview: ' . $e->getMessage()]);
        }
        exit;
    }

    public function adequacaoAplicar(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'erro' => 'JSON inválido',
            ]);
            exit;
        }

        $valorDesejado = (float)($input['valor_desejado'] ?? 0);
        $orcamentoId = (int)($input['orcamento_id'] ?? 0);
        $observacao = (string)($input['observacao'] ?? '');

        if ($valorDesejado <= 0 || $orcamentoId <= 0) {
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'erro' => 'Parâmetros inválidos',
            ]);
            exit;
        }

        Logger::info('orcamentos.adequacao.aplicar', [
            'id' => $orcamentoId,
            'valor_desejado' => $valorDesejado,
        ]);

        try {
            $resultado = OrcamentoAdequacao::aplicarAdequacao($orcamentoId, $valorDesejado, $observacao);

            if (!is_array($resultado) || empty($resultado['sucesso'])) {
                http_response_code(400);
                echo json_encode([
                    'sucesso' => false,
                    'erro' => (string)($resultado['erro'] ?? 'Falha ao aplicar adequação.'),
                    'resultado' => $resultado,
                ]);
                exit;
            }

            $itensAtualizados = (int)($resultado['itens_atualizados'] ?? 0);
            if ($itensAtualizados <= 0) {
                http_response_code(500);
                echo json_encode([
                    'sucesso' => false,
                    'erro' => 'Nenhum item foi atualizado. Verifique se o orçamento possui itens.',
                    'resultado' => $resultado,
                ]);
                exit;
            }

            $totaisDepois = OrcamentoItem::getTotaisGerais($orcamentoId);

            echo json_encode([
                'sucesso' => true,
                'mensagem' => (string)($resultado['mensagem'] ?? 'Adequação aplicada com sucesso.'),
                'resultado' => $resultado,
                'totais_depois' => $totaisDepois,
            ]);
        } catch (\Throwable $e) {
            Logger::error('orcamentos.adequacao.aplicar.error', [
                'id' => $orcamentoId,
                'message' => $e->getMessage(),
                'type' => get_class($e),
            ]);
            http_response_code(500);
            echo json_encode([
                'sucesso' => false,
                'erro' => $e->getMessage(),
            ]);
        }

        exit;
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

    public function createSinapi(): void
    {
        Logger::info('orcamentos.createSinapi');
        $this->render('orcamentos/create_sinapi', [
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
            'errors' => [],
        ]);
    }

    public function storeSinapi(): void
    {
        Logger::info('orcamentos.storeSinapi.start');
        $data = Orcamento::normalize($_POST);
        
        $data['logo_path'] = '';
        $data['capa_path_1'] = '';
        $data['capa_path_2'] = '';
        $data['capa_path_3'] = '';
        $data['capa_path_4'] = '';
        $data['tipo_orcamento'] = 'sinapi';
        
        if (!empty($_FILES['logo']['tmp_name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = bin2hex(random_bytes(8)) . '.' . $ext;
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                $data['logo_path'] = '/public/uploads/logos/' . $filename;
                Logger::info('orcamentos.storeSinapi.logo_uploaded', ['path' => $data['logo_path']]);
            }
        }
        
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($_FILES['capa_' . $i]['tmp_name'])) {
                $uploadDir = __DIR__ . '/../../public/uploads/capas/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($_FILES['capa_' . $i]['name'], PATHINFO_EXTENSION);
                $filename = bin2hex(random_bytes(8)) . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['capa_' . $i]['tmp_name'], $targetPath)) {
                    $data['capa_path_' . $i] = '/public/uploads/capas/' . $filename;
                    Logger::info('orcamentos.storeSinapi.capa_uploaded', ['index' => $i, 'path' => $data['capa_path_' . $i]]);
                }
            }
        }
        
        $errors = Orcamento::validate($data);
        if ($errors) {
            Logger::warning('orcamentos.storeSinapi.validation_failed', ['errors' => $errors]);
            $this->render('orcamentos/create_sinapi', [
                'orcamento' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        $id = Orcamento::create($data);
        Logger::info('orcamentos.storeSinapi.created', ['id' => $id, 'numero_proposta' => $data['numero_proposta'] ?? null]);

        $this->redirect('/?route=orcamentos/showSinapi&id=' . $id);
    }

    public function showSinapi(): void
    {
        // Iniciar sessão se não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Forçar exibição de erros
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        
        $id = (int)($_GET['id'] ?? 0);
        Logger::info('orcamentos.showSinapi', ['id' => $id]);

        try {
            $orcamento = Orcamento::find($id);
            if (!$orcamento) {
                Logger::warning('orcamentos.showSinapi.not_found', ['id' => $id]);
                $this->redirect('/?route=orcamentos/index');
                return;
            }

            $itens = OrcamentoItem::allByOrcamento($id);

            $this->render('orcamentos/show_sinapi', [
                'orcamento' => $orcamento,
                'itens' => $itens,
                'grupos' => OrcamentoOpcao::namesByTipo('grupo'),
                'categorias' => OrcamentoOpcao::namesByTipo('categoria'),
                'unidades' => OrcamentoOpcao::namesByTipo('unidade'),
                'item' => [
                    'grupo' => 'MATERIAIS',
                    'categoria' => 'ALVENARIA',
                    'codigo' => '1.1',
                    'descricao' => '',
                    'quantidade' => '0',
                    'unidade' => 'un',
                    'valor_unitario' => '0',
                    'custo_material' => '0',
                    'custo_mao_obra' => '0',
                    'ordem' => '0',
                ],
                'errors' => [],
            ]);
        } catch (\Throwable $e) {
            Logger::error('orcamentos.showSinapi.error', [
                'id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            http_response_code(500);
            
            // Exibir erro detalhado na tela
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Erro - Orçamento SINAPI</title>';
            echo '<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#fff;}';
            echo '.error-box{background:#2d1f1f;border:2px solid #ff4444;border-radius:8px;padding:20px;margin:20px 0;}';
            echo '.error-title{color:#ff4444;font-size:20px;font-weight:bold;margin-bottom:10px;}';
            echo '.error-message{color:#ffaa44;font-size:14px;margin:10px 0;padding:10px;background:#1a1a1a;border-radius:4px;}';
            echo '.error-trace{color:#888;font-size:11px;margin-top:15px;padding:10px;background:#0a0a0a;border-radius:4px;overflow-x:auto;}';
            echo '.btn{display:inline-block;padding:10px 20px;background:#4CAF50;color:#fff;text-decoration:none;border-radius:6px;margin-top:15px;}';
            echo '</style></head><body>';
            echo '<div class="error-box">';
            echo '<div class="error-title">❌ Erro ao carregar orçamento SINAPI</div>';
            echo '<div class="error-message"><strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<div class="error-message"><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . '</div>';
            echo '<div class="error-message"><strong>Linha:</strong> ' . $e->getLine() . '</div>';
            echo '<div class="error-trace"><strong>Stack Trace:</strong><br><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre></div>';
            echo '<a href="/?route=orcamentos/index" class="btn">← Voltar para lista de orçamentos</a>';
            echo '</div></body></html>';
        }
    }

    public function aplicarDescontoGrupo(): void
    {
        header('Content-Type: application/json');
        
        try {
            $json = file_get_contents('php://input');
            $payload = json_decode($json, true);
            
            if (!$payload || !isset($payload['orcamento_id']) || !isset($payload['grupo']) || !isset($payload['desconto'])) {
                echo json_encode(['success' => false, 'error' => 'Payload inválido']);
                return;
            }
            
            $orcamentoId = (int)$payload['orcamento_id'];
            $grupo = (string)$payload['grupo'];
            $desconto = (float)$payload['desconto'];
            
            // Buscar todos os itens do grupo
            $itens = OrcamentoItem::allByOrcamento($orcamentoId);
            $count = 0;
            
            foreach ($itens as $item) {
                if ((string)($item['grupo'] ?? '') === $grupo) {
                    // Pegar o valor de cobrança atual
                    $valorCobrancaAtual = (float)($item['valor_cobranca'] ?? 0);
                    
                    // Se não tiver valor_cobranca, usar valor_unitario
                    if ($valorCobrancaAtual == 0) {
                        $valorCobrancaAtual = (float)($item['valor_unitario'] ?? 0);
                    }
                    
                    // Aplicar ajuste sobre o valor atual
                    // Exemplo: valor = 24, desconto = -50 → 24 * (1 + (-50/100)) = 24 * 0.5 = 12
                    // Exemplo: valor = 24, aumento = 50 → 24 * (1 + (50/100)) = 24 * 1.5 = 36
                    $novoValorCobranca = $valorCobrancaAtual * (1 + ($desconto / 100));
                    
                    // Preparar dados para atualização
                    $data = [
                        'grupo' => $item['grupo'],
                        'categoria' => $item['categoria'],
                        'codigo' => $item['codigo'],
                        'descricao' => $item['descricao'],
                        'quantidade' => $item['quantidade'],
                        'unidade' => $item['unidade'],
                        'valor_unitario' => $item['valor_unitario'],
                        'ordem' => $item['ordem'],
                        'etapa' => $item['etapa'] ?? '',
                        'custo_material' => $item['custo_material'] ?? 0,
                        'custo_mao_obra' => $item['custo_mao_obra'] ?? 0,
                        'custo_equipamento' => $item['custo_equipamento'] ?? 0,
                        'valor_cobranca' => $novoValorCobranca,
                        'margem_lucro' => $item['margem_lucro'] ?? 0,
                        'desconto_item' => $item['desconto_item'] ?? 0,
                        'percentual_realizado' => $item['percentual_realizado'] ?? 0,
                        'margem_personalizada' => $desconto,
                        'usa_margem_personalizada' => 1,
                    ];
                    
                    OrcamentoItem::update((int)$item['id'], $data);
                    $count++;
                }
            }
            
            Logger::info('orcamentos.aplicarDescontoGrupo.success', [
                'orcamento_id' => $orcamentoId,
                'grupo' => $grupo,
                'desconto' => $desconto,
                'count' => $count
            ]);
            
            echo json_encode(['success' => true, 'count' => $count]);
            
        } catch (\Throwable $e) {
            Logger::error('orcamentos.aplicarDescontoGrupo.error', ['message' => $e->getMessage()]);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function addFromSinapi(): void
    {
        header('Content-Type: application/json');
        
        try {
            $json = file_get_contents('php://input');
            $payload = json_decode($json, true);
            
            if (!$payload || !isset($payload['orcamento_id']) || !isset($payload['itens'])) {
                echo json_encode(['success' => false, 'error' => 'Payload inválido']);
                return;
            }
            
            $orcamentoId = (int)$payload['orcamento_id'];
            $elementoNome = (string)($payload['elemento_nome'] ?? 'Elemento SINAPI');
            $grupoSelecionado = (string)($payload['grupo_selecionado'] ?? '');
            $categoriaSelecionada = (string)($payload['categoria_selecionada'] ?? '');
            $itens = $payload['itens'];
            
            // Buscar orçamento para obter margens globais
            $orcamento = Orcamento::find($orcamentoId);
            if (!$orcamento) {
                echo json_encode(['success' => false, 'error' => 'Orçamento não encontrado']);
                return;
            }
            
            $margemMaoObra = (float)($orcamento['margem_mao_obra'] ?? 50);
            $margemMateriais = (float)($orcamento['margem_materiais'] ?? 20);
            $margemEquipamentos = (float)($orcamento['margem_equipamentos'] ?? 20);
            
            if (!is_array($itens) || empty($itens)) {
                echo json_encode(['success' => false, 'error' => 'Nenhum item para adicionar']);
                return;
            }
            
            Logger::info('orcamentos.addFromSinapi.start', [
                'orcamento_id' => $orcamentoId,
                'elemento' => $elementoNome,
                'margem_materiais' => $margemMateriais,
                'margem_mao_obra' => $margemMaoObra,
                'margem_equipamentos' => $margemEquipamentos,
                'count' => count($itens)
            ]);
            
            // Mapear elemento para etapa
            $elementoEtapaMap = [
                'muro_tijolo_furado' => 'CINZA',
                'laje_macica' => 'CINZA',
                'fundacao_corrida' => 'CINZA',
                'contrapiso' => 'CINZA',
                'piso_ceramico' => 'ACABAMENTOS',
                'revestimento_parede' => 'ACABAMENTOS',
                'pintura' => 'ACABAMENTOS',
                'telhado' => 'ACABAMENTOS',
                'impermeabilizacao' => 'ACABAMENTOS',
                'calcada' => 'ACABAMENTOS',
            ];
            
            // Determinar etapa baseado no elemento
            $elementoId = '';
            foreach ($elementoEtapaMap as $elId => $etapa) {
                if (stripos($elementoNome, $elId) !== false || stripos($elementoNome, str_replace('_', ' ', $elId)) !== false) {
                    $elementoId = $elId;
                    break;
                }
            }
            $etapaDestino = $elementoEtapaMap[$elementoId] ?? 'CINZA';
            
            // Mapear tipo para grupo/categoria baseado na etapa
            $tipoMap = [
                'material' => ['grupo' => 'MATERIAIS', 'categoria' => 'MATERIAIS ' . $etapaDestino],
                'mao' => ['grupo' => 'MÃO DE OBRA', 'categoria' => 'MÃO DE OBRA ' . $etapaDestino],
                'equip' => ['grupo' => 'EQUIPAMENTOS', 'categoria' => 'EQUIPAMENTOS'],
            ];
            
            // Obter próximos códigos disponíveis por grupo (formato X.Y sequencial)
            $itensExistentes = OrcamentoItem::allByOrcamento($orcamentoId);
            $maxCodigos = [];
            
            foreach ($itensExistentes as $item) {
                $grupo = (string)($item['grupo'] ?? '');
                $codigo = (string)($item['codigo'] ?? '');
                
                // Inicializar grupo se não existir
                if (!isset($maxCodigos[$grupo])) {
                    $maxCodigos[$grupo] = ['major' => 0, 'minor' => 0];
                }
                
                if (preg_match('/^(\d+)\.(\d+)/', $codigo, $m)) {
                    $major = (int)$m[1];
                    $minor = (int)$m[2];
                    if ($major > $maxCodigos[$grupo]['major']) {
                        $maxCodigos[$grupo]['major'] = $major;
                        $maxCodigos[$grupo]['minor'] = $minor;
                    } elseif ($major === $maxCodigos[$grupo]['major'] && $minor > $maxCodigos[$grupo]['minor']) {
                        $maxCodigos[$grupo]['minor'] = $minor;
                    }
                }
            }
            
            // Inicializar contadores sequenciais
            $contadores = [];
            foreach ($maxCodigos as $grupo => $vals) {
                if (!isset($vals['major']) || !isset($vals['minor'])) {
                    $contadores[$grupo] = ['major' => 1, 'minor' => 1];
                } elseif ($vals['major'] === 0) {
                    $contadores[$grupo] = ['major' => 1, 'minor' => 1];
                } else {
                    $contadores[$grupo] = ['major' => $vals['major'], 'minor' => $vals['minor'] + 1];
                }
            }
            
            $count = 0;
            
            // Agrupar itens por tipo (material, mao, equip)
            $itensPorTipo = ['material' => [], 'mao' => [], 'equip' => []];
            foreach ($itens as $item) {
                $tipo = (string)($item['tipo'] ?? 'material');
                if (!isset($itensPorTipo[$tipo])) {
                    $itensPorTipo[$tipo] = [];
                }
                $itensPorTipo[$tipo][] = $item;
            }
            
            // Processar cada tipo separadamente
            foreach ($itensPorTipo as $tipo => $itensDoTipo) {
                if (empty($itensDoTipo)) {
                    continue;
                }
                
                // Determinar grupo e categoria
                if ($grupoSelecionado !== '' && $categoriaSelecionada !== '') {
                    // Usar seleção do usuário, mas adicionar sufixo baseado no tipo
                    if ($tipo === 'material') {
                        $grupo = $grupoSelecionado;
                        $categoria = $categoriaSelecionada . ' - MATERIAIS';
                    } elseif ($tipo === 'mao') {
                        $grupo = $grupoSelecionado;
                        $categoria = $categoriaSelecionada . ' - MÃO DE OBRA';
                    } else {
                        $grupo = $grupoSelecionado;
                        $categoria = $categoriaSelecionada . ' - EQUIPAMENTOS';
                    }
                } else {
                    // Fallback para lógica antiga
                    $mapping = $tipoMap[$tipo] ?? $tipoMap['material'];
                    $grupo = $mapping['grupo'];
                    $categoria = $mapping['categoria'];
                }
                
                // Garantir que o grupo existe no array de contadores
                if (!isset($contadores[$grupo])) {
                    $contadores[$grupo] = ['major' => 1, 'minor' => 1];
                }
                
                // Obter código sequencial para este grupo
                $codigo = $contadores[$grupo]['major'] . '.' . $contadores[$grupo]['minor'];
                
                foreach ($itensDoTipo as $item) {
                    $quantidade = (float)($item['qty'] ?? 0);
                    $preco = (float)($item['preco'] ?? 0);
                    
                    // Determinar margem baseada no tipo
                    $margemAplicavel = 0;
                    if ($tipo === 'material') {
                        $margemAplicavel = $margemMateriais;
                    } elseif ($tipo === 'mao') {
                        $margemAplicavel = $margemMaoObra;
                    } else { // equip
                        $margemAplicavel = $margemEquipamentos;
                    }
                    
                    // Calcular valor de cobrança com margem global
                    $valorUnitarioComMargem = round($preco * (1 + $margemAplicavel / 100), 2);
                    
                    $data = [
                        'grupo' => $grupo,
                        'categoria' => $categoria,
                        'codigo' => $codigo,
                        'descricao' => (string)($item['nome'] ?? ''),
                        'quantidade' => (string)$quantidade,
                        'unidade' => (string)($item['un'] ?? 'un'),
                        'custo_material' => $tipo === 'material' ? (string)$preco : '0',
                        'custo_mao_obra' => $tipo === 'mao' ? (string)$preco : '0',
                        'custo_equipamento' => $tipo === 'equip' ? (string)$preco : '0',
                        'valor_unitario' => (string)$preco,
                        'valor_cobranca' => (string)$valorUnitarioComMargem,
                        'margem_personalizada' => '0',
                        'usa_margem_personalizada' => '0',
                        'etapa' => $etapaDestino,
                        'ordem' => '0',
                    ];
                    
                    OrcamentoItem::create($orcamentoId, $data);
                    $count++;
                    
                    // Incrementar código para próximo item
                    $contadores[$grupo]['minor']++;
                    $codigo = $contadores[$grupo]['major'] . '.' . $contadores[$grupo]['minor'];
                }
            }
            
            Logger::info('orcamentos.addFromSinapi.success', ['count' => $count]);
            echo json_encode(['success' => true, 'count' => $count]);
            
        } catch (\Throwable $e) {
            Logger::error('orcamentos.addFromSinapi.error', ['message' => $e->getMessage()]);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function render(string $view, array $params = []): void
    {
        try {
            extract($params, EXTR_SKIP);
            $viewPath = __DIR__ . '/../Views/' . $view . '.php';

            if (!file_exists($viewPath)) {
                throw new \Exception("View não encontrada: $viewPath");
            }

            ob_start();
            require $viewPath;
            $content = ob_get_clean();

            if ($content === false) {
                throw new \Exception("Falha ao capturar conteúdo da view");
            }

            require __DIR__ . '/../Views/layout.php';
        } catch (\Throwable $e) {
            // Limpar qualquer output buffer
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Exibir erro
            http_response_code(500);
            echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Erro</title>';
            echo '<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#fff;}</style>';
            echo '</head><body>';
            echo '<h1>Erro ao renderizar view</h1>';
            echo '<p><strong>View:</strong> ' . htmlspecialchars($view) . '</p>';
            echo '<p><strong>Erro:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><strong>Arquivo:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
            echo '<p><strong>Linha:</strong> ' . $e->getLine() . '</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</body></html>';
        }
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

    public function reorderItems(): void
    {
        header('Content-Type: application/json');
        
        try {
            $json = file_get_contents('php://input');
            $payload = json_decode($json, true);
            
            if (!$payload || !isset($payload['orcamento_id'])) {
                echo json_encode(['success' => false, 'error' => 'Payload inválido']);
                return;
            }
            
            $orcamentoId = (int)$payload['orcamento_id'];
            
            // Verificar se orçamento existe
            $orcamento = Orcamento::find($orcamentoId);
            if (!$orcamento) {
                echo json_encode(['success' => false, 'error' => 'Orçamento não encontrado']);
                return;
            }
            
            $pdo = \App\Core\Database::pdo();
            
            // Processar reordenação por categorias
            if (isset($payload['categories']) && is_array($payload['categories'])) {
                $categories = $payload['categories'];
                
                Logger::info('orcamentos.reorderItems.categories', [
                    'orcamento_id' => $orcamentoId,
                    'count_categories' => count($categories)
                ]);
                
                $stmtUpdate = $pdo->prepare('UPDATE orcamento_itens SET ordem = :ordem, codigo = :codigo WHERE id = :id AND orcamento_id = :orcamento_id');
                
                $updatedCodes = [];
                $totalUpdated = 0;
                
                foreach ($categories as $catData) {
                    $ordemCategoria = (int)($catData['ordem_categoria'] ?? 0);
                    $items = $catData['items'] ?? [];
                    
                    // Renumerar itens da categoria: categoria 1 = 1.1, 1.2, ...; categoria 4 = 4.1, 4.2, ...
                    foreach ($items as $item) {
                        $itemId = (int)($item['id'] ?? 0);
                        $ordemItem = (int)($item['ordem'] ?? 0);
                        
                        // Código = ordem_categoria . ordem_item
                        $newCode = $ordemCategoria . '.' . $ordemItem;
                        
                        $stmtUpdate->execute([
                            ':id' => $itemId,
                            ':ordem' => ($ordemCategoria * 1000) + $ordemItem, // Ordem global para manter sequência
                            ':codigo' => $newCode,
                            ':orcamento_id' => $orcamentoId
                        ]);
                        
                        $updatedCodes[$itemId] = $newCode;
                        $totalUpdated++;
                    }
                }
                
                Logger::info('orcamentos.reorderItems.success', [
                    'count_categories' => count($categories),
                    'total_items' => $totalUpdated
                ]);
                
                echo json_encode([
                    'success' => true,
                    'count_categories' => count($categories),
                    'total_items' => $totalUpdated,
                    'updated_codes' => $updatedCodes
                ]);
                
            } else {
                echo json_encode(['success' => false, 'error' => 'Estrutura de dados inválida']);
            }
            
        } catch (\Throwable $e) {
            Logger::error('orcamentos.reorderItems.error', ['message' => $e->getMessage()]);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
