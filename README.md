# Clube Hub MVP

MVP funcional para gerenciamento de clubes com uma matriz e multiplas filiais, construido com Laravel 12, PHP 8.3+, PostgreSQL, Blade e Tailwind CSS.

## Escopo do MVP

- Matriz e filiais com escopo operacional distinto
- RBAC por perfil: Admin Matriz, Admin Filial, Associado e Dependente
- Adesao publica por filial em `/adesao/{slug}`
- Gestao de associados, dependentes e aprovacoes
- Planos configuraveis com regras de beneficios
- Financeiro basico com geracao mensal de cobrancas e baixa manual
- Recursos reservaveis com agenda, bloqueios e disponibilidade via API
- Dashboards e relatorios administrativos
- Auditoria basica e uso de cache para metricas

## Stack

- PHP 8.3+
- Laravel 12
- PostgreSQL
- Blade + Tailwind CSS
- Cache em banco
- Queue em banco

## Perfis de demonstracao

- `admin.matriz@clube.test` / `password`
- `admin.zonasul@clube.test` / `password`
- `associado@clube.test` / `password`
- `dependente@clube.test` / `password`

## Estrutura da aplicacao

- `app/Services`: regras de negocio de adesao, aprovacao, financeiro, reservas, dashboard e relatorios
- `app/Policies`: autorizacao por papel e por filial
- `app/Http/Requests`: validacao centralizada
- `routes/web.php`: frontend Blade
- `routes/api.php`: endpoints JSON para disponibilidade, dashboard e relatorios
- `database/migrations`: modelo relacional do produto
- `database/seeders`: dados iniciais e ambiente de demonstracao

## Configuracao local

1. Ajuste o `.env` com credenciais reais do PostgreSQL.
2. Instale dependencias:

```bash
composer install
npm install
```

3. Gere a estrutura do banco:

```bash
php artisan migrate --seed
```

4. Rode a aplicacao:

```bash
php artisan serve
npm run dev
```

## Rodar no PC e abrir no celular

Para testar a carteirinha no celular usando a mesma rede Wi-Fi do computador:

```bash
php artisan serve --host 0.0.0.0 --port 8000
```

Depois abra no PC:

```text
http://127.0.0.1:8000
```

O QR code da carteirinha passa a detectar o IP local da maquina automaticamente quando o sistema estiver em `localhost`, entao o celular consegue abrir a validacao publica na mesma rede.

Se quiser forcar um endereco especifico no QR code, configure:

```env
CARD_PUBLIC_BASE_URL=http://192.168.0.55:8000
```

## Observacoes de produto

- Nao existe superadmin neste MVP.
- Upload de documentos, e-mail, gateway de pagamento real e integracao com catraca ficaram fora do escopo propositalmente.
- O modulo financeiro ja foi organizado para futura integracao com gateway.
- O modulo de reservas usa regras configuraveis por plano e expone disponibilidade por API.
