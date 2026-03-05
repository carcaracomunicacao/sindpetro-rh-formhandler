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
$auth->requireAnyRole(['admin', 'owner']);
$authUser = $auth->user();

$repo = new FormSubmissionsRepository($db);

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

$success = false;
$errors  = [];

// POST — salva edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = $_POST['values'] ?? [];

    foreach ($values as $valueId => $newValue) {
        $valueId  = (int) $valueId;
        $newValue = trim($newValue);

        if (!$repo->updateValue($valueId, $newValue)) {
            $errors[] = "Erro ao atualizar campo ID {$valueId}.";
        }
    }

    if (empty($errors)) {
        header('Location: /admin/submissions/' . $id);
        exit;
    }

    // Recarrega os valores atualizados em caso de erro
    $submission = $repo->findByIdWithValues($id);
}

$header = new HeaderBuilder();
$header->setTitle('Editar Submissão #' . $id . ' — Admin')
    ->setDescription('Edição de submissão');
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
        <div class="mb-4">
            <p class="text-muted small fw-semibold text-uppercase mb-0">
                <i class="bi bi-inbox me-1"></i>
                <a href="/admin/dashboard" class="text-muted text-decoration-none">Submissões</a>
                <i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>
                <a href="/admin/submissions/<?= $id ?>" class="text-muted text-decoration-none">
                    #<?= $id ?>
                </a>
                <i class="bi bi-chevron-right mx-1" style="font-size:.7rem"></i>
                Editar
            </p>
            <h2 class="text-primary fw-bold mb-0">Editando Submissão #<?= $submission['id'] ?></h2>
            <p class="text-muted small mb-0 mt-1">
                <?= htmlspecialchars($submission['form_title']) ?> &nbsp;·&nbsp;
                <?= date('d/m/Y \à\s H:i', strtotime($submission['submitted_at'])) ?>
            </p>
        </div>

        <!-- Erros -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mb-4">
                <?php foreach ($errors as $error): ?>
                    <p class="mb-0 small"><i class="bi bi-exclamation-circle me-1"></i><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de edição -->
        <form method="POST" action="">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <?php foreach ($submission['values'] as $val): ?>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <?= htmlspecialchars($val['label']) ?>
                            </label>

                            <?php if ($val['field_type'] === 'file'): ?>
                                <!-- Arquivo: só visualiza, não edita -->
                                <div>
                                    <?php if ($val['field_value']): ?>
                                        <a href="/storage/<?= $submission['form_uuid'] ?>/<?= $val['field_value'] ?>"
                                            target="_blank" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-file-pdf me-1"></i>Visualizar PDF
                                        </a>
                                        <p class="text-muted small mt-1 mb-0">Arquivos não podem ser editados aqui.</p>
                                    <?php else: ?>
                                        <span class="text-muted small">Nenhum arquivo enviado.</span>
                                    <?php endif; ?>
                                </div>

                            <?php elseif ($val['field_type'] === 'textarea'): ?>
                                <textarea
                                    name="values[<?= $val['value_id'] ?>]"
                                    class="form-control"
                                    rows="3"><?= htmlspecialchars($val['field_value'] ?? '') ?></textarea>

                            <?php elseif ($val['field_type'] === 'checkbox'): ?>
                                <?php $items = json_decode($val['field_value'], true) ?? []; ?>
                                <input
                                    type="text"
                                    name="values[<?= $val['value_id'] ?>]"
                                    class="form-control"
                                    value="<?= htmlspecialchars(implode(', ', $items)) ?>">
                                <p class="text-muted small mt-1 mb-0">Separe múltiplos valores por vírgula.</p>

                            <?php else: ?>
                                <input
                                    type="text"
                                    name="values[<?= $val['value_id'] ?>]"
                                    class="form-control"
                                    value="<?= htmlspecialchars($val['field_value'] ?? '') ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Ações -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Salvar alterações
                </button>
                <a href="/admin/submissions/<?= $id ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x me-1"></i> Cancelar
                </a>
            </div>

        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>