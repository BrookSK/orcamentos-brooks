# Estimativa de Custos (PHP 8 + MVC + SQLite)

## Rodar o projeto

1. Garanta que você tenha **PHP 8.x** instalado.
2. No terminal, execute o servidor embutido apontando para a pasta `public`:

```bash
php -S localhost:8000 -t public
```

3. Acesse:

- `http://localhost:8000/`

## Estrutura

- `public/index.php` (front controller / roteamento simples)
- `app/Controllers` (controllers)
- `app/Models` (model + regras e cálculo do `valor_total`)
- `app/Views` (views)
- `storage/database.sqlite` (SQLite; criado automaticamente)

## Observações

- `valor_total` é calculado automaticamente a partir de `quantidade * valor_unitario`.
- A tela principal agrupa por `categoria`, exibe **subtotal por categoria** e **total geral**.
