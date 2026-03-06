<?php

use App\Connection\PDOConnection;
use App\Repository\FormRepository;
use App\Repository\FormFieldsRepository;
use App\Repository\FieldOptionsRepository;
use App\Repository\FormSubmissionsRepository;
use App\Service\FormService;
use App\Utils\HeaderBuilder;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use App\Service\AuthService;

require_once __DIR__ . '/../../autoload.php';

// Auth
$db           = (new PDOConnection())->getPDO();
$userRepo     = new UserRepository($db);
$userRoleRepo = new UserRoleRepository($db);
$auth         = new AuthService($userRepo, $userRoleRepo);
$auth->requireAuth();
$authUser = $auth->user();

$repo = new FormSubmissionsRepository($db);

$formService = new FormService(
    new FormRepository($db),
    new FormFieldsRepository($db),
    new FieldOptionsRepository($db)
);
$allForms = $formService->getAllActiveForms();

// Configuração da Paginação
$limit = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], [15, 30, 60, 120, 240])
    ? (int)$_GET['per_page']
    : 15;
$page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$formId     = isset($_GET['form_id']) ? (int) $_GET['form_id'] : null;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : null;

$submissions = $repo->getAllSubmissionsPaginated($limit, $offset, $searchTerm, $formId);
$totalRows   = $repo->getTotalSubmissionsCount($formId);
$totalPages  = ceil($totalRows / $limit);

// Título do formulário selecionado
$selectedForm = $formId
    ? (array_values(array_filter($allForms, fn($f) => $f['id'] == $formId))[0] ?? null)
    : null;
$formTitle = $selectedForm ? $selectedForm['title'] : null;

// Cabeçalho SEO
$header = new HeaderBuilder();
$header->setTitle('Admin — ' . ($formTitle ?? 'Submissões'))
    ->setDescription('Visualização de respostas');
?>
<!DOCTYPE html>
<html lang="<?= $header->getLang() ?>">
<?php $header->render(); ?>

<body class="bg-light">

    <?php
    $activePage = 'submissions';
    include __DIR__ . '/../../views/components/navbar.php';
    ?>

    <div class="container-fluid mt-4">

        <!-- Título com chapéu -->
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-start gap-3 mb-4">
            <div>
                <p class="text-muted small fw-semibold text-uppercase mb-0">
                    <i class="bi bi-inbox me-1"></i>Submissões
                </p>
                <h2 class="text-primary fw-bold mb-0">
                    <?= $formTitle
                        ? htmlspecialchars($formTitle)
                        : '<span class="text-muted fw-normal fs-4">Selecione um formulário</span>'
                    ?>
                </h2>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="" method="GET" class="row g-3">
                    <div class="col-12 col-md-3">
                        <select name="form_id" class="form-select" required onchange="this.form.submit()">
                            <option value="" disabled <?= !$formId ? 'selected' : '' ?>>Selecione um formulário...</option>
                            <?php foreach ($allForms as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= $formId == $f['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0"
                                placeholder="Buscar por Nome ou CPF..."
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                                <?= !$formId ? 'disabled' : '' ?>>
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <select name="per_page" class="form-select" onchange="this.form.submit()" <?= !$formId ? 'disabled' : '' ?>>
                            <?php foreach ([15, 30, 60, 120, 240] as $opt): ?>
                                <option value="<?= $opt ?>" <?= $limit == $opt ? 'selected' : '' ?>>
                                    <?= $opt ?> por página
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary" <?= !$formId ? 'disabled' : '' ?>>
                            Filtrar
                        </button>
                    </div>
                    <div class="col-6 col-md-2 d-grid">
                        <a href="../../src/Controller/ExportCSVController.php?form_id=<?= $formId ?>"
                            class="btn btn-success <?= !$formId ? 'disabled' : '' ?>">
                            <i class="bi bi-file-earmark-excel"></i> Exportar CSV
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 d-none d-md-table">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Formulário</th>
                                <th>Data</th>
                                <th class="text-center">Documento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $sub): ?>
                                <tr>
                                    <td><?= $sub['id'] ?></td>
                                    <td><?= htmlspecialchars($sub['nome']) ?></td>
                                    <td><?= $sub['cpf'] ?></td>
                                    <td><?= $sub['form_title'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($sub['submitted_at'])) ?></td>
                                    <td class="text-center">
                                        <?php if ($sub['arquivos_pdf']): ?>
                                            <a href="/storage/<?= $sub['form_uuid'] ?>/<?= $sub['arquivos_pdf'] ?>"
                                                target="_blank" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-file-pdf"></i> Visualizar
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Nenhum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary btn-view-details" data-id="<?= $sub['id'] ?>">
                                            <i class="bi bi-search"></i> Detalhes
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Versão mobile: cards -->
                    <div class="d-md-none">
                        <?php foreach ($submissions as $sub): ?>
                            <div class="border-bottom px-3 py-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <p class="fw-semibold mb-0"><?= htmlspecialchars($sub['nome']) ?></p>
                                        <p class="text-muted small mb-1"><?= $sub['cpf'] ?></p>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-calendar2 me-1"></i><?= date('d/m/Y H:i', strtotime($sub['submitted_at'])) ?>
                                        </p>
                                    </div>
                                    <span class="badge bg-light text-muted border">#<?= $sub['id'] ?></span>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <button class="btn btn-sm btn-outline-primary btn-view-details w-100" data-id="<?= $sub['id'] ?>">
                                        <i class="bi bi-search"></i> Detalhes
                                    </button>
                                    <?php if ($sub['arquivos_pdf']): ?>
                                        <a href="/storage/<?= $sub['form_uuid'] ?>/<?= $sub['arquivos_pdf'] ?>"
                                            target="_blank" class="btn btn-sm btn-outline-danger w-100">
                                            <i class="bi bi-file-pdf"></i> PDF
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Paginação -->
                <?php if ($totalPages > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <nav>
                            <ul class="pagination">

                                <!-- Anterior -->
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&form_id=<?= $formId ?>&per_page=<?= $limit ?>&search=<?= urlencode($_GET['search'] ?? '') ?>">
                                        &laquo;
                                    </a>
                                </li>

                                <?php
                                // Mostra no máximo 5 páginas ao redor da atual
                                $start = max(1, $page - 2);
                                $end   = min($totalPages, $page + 2);
                                ?>

                                <!-- Primeira página + reticências -->
                                <?php if ($start > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=1&form_id=<?= $formId ?>&per_page=<?= $limit ?>&search=<?= urlencode($_GET['search'] ?? '') ?>">1</a>
                                    </li>
                                    <?php if ($start > 2): ?>
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Páginas do meio -->
                                <?php for ($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&form_id=<?= $formId ?>&per_page=<?= $limit ?>&search=<?= urlencode($_GET['search'] ?? '') ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Última página + reticências -->
                                <?php if ($end < $totalPages): ?>
                                    <?php if ($end < $totalPages - 1): ?>
                                        <li class="page-item disabled"><span class="page-link">…</span></li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $totalPages ?>&form_id=<?= $formId ?>&per_page=<?= $limit ?>&search=<?= urlencode($_GET['search'] ?? '') ?>">
                                            <?= $totalPages ?>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <!-- Próximo -->
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&form_id=<?= $formId ?>&per_page=<?= $limit ?>&search=<?= urlencode($_GET['search'] ?? '') ?>">
                                        &raquo;
                                    </a>
                                </li>

                            </ul>
                        </nav>
                    </div>

                    <!-- Contador de registros -->
                    <p class="text-center text-muted small mt-1">
                        <?php
                        $from = $offset + 1;
                        $to   = min($offset + $limit, $totalRows);
                        ?>
                        Exibindo <?= $from ?>–<?= $to ?> de <?= $totalRows ?> submissões
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.btn-view-details').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                window.location.href = '/admin/submissions/' + id;
            });
        });
    </script>

</body>

</html>