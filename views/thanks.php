<?php
require_once __DIR__ . '/../autoload.php';

use App\Utils\HeaderBuilder;

$header = new HeaderBuilder();
$header->setTitle("Sucesso - Envio de Ficha")
    ->setDescription("Sua contribuição assistencial foi registrada com sucesso.");

$formId = $_GET['id'] ?? null;
?>

<!DOCTYPE html>
<html lang="<?= $header->getLang() ?>">
<?php $header->render(); ?>

<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-md-8 col-lg-6 text-center">

                <div class="mb-4">
                    <div class="display-1 text-success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="text-primary fw-bold mb-3">Tudo certo!</h2>
                        <p class="lead text-muted mb-4">
                            Sua ficha foi enviada com sucesso para o sistema.
                        </p>

                        <div class="alert alert-info border-0 bg-light text-start small">
                            <strong>O que acontece agora?</strong><br>
                            Seus dados e o comprovante (PDF) passarão por uma conferência.
                            Caso haja alguma divergência, entraremos em contato via e-mail.
                        </div>

                        <hr class="my-4 opacity-25">

                        <div class="d-grid">
                            <a href="<?= $formId ? '/views/form.php?id=' . htmlspecialchars($formId) : 'https://sindipetro.org.br/' ?>" class="btn btn-outline-primary">
                                Enviar outra resposta
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</body>

</html>