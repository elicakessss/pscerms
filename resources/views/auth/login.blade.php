<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSCERMS - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #f0f4f0;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Standard font sizes matching your system */
        .text-xs { font-size: 10px; }
        .text-sm { font-size: 11px; }
        .text-base { font-size: 12px; }
        .text-lg { font-size: 14px; }
        .text-xl { font-size: 16px; }
        .text-2xl { font-size: 18px; }

        .login-container {
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .login-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            min-height: 500px;
        }

        .login-form {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-logo {
            flex: 1;
            background: linear-gradient(180deg, #064e3b 0%, #065f46 50%, #047857 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            margin-top: 0.5rem;
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #047857;
            box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.1);
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .forgot-link {
            display: block;
            text-align: right;
            color: #064e3b;
            font-size: 11px;
            margin-top: 0.5rem;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #059669;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(180deg, #064e3b 0%, #065f46 50%, #047857 100%);
            color: white;
            padding: 0.875rem;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
            margin-top: 1rem;
            font-family: 'Poppins', sans-serif;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 78, 59, 0.3);
        }

        .close-btn {
            font-size: 18px;
            cursor: pointer;
            color: #9ca3af;
            transition: color 0.2s;
        }

        .close-btn:hover {
            color: #6b7280;
        }

        .login-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .login-logo h2 {
            font-size: 16px;
            font-weight: 600;
            margin: 1rem 0 0.5rem 0;
        }

        .login-logo p {
            font-size: 12px;
            margin: 0;
            opacity: 0.9;
        }

        .logo-image {
            width: 120px;
            height: auto;
            margin-bottom: 1rem;
        }

        .error-text {
            font-size: 10px;
            color: #dc2626;
            margin-top: 0.5rem;
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
                margin: 1rem;
            }

            .login-form {
                padding: 2rem;
            }

            .login-logo {
                padding: 2rem;
                order: -1;
            }

            .login-title {
                font-size: 16px;
            }

            .login-logo h2 {
                font-size: 14px;
            }

            .main-container {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .login-form {
                padding: 1.5rem;
            }

            .login-logo {
                padding: 1.5rem;
            }

            .login-title {
                font-size: 14px;
            }

            .login-logo h2 {
                font-size: 12px;
            }

            .form-input {
                font-size: 11px;
                padding: 0.75rem;
            }

            .submit-btn {
                font-size: 11px;
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="login-container">
            <div class="login-card">
                <!-- Login Form -->
                <div class="login-form">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h1 class="login-title">Login</h1>
                        <span class="close-btn">×</span>
                    </div>

                    <form method="POST" action="/login">
                        @csrf

                        <!-- ID Number -->
                        <div class="form-group">
                            <label for="id_number" class="form-label">ID Number</label>
                            <input type="text"
                                id="id_number"
                                name="id_number"
                                value="{{ old('id_number') }}"
                                class="form-input"
                                placeholder="Enter your ID number"
                                required
                                autofocus>
                            @error('id_number')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Enter your password"
                                required>
                            @error('password')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Role Selection -->
                        <div class="form-group">
                            <label for="role" class="form-label">Role</label>
                            <select id="role"
                                    name="role"
                                    class="form-input"
                                    required>
                                <option value="" disabled selected>-- Select Role --</option>
                                <option value="student">Student</option>
                                <option value="adviser">Adviser</option>
                                <option value="admin">Administrator</option>
                            </select>
                            @error('role')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Forgot Password Link -->
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Forgot Password?
                        </a>

                        <!-- Submit Button -->
                        <button type="submit" class="submit-btn">
                            Log In
                        </button>
                    </form>
                </div>

                <!-- Logo Section -->
                <div class="login-logo">
                    <img src="{{ asset('images/psg-logo.png') }}" alt="PSG Logo" class="logo-image">
                    <h2>Paulinian Student Council</h2>
                    <p>E-Portfolio and Rank Management System</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
