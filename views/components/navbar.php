<?php

/**
 * Componente: Navbar
 *
 * Variáveis esperadas:
 * @var array  $authUser  Usuário autenticado vindo do AuthService->user()
 * @var string $activePage  Identificador da página ativa (ex: 'submissions', 'users', 'forms')
 */

$activePage = $activePage ?? '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid px-4">

        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="/admin/dashboard">
            <i class="bi bi-ui-checks-grid me-2"></i>SPFH Admin
        </a>

        <!-- Toggle mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">

            <!-- Nav links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $activePage === 'submissions' ? 'active' : '' ?>" href="/admin/dashboard">
                        <i class="bi bi-table me-1"></i>Submissões
                    </a>
                </li>
                <!-- Adicione novos itens aqui -->
            </ul>

            <!-- Usuário + Logout -->
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item">
                    <span class="navbar-text text-white-50 small">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($authUser['name']) ?>
                        <span class="badge bg-white text-primary ms-1 fw-semibold" style="font-size: .65rem;">
                            <?= htmlspecialchars(strtoupper($authUser['roles'][0] ?? '')) ?>
                        </span>
                    </span>
                </li>
                <li class="nav-item">
                    <a href="/logout" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>Sair
                    </a>
                </li>
            </ul>

        </div>
    </div>
</nav>