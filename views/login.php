<?php

use App\Connection\PDOConnection;
use App\Repository\UserRepository;
use App\Repository\UserRoleRepository;
use App\Service\AuthService;
use App\Utils\HeaderBuilder;

require_once __DIR__ . '../../autoload.php';

$db              = (new PDOConnection())->getPDO();
$userRepository  = new UserRepository($db);
$userRoleRepo    = new UserRoleRepository($db);
$auth            = new AuthService($userRepository, $userRoleRepo);

// Se já estiver logado, redireciona direto
if ($auth->check()) {
    $auth->redirectToDashboard();
}

$error    = null;
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    $oldEmail = htmlspecialchars($email);

    try {
        $success = $auth->login($email, $password, $remember);

        if ($success) {
            $auth->redirectToDashboard();
        } else {
            $error = 'E-mail ou senha incorretos.';
        }
    } catch (\InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (\RuntimeException $e) {
        $error = $e->getMessage();
    }
}

$header = new HeaderBuilder();
$header->setTitle('Login — Admin')
    ->setDescription('Acesso à área administrativa');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php echo $header->render(); ?>

<body class="bg-light">

    <div class="min-vh-100 d-flex flex-column justify-content-center align-items-center px-3 py-5">

        <!-- Card de Login -->
        <div class="card shadow-sm border-0 w-100" style="max-width: 420px;">

            <!-- Topo do card -->
            <div class="card-header bg-white border-bottom text-center py-4">
                <h4 class="fw-bold mb-0">Área Administrativa</h4>
                <p class="text-muted small mb-0 mt-1">Faça login para continuar</p>
            </div>

            <div class="card-body px-4 py-4">

                <!-- Alerta de erro -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        <?= $error ?>
                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                <?php endif; ?>

                <!-- Formulário -->
                <form method="POST" action="" novalidate>

                    <!-- E-mail -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">E-mail</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control <?= $error ? 'is-invalid' : '' ?>"
                            value="<?= $oldEmail ?>"
                            placeholder="seu@email.com.br"
                            required
                            autofocus
                            autocomplete="email">
                    </div>

                    <!-- Senha -->
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Senha</label>
                        <div class="input-group">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control <?= $error ? 'is-invalid' : '' ?>"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password">
                            <button
                                class="btn btn-outline-secondary"
                                type="button"
                                id="togglePassword"
                                title="Mostrar/ocultar senha">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Lembrar-me -->
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label small text-muted" for="remember">
                            Lembrar-me por 30 dias
                        </label>
                    </div>

                    <!-- Botão -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                            Entrar
                        </button>
                    </div>

                </form>

            </div><!-- /.card-body -->

        </div><!-- /.card -->

        <!-- Assinatura -->
        <p class="text-muted small mt-4 text-center">
            Desenvolvido por
            <a href="https://www.voacarcara.com.br" target="_blank" rel="noopener noreferrer" class="text-muted">
                Carcará Comunicação
            </a>
        </p>

    </div><!-- /.min-vh-100 -->

    <!-- Bootstrap Icons + JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle mostrar/ocultar senha
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        togglePassword.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>

</body>

</html>