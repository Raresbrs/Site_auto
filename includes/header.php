<?php
require_once dirname(__DIR__) . '/config/config.php';

// Funcție helper pentru a obține numele utilizatorului
function getUserDisplayName()
{
    if (isset($_SESSION['user_name']) && !empty($_SESSION['user_name'])) {
        return $_SESSION['user_name'];
    } elseif (isset($_SESSION['user_email'])) {
        return $_SESSION['user_email'];
    }
    return 'Utilizator';
}
?>
<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo url('assets/css/style.css'); ?>" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo url(); ?>">
                <i class="fas fa-car me-2"></i><?php echo SITE_NAME; ?>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url(); ?>">
                            <i class="fas fa-home me-1"></i>Acasă
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('cars.php'); ?>">
                            <i class="fas fa-car me-1"></i>Mașini
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('about.php'); ?>">
                            <i class="fas fa-info-circle me-1"></i>Despre
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('contact.php'); ?>">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo url('admin/dashboard.php'); ?>">
                                    <i class="fas fa-tachometer-alt me-1"></i>Admin
                                </a>
                            </li>
                        <?php elseif (isSeller()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo url('seller/dashboard.php'); ?>">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars(getUserDisplayName()); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo url('profile.php'); ?>">
                                        <i class="fas fa-user-edit me-2"></i>Profil
                                    </a></li>
                                <?php if (isSeller() || isAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?php echo url(isSeller() ? 'seller/my-cars.php' : 'admin/cars.php'); ?>">
                                            <i class="fas fa-car me-2"></i>Mașinile mele
                                        </a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?php echo url('auth/logout.php'); ?>">
                                        <i class="fas fa-sign-out-alt me-2"></i>Ieșire
                                    </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('auth/login.php'); ?>">
                                <i class="fas fa-sign-in-alt me-1"></i>Autentificare
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('auth/register.php'); ?>">
                                <i class="fas fa-user-plus me-1"></i>Înregistrare
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Afișează mesajele de succes/eroare -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="container">
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="container">
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Debug info (elimină în producție) -->
    <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
        <div class="container">
            <div class="alert alert-info mt-3">
                <strong>Debug Session Info:</strong><br>
                <?php
                echo "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'N/A') . "<br>";
                echo "User Name: " . (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'N/A') . "<br>";
                echo "User Email: " . (isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'N/A') . "<br>";
                echo "User Type: " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'N/A') . "<br>";
                echo "Session ID: " . session_id() . "<br>";
                ?>
            </div>
        </div>
    <?php endif; ?>