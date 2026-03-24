<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Item;

final class ItemController
{
    public function index(): void
    {
        $items = Item::all();
        $this->render('items/index', [
            'items' => $items,
        ]);
    }

    public function create(): void
    {
        $this->render('items/create', [
            'item' => [
                'nome' => '',
                'categoria' => '',
                'unidade' => '',
                'quantidade' => 0,
                'valor_unitario' => 0,
            ],
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        $data = Item::normalize($_POST);
        $errors = Item::validate($data);

        if ($errors) {
            $this->render('items/create', [
                'item' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        Item::create($data);
        $this->redirect('/?route=items/index');
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = Item::find($id);
        if (!$item) {
            $this->redirect('/?route=items/index');
            return;
        }

        $this->render('items/edit', [
            'item' => $item,
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $existing = Item::find($id);
        if (!$existing) {
            $this->redirect('/?route=items/index');
            return;
        }

        $data = Item::normalize($_POST);
        $errors = Item::validate($data);

        if ($errors) {
            $data['id'] = $id;
            $this->render('items/edit', [
                'item' => $data,
                'errors' => $errors,
            ]);
            return;
        }

        Item::update($id, $data);
        $this->redirect('/?route=items/index');
    }

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            Item::delete($id);
        }
        $this->redirect('/?route=items/index');
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

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}
