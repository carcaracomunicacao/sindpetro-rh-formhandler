<?php
require_once __DIR__ . '/../autoload.php';

use App\Connection\PDOConnection;
use App\Repository\FormRepository;
use App\Repository\FormFieldsRepository;
use App\Repository\FieldOptionsRepository;
use App\Service\RenderFormService; // Criaremos esta classe concreta abaixo
use App\Utils\HeaderBuilder;

// 1. Inicialização das Dependências
$db = (new PDOConnection())->getPDO();
$renderService = new RenderFormService(
    new FormRepository($db),
    new FormFieldsRepository($db),
    new FieldOptionsRepository($db)
);

// 2. Busca de Dados via Service
$formUuid = $_GET['id'] ?? null;
$formData = $formUuid ? $renderService->getFullFormData($formUuid) : null;

if (!$formData) {
    die("Formulário não encontrado ou inválido.");
}

$form = $formData['form'];
$fields = $formData['fields'];

// 3. Configura o Cabeçalho (SEO)
$header = new HeaderBuilder();
$header->setTitle($form['title'])
    ->setDescription($form['description'])
    ->setOgImage($form['og_image'] ?? '');
?>

<!DOCTYPE html>
<html lang="<?= $header->getLang() ?>">
<?php $header->render(); ?>

<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-md-8 col-lg-7 my-5">

                <div class="card shadow-sm border-0">
                    <img src="/assets/img/header.jpg" alt="">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="text-start mb-4 text-primary fw-bold">
                            <?= htmlspecialchars($form['title']) ?>
                        </h2>
                        <p class="text-muted text-start mb-5" style="text-align: left">
                            <?= $form['description'] ?>
                        </p>

                        <form id="mainForm" enctype="multipart/form-data">
                            <input type="hidden" name="form_id" value="<?= $form['id'] ?>">

                            <?php foreach ($fields as $field): ?>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <?= htmlspecialchars($field['label']) ?>
                                        <?= $field['is_required'] ? '<span class="text-danger">*</span>' : '' ?>
                                    </label>

                                    <?php if (!empty($field['description'])): ?>
                                        <div class="form-text text-muted mb-2" style="font-size: 0.85rem;">
                                            <?= htmlspecialchars($field['description']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($field['field_type'] === 'textarea'): ?>
                                        <textarea class="form-control" name="field_<?= $field['id'] ?>"
                                            placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
                                            <?= $field['is_required'] ? 'required' : '' ?>></textarea>

                                    <?php elseif ($field['field_type'] === 'select'): ?>
                                        <select class="form-select" name="field_<?= $field['id'] ?>" <?= $field['is_required'] ? 'required' : '' ?>>
                                            <option value=""><?= htmlspecialchars($field['placeholder'] ?: 'Selecione uma opção') ?></option>
                                            <?php foreach ($field['options'] as $opt): ?>
                                                <option value="<?= htmlspecialchars($opt['option_value']) ?>">
                                                    <?= htmlspecialchars($opt['option_label']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                    <?php elseif ($field['field_type'] === 'radio'): ?>
                                        <div class="mt-2">
                                            <?php foreach ($field['options'] as $opt): ?>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio"
                                                        name="field_<?= $field['id'] ?>"
                                                        id="opt_<?= $opt['id'] ?>"
                                                        value="<?= htmlspecialchars($opt['option_value']) ?>"
                                                        <?= $field['is_required'] ? 'required' : '' ?>>
                                                    <label class="form-check-label" for="opt_<?= $opt['id'] ?>">
                                                        <?= htmlspecialchars($opt['option_label']) ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                    <?php elseif ($field['field_type'] === 'file'): ?>
                                        <input type="file" class="form-control" name="field_<?= $field['id'] ?>"
                                            <?= $field['is_required'] ? 'required' : '' ?> accept="application/pdf">
                                        <div class="form-text small text-danger mt-1">Apenas arquivos PDF (Máx. 2MB).</div>

                                    <?php elseif ($field['field_type'] === 'email'): ?>
                                        <input type="email"
                                            class="form-control"
                                            name="field_<?= $field['id'] ?>"
                                            placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
                                            <?= $field['is_required'] ? 'required' : '' ?>>
                                    <?php else: ?>
                                        <?php
                                        // Lógica para definir se o teclado no celular deve ser numérico
                                        $isNumeric = in_array($field['field_type'], ['cpf', 'number', 'ddd']) || !empty($field['field_mask']);
                                        ?>
                                        <input type="text"
                                            class="form-control dynamic-mask"
                                            name="field_<?= $field['id'] ?>"
                                            placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
                                            data-mask="<?= htmlspecialchars($field['field_mask'] ?? '') ?>"
                                            inputmode="<?= $isNumeric ? 'numeric' : 'text' ?>"
                                            <?= $field['is_required'] ? 'required' : '' ?>>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <div class="d-grid gap-2 mt-5">
                                <button type="submit" class="btn btn-primary btn-lg">Enviar Formulário</button>
                            </div>
                        </form>
                    </div>
                </div>

                <p class="text-center mt-4 text-muted small">
                    &copy; <?= date('Y') ?> SindPetro RJ - Gerenciador de Formulários
                </p>

            </div>
        </div>
    </div>

    <script src="https://unpkg.com/imask"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.dynamic-mask').forEach(el => {
                const maskPattern = el.dataset.mask;

                // Só aplica se houver algo definido no data-mask
                if (maskPattern && maskPattern.trim() !== "") {

                    if (maskPattern === '00000-0000') {
                        // Máscara de Telefone (aceita 8 ou 9 dígitos)
                        IMask(el, {
                            mask: [{
                                    mask: '0000-0000'
                                },
                                {
                                    mask: '00000-0000'
                                }
                            ]
                        });
                    } else if (maskPattern === 'currency') {
                        // Configuração para Moeda Brasileira (R$)
                        IMask(el, {
                            mask: 'R$ num',
                            blocks: {
                                num: {
                                    mask: Number,
                                    thousandsSeparator: '.',
                                    radix: ',',
                                    mapToRadix: ['.'],
                                    scale: 2,
                                    signed: false,
                                    padFractionalZeros: true,
                                    normalizeZeros: true,
                                    min: 0
                                }
                            }
                        });
                    } else {
                        // Para as demais (CPF, Data, DDD), aplica a string direta
                        IMask(el, {
                            mask: maskPattern
                        });
                    }
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('mainForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            Swal.fire({
                title: 'Enviando dados...',
                text: 'Por favor, aguarde enquanto processamos sua ficha.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('/src/Controller/SubmitController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: data.message,
                            confirmButtonColor: '#dc3545' // Seu vermelho base
                        }).then(() => {
                            window.location.href = '/views/thanks.php?id=' + data.form_uuid;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Opa! Algo deu errado',
                            text: data.message,
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de Conexão',
                        text: 'Não foi possível contatar o servidor. Tente novamente.',
                        confirmButtonColor: '#dc3545'
                    });
                });
        });
    </script>
</body>

</html>