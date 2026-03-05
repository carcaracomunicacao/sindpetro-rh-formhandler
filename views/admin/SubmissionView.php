<?php

use App\Connection\PDOConnection;
use App\Repository\FormSubmissionsRepository;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use App\Service\AuthService;
use App\Utils\HeaderBuilder;

require_once __DIR__ . '/../../autoload.php';

$db           = (new PDOConnection())->getPDO();
$userRepo     = new UserRepository($db);
$userRoleRepo = new UserRoleRepository($db);
$auth         = new AuthService($userRepo, $userRoleRepo);
$auth->requireAuth();
$authUser = $auth->user();

$repo = new FormSubmissionsRepository($db);

// Pega o ID da URL (via .htaccess rewrite)
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id) {
    header('Location: /admin/dashboard');
    exit;
}

$submission = $repo->findByIdWithValues($id);

if (!$submission) {
    header('Location: /admin/dashboard');
    exit;
}

$canEdit = $auth->hasAnyRole(['admin', 'owner']);

$name = '';
foreach ($submission['values'] as $val) {
    if (stripos($val['label'], 'Nome') !== false) {
        $name = $val['field_value'];
        break;
    }
}

$header = new HeaderBuilder();
$header->setTitle('Submissão #' . $id . ' — Admin')
    ->setDescription('Visualização de submissão');
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

        <!-- Chapéu + título -->
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-start gap-3 mb-4">
            <div>
                <p class="text-muted small fw-semibold text-uppercase mb-0">
                    <i class="bi bi-inbox me-1"></i>
                    <a href="/admin/dashboard" class="text-muted text-decoration-none">Submissões</a>
                    <i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>
                    <?= htmlspecialchars($submission['form_title']) ?>
                </p>
                <h2 class="text-primary fw-bold mb-0"><?= $name ?></h2>
                <p class="text-muted small mb-0 mt-1">
                    <i class="bi bi-clock me-1"></i><?= date('d/m/Y \à\s H:i', strtotime($submission['submitted_at'])) ?>
                    &nbsp;·&nbsp;
                    <i class="bi bi-globe me-1"></i><?= htmlspecialchars($submission['ip_address'] ?? '—') ?>
                </p>
            </div>
            <?php if ($canEdit): ?>
                <a href="/admin/submissions/<?= $id ?>/edit" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
            <?php endif; ?>
        </div>

        <!-- Valores -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 35%">Campo</th>
                            <th>Resposta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submission['values'] as $val): ?>
                            <tr>
                                <td class="fw-semibold text-muted small">
                                    <?= htmlspecialchars($val['label']) ?>
                                </td>
                                <td>
                                    <?php if ($val['field_type'] === 'file'): ?>
                                        <?php if ($val['field_value']): ?>
                                            <a href="/storage/<?= $submission['form_uuid'] ?>/<?= $val['field_value'] ?>"
                                                target="_blank" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-file-pdf me-1"></i>Visualizar PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Nenhum arquivo</span>
                                        <?php endif; ?>
                                    <?php elseif ($val['field_type'] === 'checkbox'): ?>
                                        <?php
                                        $items = json_decode($val['field_value'], true) ?? [];
                                        echo implode(', ', array_map('htmlspecialchars', $items)) ?: '—';
                                        ?>
                                    <?php else: ?>
                                        <?= $val['field_value'] ? htmlspecialchars($val['field_value']) : '<span class="text-muted">—</span>' ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rodapé -->
        <div class="mt-3 d-flex gap-2">
            <a href="/admin/dashboard?form_id=<?= $submission['form_id'] ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <?php if ($canEdit): ?>
                <a href="/admin/submissions/<?= $id ?>/edit" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>