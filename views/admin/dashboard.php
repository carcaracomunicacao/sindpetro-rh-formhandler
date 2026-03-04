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
$limit  = 15;
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
        <div class="d-flex justify-content-between align-items-start mb-4">
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
            <a href="../../src/Controller/ExportCSVController.php?form_id=<?= $formId ?>"
                class="btn btn-success btn-sm <?= !$formId ? 'disabled' : '' ?>">
                <i class="bi bi-file-earmark-excel"></i> Exportar CSV
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="" method="GET" class="row g-3">
                    <div class="col-12 col-md-4">
                        <select name="form_id" class="form-select" required onchange="this.form.submit()">
                            <option value="" disabled <?= !$formId ? 'selected' : '' ?>>Selecione um formulário...</option>
                            <?php foreach ($allForms as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= $formId == $f['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($f['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
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
                    <div class="col-12 col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary" <?= !$formId ? 'disabled' : '' ?>>Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>CPF</th>
                                <td>Formulário</td>
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
                </div>
            </div>
        </div>

    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasDetails" aria-labelledby="offcanvasDetailsLabel" style="width: 500px;">
        <div class="offcanvas-header bg-light">
            <h5 class="offcanvas-title fw-bold text-primary" id="offcanvasDetailsLabel">Detalhes da Submissão</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body" id="detailsContent">
            <div class="text-center mt-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Carregando dados...</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.btn-view-details').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const contentDiv = document.getElementById('detailsContent');
                const offcanvasElement = document.getElementById('offcanvasDetails');
                const bsOffcanvas = new bootstrap.Offcanvas(offcanvasElement);

                contentDiv.innerHTML = '<div class="text-center mt-5"><div class="spinner-border text-primary"></div></div>';
                bsOffcanvas.show();

                fetch(`../../src/Service/GetSubmissionDetailed.php?id=${id}`)
                    .then(response => response.text())
                    .then(html => {
                        contentDiv.innerHTML = html;
                    })
                    .catch(() => {
                        contentDiv.innerHTML = '<div class="alert alert-danger">Erro ao carregar detalhes.</div>';
                    });
            });
        });
    </script>

</body>

</html>