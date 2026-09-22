<?php 
    // Guards against unset errors
    $login_error = $data["error"] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Inventory Dashboard | Login</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="min-h-screen flex flex-col justify-center items-center bg-slate-50 px-4 py-12">
    <!-- Stores the login form -->
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-sm border border-slate-200/80">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-slate-800">Login</h2>
            <p class="text-sm text-slate-500 mt-1">Please sign in below</p>
        </div>

        <!-- Form to collect the user input -->
        <form action="/login" method="post" class="space-y-4">
            <div class="space-y-3">
                <div>
                    <div class="flex flex-col space-y-1.5 mb-4">
                        <label for="username" class="text-sm font-medium text-slate-700">
                            Username
                        </label>
                        <input 
                            required
                            onkeyup="validateUsernameField()"
                            name="username"
                            id="usernameField"
                            type="text"
                            placeholder="Enter your username"
                            class="w-full px-3 py-2 text-sm text-slate-900 bg-white border rounded-lg outline-none transition-all duration-150"
                        >
                    </div>
                    <!-- Stores the error box -->
                    <div class="flex justify-between -mt-2">
                        <span id="usernameError" class="hidden text-xs font-semibold text-red-600"></span>
                    </div>
                </div>

                <!-- Stores the password -->
                <div>
                    <div class="flex flex-col space-y-1.5 mb-4">
                        <label for="password" class="text-sm font-medium text-slate-700">
                            Password
                        </label>
                        <input 
                            required
                            onkeyup="validatePasswordField()"
                            name="password"
                            type="password"
                            id="passwordField"
                            placeholder="Enter your password"
                            class="w-full px-3 py-2 text-sm text-slate-900 bg-white border rounded-lg outline-none transition-all duration-150"
                        >
                    </div>
                    <!-- Forget password link -->
                    <div class="flex justify-between -mt-2">
                        <span id="passwordError" class="hidden text-xs font-semibold text-red-600"></span>
                        <a 
                            href=""
                            class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline transition-colors"
                        >
                            Forgot password?
                        </a>
                    </div>
                </div>
            </div>

            <?php if($login_error){ ?>
                <div>
                    <span class="text-xs font-medium text-red-500 mt-1">
                        <?= htmlspecialchars($login_error) ?>
                    </span>
                </div>
            <?php } ?>
        
            <!-- Submit Button -->
            <button
                type="submit"
                id="submitBtn"
                onsubmit="handleLoginSubmit()"
                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors cursor-pointer"
            >
                Login
            </button>

            <!-- Allows the user to go and create an account -->
            <p class="text-center text-sm text-slate-600 pt-3 border-t border-slate-100">
                Don't have an account? <a href="/signup" class="font-semibold text-emerald-600 hover:text-emerald-700 hover:underline transition-colors">Create account</a>
            </p>
        </form>
    </div>

    <!-- Connects to the javascript -->
    <script src="/assets/js/forms/login/login.js"></script>
</body>
</html>