<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Inventory Dashboard | Login</title>
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
            <div class="space-y-3"">
                <div class="flex flex-col space-y-1.5 mb-4">
                    <label for="username" class="text-sm font-medium text-slate-700">
                        Username
                    </label>
                    <input 
                        type="text"
                        placeholder="Enter your username"
                        class="w-full px-3 py-2 text-sm text-slate-900 bg-white border rounded-lg outline-none transition-all duration-150"
                    >
                </div>

                <!-- Stores the password -->
                <div>
                    <div class="flex flex-col space-y-1.5 mb-4">
                        <label for="username" class="text-sm font-medium text-slate-700">
                            Password
                        </label>
                        <input 
                            type="password"
                            placeholder="Enter your password"
                            class="w-full px-3 py-2 text-sm text-slate-900 bg-white border rounded-lg outline-none transition-all duration-150"
                        >
                    </div>
                    <!-- Forget password link -->
                    <div class="flex justify-end -mt-2">
                        <a 
                            href=""
                            class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline transition-colors"
                        >
                            Forgot password?
                        </a>
                    </div>
                </div>
            </div>
        
            <!-- Submit Button -->
            <button
                type="submit"
                id="submitBtn"
                class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors cursor-pointer"
            >
                Submit Files
            </button>

            <!-- Allows the user to go and create an account -->
            <p className="text-center text-sm text-slate-600 pt-3 border-t border-slate-100">
                Don't have an account?
                <a 
                    href="/signup" 
                    className="font-semibold text-emerald-600 hover:text-emerald-700 hover:underline transition-colors"
                >
                    Create account
                </a>
            </p>
        </form>
    </div>
</body>
</html>