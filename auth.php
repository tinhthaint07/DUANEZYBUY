<?php
require_once 'config.php';
$m = $_GET['m'] ?? 'login';
if ($m === 'logout') { unset($_SESSION['user']); header('Location: index.php'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    if ($m === 'register') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
            $err = 'Nhập họ tên, email hợp lệ và mật khẩu từ 6 ký tự.';
        } else {
            try {
                $db->prepare("INSERT INTO users(name,email,password) VALUES(?,?,?)")
                   ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
                $_SESSION['user'] = ['id' => $db->lastInsertId(), 'name' => $name];
                header('Location: index.php'); exit;
            } catch (PDOException $x) { $err = 'Email này đã được đăng ký.'; }
        }
    } else {
        $st = $db->prepare("SELECT * FROM users WHERE email = ?");
        $st->execute([$email]);
        $u = $st->fetch();
        if ($u && password_verify($pass, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name']];
            header('Location: index.php'); exit;
        }
        $err = 'Email hoặc mật khẩu không đúng.';
    }
}
$title = $m === 'register' ? 'Tạo tài khoản' : 'Đăng nhập';
require 'header.php';
?>
<form method="post" class="auth">
  <h1><?= $title ?></h1>
  <?php if ($err): ?><p class="err"><?= e($err) ?></p><?php endif; ?>
  <?php if ($m === 'register'): ?><input name="name" placeholder="Họ và tên" required><?php endif; ?>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Mật khẩu" required>
  <button class="btn"><?= $m === 'register' ? 'Tạo tài khoản' : 'Đăng nhập' ?></button>
  <small><?= $m === 'register' ? '<a href="auth.php">Đã có tài khoản? Đăng nhập</a>' : '<a href="auth.php?m=register">Chưa có tài khoản? Đăng ký</a>' ?></small>
</form>
<?php require 'footer.php'; ?>
