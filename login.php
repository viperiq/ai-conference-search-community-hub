<?php
session_start();
$mysqli = require __DIR__ . "/database.php";
require_once __DIR__ . "/functions.php";

$user = null;
if (isset($_SESSION["user_id"])) {
    $user = getUserProfile((int)$_SESSION["user_id"], $mysqli);
}

$is_invalid = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $mysqli = require __DIR__ . "/database.php";
    
    $sql = sprintf("SELECT * FROM user
                    WHERE email = '%s'",
                   $mysqli->real_escape_string($_POST["email"]));
    
    $result = $mysqli->query($sql);
    
    $user = $result->fetch_assoc();
    
    if ($user) {
        
        if (password_verify($_POST["password"], $user["pass_hash"])) {
            
            session_start();
            
            session_regenerate_id();
            
            $_SESSION["user_id"] = $user["id"];
            
            header("Location: home.php");
            exit;
        }
    }
    
    $is_invalid = true;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Login | ConfHub</title>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-900 min-h-screen flex flex-col">
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="flex-1 flex items-center justify-center p-6">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden p-10">
            <?php if ($is_invalid): ?>
                <div class="bg-rose-50 text-rose-600 p-4 rounded-xl mb-6 text-sm font-bold text-center border border-rose-100">
                    Invalid login credentials
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Welcome Back</h1>
                    <p class="text-slate-500 text-sm mt-2">Enter your details to access your account</p>
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Email Address</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500 transition" placeholder="name@example.com">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Password</label>
                    <input type="password" name="password" id="password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-indigo-500 transition" placeholder="••••••••">
                </div>

                <button class="w-full py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition transform hover:-translate-y-0.5 active:translate-y-0">
                    LogIn
                </button>

                <p class="text-center text-slate-600 text-sm">
                    Don't have an account? 
                    <a href="signup.html" class="text-indigo-600 font-bold hover:underline">Create account</a>
                </p>
            </form>
        </div>
    </div>

</body>
</html>
