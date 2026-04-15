<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Prazo Encerrado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh;">

    <div class="text-center p-5 bg-white shadow rounded" style="max-width: 600px;">
        
        <h2 class="fw-bold mb-3">
            <?= htmlspecialchars($form['title']) ?>
        </h2>

        <h4 class="text-danger fw-bold mb-4">
            ⚠️ Aviso Importante: Prazo Encerrado
        </h4>

        <p class="text-muted mb-3">
            Informamos que o prazo para oposição à Contribuição Assistencial encerrou-se no dia <strong>14/04</strong>, conforme o ACT e normativos vigentes.
        </p>

        <p class="text-muted mb-4">
            Pedidos recebidos a partir de <strong>15/04</strong> encontram-se fora do prazo estabelecido e não poderão ser efetivados.
        </p>

        <img src="/assets/img/sindpedro-logo.jpg" style="padding:16px; max-width:65%; margin: auto">

    </div>

</body>
</html>