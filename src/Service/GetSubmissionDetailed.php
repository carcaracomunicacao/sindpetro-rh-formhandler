<?php
require_once __DIR__ . '/../../autoload.php';
use App\Connection\PDOConnection;

$id = (int)$_GET['id'];
$db = (new PDOConnection())->getPDO();

// Query para buscar TODOS os campos e valores da submissão
$sql = "SELECT f.label, v.field_value, f.field_type, f.form_id, frm.uuid as form_uuid
        FROM spfh_submission_values v
        JOIN spfh_form_fields f ON v.field_id = f.id
        JOIN spfh_form_submissions s ON v.submission_id = s.id
        JOIN spfh_forms frm ON s.form_id = frm.id
        WHERE v.submission_id = :id";

$stmt = $db->prepare($sql);
$stmt->execute(['id' => $id]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$details) echo "Nenhum dado encontrado.";

foreach ($details as $row): ?>
    <div class="mb-4 pb-2 border-bottom">
        <label class="form-label text-muted small fw-bold text-uppercase"><?= htmlspecialchars($row['label']) ?></label>
        <div class="fs-5">
            <?php if ($row['field_type'] === 'file'): ?>
                <a href="/storage/<?= $row['form_uuid'] ?>/<?= $row['field_value'] ?>" target="_blank" class="btn btn-danger btn-sm">
                    <i class="bi bi-file-pdf"></i> Abrir PDF
                </a>
            <?php else: ?>
                <?= nl2br(htmlspecialchars($row['field_value'])) ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>